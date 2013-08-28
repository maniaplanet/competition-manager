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
use CompetitionManager\Services\Scores;
use CompetitionManager\Services\StageService;

class Groups extends \CompetitionManager\Services\Stage implements IntermediateCompliant
{
	function __construct()
	{
		$this->type = Constants\StageType::GROUPS;
		$this->schedule = new \CompetitionManager\Services\Schedules\MultiSimple();
		$this->parameters['isFreeForAll'] = false;
		$this->parameters['numberOfRounds'] = 1;
		$this->parameters['pointsForWin'] = 2;
		$this->parameters['pointsForLoss'] = 1;
		$this->parameters['scoringSystem'] = null;
		$this->parameters['numberOfGroups'] = 4;
		$this->parameters['groupParticipants'] = array();
	}
	
	function getName()
	{
		return _('Groups');
	}
	
	function getInfo()
	{
		
	}
	
	function getRoundsCount($slots=null)
	{
		if($slots === null)
			$slots = $this->maxSlots;
		$slotsPerGroup = ceil($slots / $this->parameters['numberOfGroups']);
		return $this->parameters['numberOfRounds'] * ($this->parameters['isFreeForAll'] ?: $slotsPerGroup-!($slotsPerGroup&1));
	}
	
	function getScheduleNames()
	{
		$roundNames = array();
		
		for($round = 1; $round <= $this->getRoundsCount(); ++$round)
			$roundNames[] = sprintf(_('Round #%d'), $round);
		
		return $roundNames;
	}
	
	function getIcon()
	{
		
	}
	
	function getAction()
	{
		return 'groups';
	}
	
	function getDefaultScore()
	{
		$score = new Scores\Summary();
		$score->main = new Scores\Points();
		$score->summary = array_fill(0, $this->getRoundsCount(), null);
		return $score;
	}
	
	function findMatch($matchId)
	{
		foreach($this->matches as $group => $groupRounds)
		{
			if($this->parameters['isFreeForAll'])
			{
				foreach($groupRounds as $round => $matchOrId)
					if(($matchOrId instanceof Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
						return array($group, $round, null);
			}
			else
			{
				foreach($groupRounds as $round => $roundMatches)
					foreach($roundMatches as $offset => $matchOrId)
						if(($matchOrId instanceof Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
							return array($group, $round, $offset);
			}
		}
	}
	
	function getEmptyLabels($group, $round=null, $offset=null)
	{
		$service = new StageService();
		$previousStage = $service->get($this->previousId);
		
		$emptyLabels = array();
		foreach(range($group+1, $this->maxSlots, $this->parameters['numberOfGroups']) as $rank)
			$emptyLabels[] = $previousStage->getPlaceholder($rank, $this->maxSlots);
		
		if($round === null || $this->parameters['isFreeForAll'])
			return $emptyLabels;
		
		if(count($emptyLabels) & 1)
			$emptyLabels[] = null;
		list($home, $away) = array_chunk($emptyLabels, count($emptyLabels)>>1);
		while($round-- > 0)
		{
			array_unshift($home, array_shift($away));
			array_splice($away, -1, 0, array(array_pop($home)));
		}
		return array($home[$offset], $away[$offset]);
	}
	
	function onCreate()
	{
		$this->matches = array();
		$this->parameters['groupParticipants'] = array_fill(0, $this->parameters['numberOfGroups'], array());
		$service = new MatchService();
		$slotsPerGroup = ceil($this->maxSlots / $this->parameters['numberOfGroups']);
		
		if($this->parameters['isFreeForAll'])
		{
			for($round=0; $round<$this->getRoundsCount(); ++$round)
				for($group=0; $group<$this->parameters['numberOfGroups']; ++$group)
					$this->matches[$group][] = $this->createMatch($service, $round)->matchId;
		}
		else
		{
			for($round=0; $round<$this->getRoundsCount(); ++$round)
				for($group=0; $group<$this->parameters['numberOfGroups']; ++$group)
				{
					$roundMatches = array();
					for($i=0; $i<$slotsPerGroup>>1; ++$i)
						$roundMatches[] = $this->createMatch($service, $round)->matchId;
					$this->matches[$group][] = $roundMatches;
				}
		}
	}
	
	protected function createMatch($service, $round)
	{
		$match = new Match();
		$match->name = sprintf(_('Round #%d'), $round+1);
		$match->stageId = $this->stageId;
		if(isset($this->schedule->startTimes[$round]))
			$match->startTime = $this->schedule->startTimes[$round];
		$service->create($match);
		
		return $match;
	}
	
	function onReady($participants)
	{
		// Dispatch in groups
		$this->parameters['groupParticipants'] = array();
		foreach(array_values($participants) as $index => $participant)
			$this->parameters['groupParticipants'][$index%$this->parameters['numberOfGroups']][] = (int) $participant->participantId;
		
		$matchService = new MatchService();
		$stageService = new StageService();
		$stageService->update($this);
		
		if($this->parameters['isFreeForAll'])
		{
			foreach($this->matches as $groupMatches)
			{
				foreach($groupMatches as $matchId)
					$matchService->assignParticipants($matchId, $participants, $this->rules->getDefaultScore());
				$matchService->setState($groupMatches[0], Constants\State::READY);
			}
		}
		else
		{
			// Removing unnecessary matches
			$nbParticipants = count($participants);
			if($nbParticipants < $this->maxSlots)
			{
				foreach($this->matches as $group => &$groupMatches)
				{
					foreach(array_splice($groupMatches, $this->getRoundsCount($nbParticipants)) as $roundMatches)
						foreach($roundMatches as $matchId)
							$matchService->delete($matchId);
					$nbMatchesPerRound = count($this->parameters['groupParticipants'][$group])>>1;
					foreach($groupMatches as &$roundMatches)
						foreach(array_splice($roundMatches, $nbMatchesPerRound) as $matchId)
							$matchService->delete($matchId);
				}
				unset($groupMatches);
				unset($roundMatches);
				$stageService->update($this);
			}
			
			// Assigning participants to their matches
			foreach($this->matches as $group => &$groupMatches)
			{
				$groupParticipants = $this->parameters['groupParticipants'][$group];
				if(count($groupParticipants) & 1)
					$groupParticipants[] = null;
				list($homeParticipants, $awayParticipants) = array_chunk($groupParticipants, count($groupParticipants)>>1);
				foreach($groupMatches as $roundMatches)
				{
					foreach($roundMatches as $index => $matchId)
						$matchService->assignParticipants($matchId, array($homeParticipants[$index], $awayParticipants[$index]), $this->rules->getDefaultScore());
					array_unshift($homeParticipants, array_shift($awayParticipants));
					array_splice($awayParticipants, -1, 0, array(array_pop($homeParticipants)));
				}
				foreach($groupMatches[0] as $matchId)
					$matchService->setState($matchId, Constants\State::READY);
			}
		}
	}
	
	function onMatchOver($match)
	{
		list($group, $round, $offset) = $this->findMatch($match->matchId);
		$this->updateScores($match, $round);
		
		$groupParticipants = array_intersect_key($this->participants, array_flip($this->parameters['groupParticipants'][$group]));
		$service = new ParticipantService();
		$service->rank($groupParticipants);
		foreach($groupParticipants as $participantId => $participant)
			$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
		
		$service = new MatchService();
		if(isset($this->matches[$group][$round+1]))
		{
			if($this->parameters['isFreeForAll'])
				$service->setState($this->matches[$group][$round+1], Constants\State::READY);
			else
			{
				$offsetMax = (count($groupParticipants) >> 1) - 1;
				$isOdd = count($groupParticipants) & 1;
				// FIXME ? the following code is relying on how players are assigned to their matches
				switch($offset)
				{
					case 0:
						if($service->getState($this->matches[$group][$round][$offset+1]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$group][$round+1][$offset], Constants\State::READY);
						break;
					case 1:
						if($service->getState($this->matches[$group][$round][$offset-1]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$group][$round+1][$offset-1], Constants\State::READY);
						break;
					default:
						if($service->getState($this->matches[$group][$round][$offset-2]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$group][$round+1][$offset-1], Constants\State::READY);
						break;
				}
				switch($offsetMax - $offset)
				{
					case 0:
						if(!$isOdd && $service->getState($this->matches[$group][$round][$offset-1]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$group][$round+1][$offset], Constants\State::READY);
						break;
					case 1:
						if($isOdd)
							$service->setState($this->matches[$group][$round+1][$offset+1], Constants\State::READY);
						else if($service->getState($this->matches[$group][$round][$offset+1]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$group][$round+1][$offset+1], Constants\State::READY);
						break;
					default:
						if($service->getState($this->matches[$group][$round][$offset+2]) == Constants\State::ARCHIVED)
							$service->setState($this->matches[$group][$round+1][$offset+1], Constants\State::READY);
						break;
				}
			}
		}
		$service->setState($match->matchId, Constants\State::ARCHIVED);
	}
	
	protected function updateScores($match, $round)
	{
		$this->fetchParticipants();
		$match->fetchParticipants();
		
		if($this->parameters['isFreeForAll'])
			$pointsByRank = $this->parameters['scoringSystem']['points'];
		else
			$pointsByRank = array($this->parameters['pointsForWin'], $this->parameters['pointsForLoss']);
		
		foreach($match->participants as $matchResult)
		{
			$participant = $this->participants[$matchResult->participantId];
			$participant->score->summary[$round] = intval($matchResult->rank) ?: null;
			$participant->score->points = null;
			foreach($participant->score->summary as $rank)
			{
				if($rank === null)
					continue;
				$participant->score->points += $pointsByRank[min($rank, count($pointsByRank))-1];
			}
		}
	}
	
	function onEnd()
	{
		$this->fetchParticipants();
		
		$service = new ParticipantService();
		$stageService = new StageService();
		
		$nextStage = $stageService->get($this->nextId);
		$qualifiedPerGroup = floor($nextStage->maxSlots / $this->parameters['numberOfGroups']);
		
		foreach($this->parameters['groupParticipants'] as $group => $participantIds)
		{
			$groupParticipants = array_intersect_key($this->participants, array_flip($participantIds));
			$service->rank($groupParticipants);
			$service->breakTies($groupParticipants, $qualifiedPerGroup);
			foreach($groupParticipants as $participantId => $participant)
			{
				$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
				if($participant->rank <= $qualifiedPerGroup)
				{
					$service->setStageQualification($this->stageId, $participantId, Constants\Qualified::YES);
					// hack to get right seeds in next stage, rank must not be updated in DB!!
					$participant->rank = ($participant->rank-1) * $this->parameters['numberOfGroups'] + $group + 1;
				}
				else if($participant->rank === null)
					$service->setStageQualification($this->stageId, $participantId, Constants\Qualified::LEAVED);
				else
				{
					$service->setStageQualification($this->stageId, $participantId, Constants\Qualified::NO);
					// hack to avoid qualification for next stage, rank must not be updated in DB!!
					$participant->rank = $nextStage->maxSlots + 1;
				}
			}
		}
	}
	
	///////////////////////////////////////////////////////////////////////////
	// Interfaces implementation
	///////////////////////////////////////////////////////////////////////////
	
	function getPlaceholder($rank, $max)
	{
		$qualifiedPerGroup = floor($max / $this->parameters['numberOfGroups']);
		$qualified = $qualifiedPerGroup * $this->parameters['numberOfGroups'];
		if($rank > $qualified)
			return _('-');
		
		$group = floor(($rank-1) / $qualifiedPerGroup);
		$groupRank = (($rank-1) % $qualifiedPerGroup) + 1;
		return sprintf(_('#%d of group %s'), $groupRank, self::getGroupLetter($group));
	}
	
	///////////////////////////////////////////////////////////////////////////
	// Utilities
	///////////////////////////////////////////////////////////////////////////
	
	static function getGroupLetter($group)
	{
		$groupLetter = '';
		for(++$group; $group>0; $group=floor($group/26))
			$groupLetter = chr(ord('A')+($group-1)%26).$groupLetter;
		return $groupLetter;
	}
}

?>
