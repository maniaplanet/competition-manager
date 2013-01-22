<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9065 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-06 18:09:45 +0100 (jeu., 06 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

class Championship extends \CompetitionManager\Services\Stage
{
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::CHAMPIONSHIP;
		$this->schedule = new \CompetitionManager\Services\Schedules\MultiSimple();
		$this->parameters['isFreeForAll'] = false;
		$this->parameters['numberOfRounds'] = 1;
	}
	
	function getName()
	{
		return _('Championship');
	}
	
	function getInfo()
	{
		
	}
	
	function getRoundsCount()
	{
		return $this->parameters['numberOfRounds'] * ($this->parameters['isFreeForAll'] ? 1 : $this->maxSlots-!($this->maxSlots&1));
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
		return 'championship';
	}
	
	function onCreate()
	{
		$this->matches = array();
		$service = new \CompetitionManager\Services\MatchService();
		
		for($round = 0; $round < $this->getRoundsCount(); ++$round)
		{
			if($this->parameters['isFreeForAll'])
				$this->matches[] = $this->createMatch($service, $round)->matchId;
			else
			{
				$roundMatches = array();
				for($i=0; $i<$this->maxSlots>>1; ++$i)
					$roundMatches[] = $this->createMatch($service, $round)->matchId;
				$this->matches[] = $roundMatches;
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
		$matchService = new \CompetitionManager\Services\MatchService();
		if($this->parameters['isFreeForAll'])
		{
			foreach($this->matches as $matchId)
				$matchService->assignParticipants($matchId, $participants, $this->rules->getDefaultDetails());
		}
		else
		{
			$nbParticipants = count($participants);
			$nbMatchesPerRound = $nbParticipants>>1;
			if($nbParticipants < $this->maxSlots)
			{
				$this->maxSlots = $nbParticipants;
				foreach(array_splice($this->matches, -$this->getRoundsCount()) as $matchId)
					$matchService->delete($matchId);
				foreach($this->matches as &$roundMatches)
					foreach(array_splice($roundMatches, -$nbMatchesPerRound) as $matchId)
						$matchService->delete($matchId);
				unset($roundMatches);
				$stageService = new \CompetitionManager\Services\StageService();
				$stageService->update($this);
			}
			
			if($nbParticipants & 1)
				$participants[] = null;
			list($homeParticipants, $awayParticipants) = array_chunk($participants, count($participants)>>1);
			foreach($this->matches as $roundMatches)
			{
				foreach($roundMatches as $i => $matchId)
					$matchService->assignParticipants($matchId, array($homeParticipants[$i], $awayParticipants[$i]), $this->rules->getDefaultDetails());
				array_unshift($homeParticipants, array_shift($awayParticipants));
				array_splice($awayParticipants, -1, 0, array_pop($homeParticipants));
			}
		}
	}
	
	function onMatchOver($match)
	{
		// TODO how to give points ?
	}
	
	function onEnd()
	{
		
	}
}

?>
