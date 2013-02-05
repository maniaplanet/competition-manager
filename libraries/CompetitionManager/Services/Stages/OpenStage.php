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
use CompetitionManager\Services\Scores;

class OpenStage extends \CompetitionManager\Services\Stage implements FirstCompliant, IntermediateCompliant, LastCompliant
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
	
	function getDefaultScore()
	{
		if(count($this->matches) == 1)
			return new Scores\None();
		
		$score = new Scores\Counting();
		$score->main = $this->rules->getDefaultScore();
		return $score;
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
		$score = $this->rules->getDefaultScore();
		$service = new \CompetitionManager\Services\MatchService();
		foreach($this->matches as $matchId)
		{
			$service->assignParticipants($matchId, $this->participants, $score);
			$service->setState($matchId, State::READY);
			\CompetitionManager\Services\WebServicesProxy::onMatchReady($matchId);
		}
	}
	
	private function updateGuestLists()
	{
		foreach($this->matches as $match)
		{
			$service = new \CompetitionManager\Services\ServerService();
			$server = $service->getByMatch($match->matchId);
			if($server)
			{
				$match->createGuestList();
				$server->openConnection();
				$server->connection->loadGuestList('GuestLists\competition.match-'.$match->matchId.'.txt');
				$server->closeConnection();
			}
		}
	}
	
	private function updateScores()
	{
		$this->fetchMatches();
		$this->fetchParticipants();
		
		if(count($this->matches) == 1)
		{
			$this->matches[0]->fetchParticipants();
			foreach($this->matches[0]->participants as $participantId => $participant)
				$this->participants[$participantId] = $participant;
		}
		else
		{
			foreach($this->participants as $participant)
				$participant->score = $this->getDefaultScore();
			foreach($this->matches as $match)
			{
				$match->fetchParticipants();
				foreach($match->participants as $participantId => $participant)
					if(!$participant->score->isNull())
					{
						$this->participants[$participantId]->score->main = $this->participants[$participantId]->score->main->add($participant->score);
						++$this->participants[$participantId]->score->count;
					}
			}
		}
	}
	
	private function updateRanking()
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
	
	function onRun()
	{
		parent::onRun();
		
		if(!$this->previousId)
			$this->updateGuestLists();
		
		$this->updateScores();
		$this->updateRanking();
	}
	
	function onMatchOver($match)
	{
		$service = new \CompetitionManager\Services\MatchService();
		$service->setState($match->matchId, State::ARCHIVED);
	}
	
	function onEnd()
	{
		// Everything is already done in onRun()
	}
	
	///////////////////////////////////////////////////////////////////////////
	// Interfaces implementation
	///////////////////////////////////////////////////////////////////////////
	
	function onRegistration($participantId)
	{
		if($this->previousId)
			return;
		
		$matchDetails = $this->rules->getDefaultScore();
		$stageDetails = $this->getDefaultScore();
		
		$service = new \CompetitionManager\Services\StageService();
		$service->assignParticipants($this->stageId, array($participantId), $stageDetails, Qualified::NO);
		$service = new \CompetitionManager\Services\MatchService();
		foreach($this->matches as $matchId)
			$service->assignParticipants($matchId, array($participantId), $matchDetails);
	}
	
	function onUnregistration($participantId)
	{
		$service = new \CompetitionManager\Services\StageService();
		$service->excludeParticipants($this->stageId, array($participantId));
		$service = new \CompetitionManager\Services\MatchService();
		foreach($this->matches as $matchId)
			$service->excludeParticipants($matchId, array($participantId));
	}
	
	function getPlaceholder($rank, $max)
	{
		return sprintf(_('#%d of previous stage'), $rank);
	}
}

?>
