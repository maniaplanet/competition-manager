<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9065 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-06 18:09:45 +0100 (jeu., 06 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

class Groups extends Championship
{
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::GROUPS;
		$this->schedule = new \CompetitionManager\Services\Schedules\MultiSimple();
		$this->parameters['numberOfRounds'] = 1;
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
	
	function getAction()
	{
		return 'groups';
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
					$matchService->assignParticipants($matchId, $participants, $this->rules->getDefaultDetails());
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
						$matchService->assignParticipants($matchId, array($homeParticipants[$index], $awayParticipants[$index]), $this->rules->getDefaultDetails());
					array_unshift($homeParticipants, array_shift($awayParticipants));
					array_splice($awayParticipants, -1, 0, array_pop($homeParticipants));
				}
			}
		}
	}
	
	function onMatchOver($match)
	{
		// TODO how to give points ? (might be almost same function than parent)
	}
	
	function onEnd()
	{
		
	}
}

?>
