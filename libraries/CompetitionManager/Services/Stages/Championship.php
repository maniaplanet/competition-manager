<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9065 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-06 18:09:45 +0100 (jeu., 06 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

use CompetitionManager\Constants;
use CompetitionManager\Services\Match;
use CompetitionManager\Services\MatchService;
use CompetitionManager\Services\ParticipantService;
use CompetitionManager\Services\StageService;

class Championship extends Groups implements LastCompliant
{
	function __construct()
	{
		$this->type = Constants\StageType::CHAMPIONSHIP;
		$this->schedule = new \CompetitionManager\Services\Schedules\MultiSimple();
		$this->parameters['isFreeForAll'] = false;
		$this->parameters['numberOfRounds'] = 1;
		$this->parameters['pointsForWin'] = 2;
		$this->parameters['pointsForLoss'] = 1;
		$this->parameters['scoringSystem'] = null;
	}
	
	function getName()
	{
		return _('Championship');
	}
	
	function getInfo()
	{
		
	}
	
	function getRoundsCount($slots=null)
	{
		if($slots === null)
			$slots = $this->maxSlots;
		return $this->parameters['numberOfRounds'] * ($this->parameters['isFreeForAll'] ?: $slots-!($slots&1));
	}
	
	function getAction()
	{
		return 'championship';
	}
	
	function findMatch($matchId)
	{
		if($this->parameters['isFreeForAll'])
		{
			foreach($this->matches as $round => $matchOrId)
				if(($matchOrId instanceof Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
					return array($round, null);
		}
		else
		{
			foreach($this->matches as $round => $roundMatches)
				foreach($roundMatches as $offset => $matchOrId)
					if(($matchOrId instanceof Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
						return array($round, $offset);
		}
	}
	
	function onCreate()
	{
		$this->matches = array();
		$service = new MatchService();
		
		if($this->parameters['isFreeForAll'])
		{
			for($round=0; $round<$this->getRoundsCount(); ++$round)
				$this->matches[] = $this->createMatch($service, $round)->matchId;
		}
		else
		{
			for($round=0; $round<$this->getRoundsCount(); ++$round)
			{
				$roundMatches = array();
				for($i=0; $i<$this->maxSlots>>1; ++$i)
					$roundMatches[] = $this->createMatch($service, $round)->matchId;
				$this->matches[] = $roundMatches;
			}
		}
	}
	
	function onReady($participants)
	{
		$matchService = new MatchService();
		if($this->parameters['isFreeForAll'])
		{
			foreach($this->matches as $matchId)
				$matchService->assignParticipants($matchId, $participants, $this->rules->getDefaultScore());
			$matchService->setState($this->matches[0], Constants\State::READY);
		}
		else
		{
			// Removing unnecessary matches
			$nbParticipants = count($participants);
			$nbMatchesPerRound = $nbParticipants>>1;
			if($nbParticipants < $this->maxSlots)
			{
				foreach(array_splice($this->matches, $this->getRoundsCount()) as $roundMatches)
					foreach($roundMatches as $matchId)
						$matchService->delete($matchId);
				foreach($this->matches as &$roundMatches)
					foreach(array_splice($roundMatches, $nbMatchesPerRound) as $matchId)
						$matchService->delete($matchId);
				unset($roundMatches);
				$stageService = new StageService();
				$stageService->update($this);
			}
			
			// Assigning participants to their matches
			if($nbParticipants & 1)
				$participants[] = null;
			list($homeParticipants, $awayParticipants) = array_chunk($participants, count($participants)>>1);
			foreach($this->matches as $roundMatches)
			{
				foreach($roundMatches as $index => $matchId)
					$matchService->assignParticipants($matchId, array($homeParticipants[$index], $awayParticipants[$index]), $this->rules->getDefaultScore());
				array_unshift($homeParticipants, array_shift($awayParticipants));
				array_splice($awayParticipants, -1, 0, array(array_pop($homeParticipants)));
			}
			foreach($this->matches[0] as $matchId)
				$matchService->setState($matchId, Constants\State::READY);
		}
	}
	
	function onMatchOver($match)
	{
		list($round, $offset) = $this->findMatch($match->matchId);
		$this->updateScores($match, $round);
		
		$service = new ParticipantService();
		$service->rank($this->participants);
		foreach($this->participants as $participantId => $participant)
			$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
		
		$service = new MatchService();
		if(isset($this->matches[$round+1]))
		{
			if($this->parameters['isFreeForAll'])
				$service->setState($this->matches[$round+1], Constants\State::READY);
			else
			{
				$offsetMax = (count($this->participants) >> 1) - 1;
				$isOdd = count($this->participants) & 1;
				// FIXME ? the following code is relying on how players are assigned to their matches
				switch($offset)
				{
					case 0:
						if($service->getState($this->matches[$round][$offset+1]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$round+1][$offset], Constants\State::READY);
						break;
					case 1:
						if($service->getState($this->matches[$round][$offset-1]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$round+1][$offset-1], Constants\State::READY);
						break;
					default:
						if($service->getState($this->matches[$round][$offset-2]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$round+1][$offset-1], Constants\State::READY);
						break;
				}
				switch($offsetMax - $offset)
				{
					case 0:
						if(!$isOdd && $service->getState($this->matches[$round][$offset-1]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$round+1][$offset], Constants\State::READY);
						break;
					case 1:
						if($isOdd)
							$service->setState($this->matches[$round+1][$offset+1], Constants\State::READY);
						else if($service->getState($this->matches[$round][$offset+1]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$round+1][$offset+1], Constants\State::READY);
						break;
					default:
						if($service->getState($this->matches[$round][$offset+2]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$round+1][$offset+1], Constants\State::READY);
						break;
				}
			}
		}
		$service->setState($match->matchId, Constants\State::ARCHIVED);
	}
	
	function onEnd()
	{
		$this->fetchParticipants();
		
		$service = new ParticipantService();
		$service->rank($this->participants);
		if($this->nextId)
		{
			$stageService = new StageService();
			$nextStage = $stageService->get($this->nextId);
			
			$service->breakTies($this->participants, $nextStage->maxSlots);
			foreach($this->participants as $participantId => $participant)
			{
				$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
				if($participant->rank <= $nextStage->maxSlots)
					$service->setStageQualification($this->stageId, $participantId, Constants\Qualified::YES);
				else if($participant->rank === null)
					$service->setStageQualification($this->stageId, $participantId, Constants\Qualified::LEAVED);
				else
					$service->setStageQualification($this->stageId, $participantId, Constants\Qualified::NO);
			}
		}
		else
			foreach($this->participants as $participantId => $participant)
				$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
	}
	
	///////////////////////////////////////////////////////////////////////////
	// Interfaces implementation
	///////////////////////////////////////////////////////////////////////////
	
	function getPlaceholder($rank, $max)
	{
		return sprintf(_('#%d of previous stage'), $rank);
	}
}

?>
