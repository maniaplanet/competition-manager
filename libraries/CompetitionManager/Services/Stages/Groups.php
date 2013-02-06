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
						return array($group, $round);
			}
			else
			{
				foreach($groupRounds as $round => $roundMatches)
					foreach($roundMatches as $matchOrId)
						if(($matchOrId instanceof Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
							return array($group, $round);
			}
		}
	}
	
	function onCreate()
	{
		$this->matches = array();
		$service = new MatchService();
		$slotsPerGroup = ceil($this->maxSlots / $this->parameters['numberOfGroups']);
		
		if($this->parameters['isFreeForAll'])
		{
			for($group=0; $group<$this->parameters['numberOfGroups']; ++$group)
				for($round=0; $round<$this->getRoundsCount(); ++$round)
					$this->matches[$group][] = $this->createMatch($service, $round)->matchId;
		}
		else
		{
			for($group=0; $group<$this->parameters['numberOfGroups']; ++$group)
				for($round=0; $round<$this->getRoundsCount(); ++$round)
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
				foreach($groupMatches as $matchId)
					$matchService->assignParticipants($matchId, $participants, $this->rules->getDefaultScore());
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
			}
		}
	}
	
	function onMatchOver($match)
	{
		list($group, $round) = $this->findMatch($match->matchId);
		$this->updateScores($match, $round);
		
		$groupParticipants = array_intersect_key($this->participants, array_flip($this->parameters['groupParticipants'][$group]));
		$service = new ParticipantService();
		$service->rank($groupParticipants);
		foreach($groupParticipants as $participantId => $participant)
			$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
		
		$service = new MatchService();
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
			return _('BYE');
		
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
