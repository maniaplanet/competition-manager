<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9065 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-06 18:09:45 +0100 (jeu., 06 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

use CompetitionManager\Constants\Qualified;
use CompetitionManager\Constants\State;

class OpenStage extends \CompetitionManager\Services\Stage
{
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::OPEN_STAGE;
		$this->schedule = new \CompetitionManager\Services\Schedules\Range();
	}
	
	function getName()
	{
		if($this->nextId)
		{
			if($this->previousId)
				return _('Qualifiers');
			else
				return _('Open Qualifiers');
		}
		return _('Open Match');
	}
	
	function getInfo()
	{
		
	}
	
	function getScheduleNames()
	{
		return array(_('Playing period'));
	}
	
	function getIcon()
	{
		
	}
	
	function getAction()
	{
		if($this->nextId)
		{
			if($this->previousId)
				return 'qualifiers';
			else
				return 'openQualifiers';
		}
		return 'openMatch';
	}
	
	function getDefaultDetails()
	{
		$matchDetails = $this->rules->getDefaultDetails();
		if(count($this->matches) == 1)
			return $matchDetails;
		$details = new \CompetitionManager\Services\ScoreDetails\MapsCount();
		$details->isTime = $matchDetails->isTime;
		return $details;
	}
	
	function onCreate()
	{
		$this->matches = array();
		$service = new \CompetitionManager\Services\MatchService();
		$mapService = new \CompetitionManager\Services\MapService();
		foreach($this->maps as $map)
		{
			$match = new \CompetitionManager\Services\Match();
			$match->name = 'Qualifier on '.$mapService->get($map)->name;
			$match->stageId = $this->stageId;
			$match->startTime = $this->schedule->startTime;
			$match->endTime = $this->schedule->endTime;
			$service->create($match);
			$service->assignMaps($match->matchId, array($map));
			
			$this->matches[] = $match->matchId;
		}
	}
	
	function onReady($participants)
	{
		$this->participants = $participants;
		$scoreDetails = $this->rules->getDefaultDetails();
		$service = new \CompetitionManager\Services\MatchService();
		foreach($this->matches as $matchId)
		{
			$service->assignParticipants($matchId, $this->participants, $scoreDetails);
			$service->setState($matchId, State::READY);
			\CompetitionManager\Services\WebServicesProxy::onMatchReady($matchId);
		}
	}
	
	function onRegistration($participantId)
	{
		if($this->previousId)
			return false;
		
		$matchDetails = $this->rules->getDefaultDetails();
		$stageDetails = $this->getDefaultDetails();
		
		$service = new \CompetitionManager\Services\StageService();
		$service->assignParticipants($this->stageId, array($participantId), $stageDetails, Qualified::NO);
		$service = new \CompetitionManager\Services\MatchService();
		foreach($this->matches as $matchId)
			$service->assignParticipants($matchId, array($participantId), $matchDetails);
		
		return true;
	}
	
	function onRun()
	{
		parent::onRun();
		$this->fetchMatches();
		
		// Update guestlists if stage is merged with registrations
		if(!$this->previousId)
		{
			foreach($this->matches as $match)
			{
				$service = new \CompetitionManager\Services\ServerService();
				$server = $service->getByMatch($match->matchId);
				if($server)
				{
					$match->createGuestList(true);
					$connection = \DedicatedApi\Connection::factory($server->rpcHost, $server->rpcPort, 5, 'SuperAdmin', $server->rpcPassword);
					$connection->loadGuestList('GuestLists\competition.match-'.$match->matchId.'.txt');
					$server->closeConnection();
				}
			}
		}
		
		// Update scores
		if(count($this->matches) == 1)
		{
			$this->matches[0]->fetchParticipants();
			foreach($this->matches[0]->participants as $participantId => $participant)
				$this->participants[$participantId] = $participant;
			
			$rules = $this->rules;
			uasort($this->participants, function($p1, $p2) use($rules) { return $rules->compare($p1->score, $p2->score); });
		}
		else
		{
			$this->fetchParticipants();
			foreach($this->participants as $participant)
			{
				$participant->score = null;
				$participant->scoreDetails->nbMaps = 0;
			}
			foreach($this->matches as $match)
			{
				$match->fetchParticipants();
				foreach($match->participants as $participantId => $participant)
				{
					if($participant->score > 0)
					{
						$this->participants[$participantId]->score += $participant->score;
						++$this->participants[$participantId]->scoreDetails->nbMaps;
					}
				}
			}
		
			$rules = $this->rules;
			uasort($this->participants,
					function($p1, $p2) use($rules)
					{
						$mapsDiff = $p2->scoreDetails->nbMaps - $p1->scoreDetails->nbMaps;
						if($mapsDiff)
							return $mapsDiff;
						return $rules->compare($p1->score, $p2->score);
					});
		}
		
		// Update ranks
		$rank = 1;
		if($this->nextId)
		{
			$service = new \CompetitionManager\Services\StageService();
			$nextPlayersLimit = $service->get($this->nextId)->maxSlots;
		}
		$service = new \CompetitionManager\Services\ParticipantService();
		foreach($this->participants as $participantId => $participant)
		{
			if($participant->score === null)
				break;
			$participant->rank = $rank++;
			$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score, $participant->scoreDetails);
			if($this->nextId)
				$service->setStageQualification($this->stageId, $participantId, $participant->rank > $nextPlayersLimit ? Qualified::NO : Qualified::YES);
		}
	}
	
	function onMatchOver($match)
	{
		$service = new \CompetitionManager\Services\MatchService();
		$service->setState($match->matchId, State::ARCHIVED);
	}
	
	function onEnd()
	{
		
	}
}

?>
