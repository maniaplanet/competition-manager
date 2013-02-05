<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9065 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-06 18:09:45 +0100 (jeu., 06 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

use CompetitionManager\Services\Scores;

class Groups extends \CompetitionManager\Services\Stage implements IntermediateCompliant
{
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::GROUPS;
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
		return $score;
	}
	
	function findMatch($matchId)
	{
		foreach($this->matches as $group => $groupRounds)
		{
			if($this->parameters['isFreeForAll'])
			{
				foreach($groupRounds as $round => $matchOrId)
					if(($matchOrId instanceof \CompetitionManager\Services\Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
						return array($group, $round);
			}
			else
			{
				foreach($groupRounds as $round => $roundMatches)
					foreach($roundMatches as $matchOrId)
						if(($matchOrId instanceof \CompetitionManager\Services\Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
							return array($group, $round);
			}
		}
	}
	
	function onCreate()
	{
		$this->matches = array();
		$service = new \CompetitionManager\Services\MatchService();
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
	
	private function createMatch($service, $round)
	{
		$match = new \CompetitionManager\Services\Match();
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
		foreach($participants as $index => $participant)
			$this->parameters['groupParticipants'][$index%$this->parameters['numberOfGroups']][] = $participant->participantId;
		
		$matchService = new \CompetitionManager\Services\MatchService();
		$stageService = new \CompetitionManager\Services\StageService();
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
				foreach($this->matches as &$groupMatches)
					foreach(array_splice($groupMatches, -$this->getRoundsCount()) as $matchId)
						$matchService->delete($matchId);
				foreach($this->matches as $group => &$groupMatches)
				{
					$nbMatchesPerRound = count($this->parameters['groupParticipants'][$group])>>1;
					foreach($groupMatches as &$roundMatches)
						foreach(array_splice($roundMatches, -$nbMatchesPerRound) as $matchId)
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
					array_splice($awayParticipants, -1, 0, array_pop($homeParticipants));
				}
			}
		}
	}
	
	function onMatchOver($match)
	{
		list($group, $round) = $this->findMatch($match->matchId);
		$match->fetchParticipants();
		$this->fetchParticipants();
		
		foreach($match->participants as $matchResult)
		{
			$stageResult = $this->participants[$matchResult->participantId];
			$stageResult->score->summary[$round] = $matchResult->rank;
			$stageResult->score->points = null;
			foreach($stageResult->score->summary as $rank)
			{
				if($rank === null)
					continue;
				
				if($this->parameters['isFreeForAll'])
					$pointsByRank = $this->parameters['scoringSystem']->points;
				else
					$pointsByRank = array($this->parameters['pointsForWin'], $this->parameters['pointsForLoss']);
				
				$stageResult->score->points += $pointsByRank[$rank-1];
			}
		}
		
		$groupParticipants = array_intersect_key($this->participants, array_flip($this->parameters['groupParticipants'][$group]));
		$service = new \CompetitionManager\Services\ParticipantService();
		$service->rankParticipants($groupParticipants);
		foreach($groupParticipants as $participantId => $participant)
			$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
		
		$service = new \CompetitionManager\Services\MatchService();
		$service->setState($match->matchId, State::ARCHIVED);
	}
	
	function onEnd()
	{
		
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
