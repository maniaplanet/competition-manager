<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9065 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-06 18:09:45 +0100 (jeu., 06 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

class Championship extends Groups implements LastCompliant
{
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::CHAMPIONSHIP;
		$this->schedule = new \CompetitionManager\Services\Schedules\MultiSimple();
		$this->parameters['isFreeForAll'] = false;
		$this->parameters['numberOfRounds'] = 1;
		$this->parameters['pointsForWin'] = 2;
		$this->parameters['pointsForLoss'] = 1;
		$this->parameters['pointsForForfeit'] = 0;
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
				if(($matchOrId instanceof \CompetitionManager\Services\Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
					return $round;
		}
		else
		{
			foreach($this->matches as $round => $roundMatches)
				foreach($roundMatches as $matchOrId)
					if(($matchOrId instanceof \CompetitionManager\Services\Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
						return $round;
		}
	}
	
	function onCreate()
	{
		$this->matches = array();
		$service = new \CompetitionManager\Services\MatchService();
		
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
		$matchService = new \CompetitionManager\Services\MatchService();
		if($this->parameters['isFreeForAll'])
		{
			foreach($this->matches as $matchId)
				$matchService->assignParticipants($matchId, $participants, $this->rules->getDefaultScore());
		}
		else
		{
			// Removing unnecessary matches
			$nbParticipants = count($participants);
			$nbMatchesPerRound = $nbParticipants>>1;
			if($nbParticipants < $this->maxSlots)
			{
				foreach(array_splice($this->matches, -$this->getRoundsCount()) as $matchId)
					$matchService->delete($matchId);
				foreach($this->matches as &$roundMatches)
					foreach(array_splice($roundMatches, -$nbMatchesPerRound) as $matchId)
						$matchService->delete($matchId);
				unset($roundMatches);
				$stageService = new \CompetitionManager\Services\StageService();
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
				array_splice($awayParticipants, -1, 0, array_pop($homeParticipants));
			}
		}
	}
	
	function onMatchOver($match)
	{
		$round = $this->findMatch($match->matchId);
		$match->fetchParticipants();
		$this->fetchParticipants();

		if($this->parameters['isFreeForAll'])
			$pointsByRank = $this->parameters['scoringSystem']->points;
		else
			$pointsByRank = array($this->parameters['pointsForWin'], $this->parameters['pointsForLoss']);
		
		foreach($match->participants as $matchResult)
		{
			$stageResult = $this->participants[$matchResult->participantId];
			$stageResult->score->summary[$round] = $matchResult->rank;
			$stageResult->score->points = null;
			foreach($stageResult->score->summary as $rank)
			{
				if($rank === null)
					continue;
				$stageResult->score->points += $pointsByRank[min($rank, count($pointsByRank))-1];
			}
		}
		
		$service = new \CompetitionManager\Services\ParticipantService();
		$service->rank($this->participants);
		foreach($this->participants as $participantId => $participant)
			$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
		
		$service = new \CompetitionManager\Services\MatchService();
		$service->setState($match->matchId, State::ARCHIVED);
	}
	
	function onEnd()
	{
		$this->fetchParticipants();
		
		$service = new \CompetitionManager\Services\ParticipantService();
		$service->rank($this->participants);
		if($this->nextId)
		{
			$stageService = new \CompetitionManager\Services\StageService();
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
