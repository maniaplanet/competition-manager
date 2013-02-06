<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9115 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-14 18:12:50 +0100 (ven., 14 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

use CompetitionManager\Constants;
use CompetitionManager\Services\Match;
use CompetitionManager\Services\MatchService;
use CompetitionManager\Services\ParticipantService;
use CompetitionManager\Services\StageService;

class SingleMatch extends \CompetitionManager\Services\Stage implements IntermediateCompliant, LastCompliant
{
	function __construct()
	{
		$this->type = Constants\StageType::SINGLE_MATCH;
		$this->schedule = new \CompetitionManager\Services\Schedules\Simple();
	}
	
	function getName()
	{
		return _('Match');
	}
	
	function getInfo()
	{
		if($this->rules->fixedSlots == 2)
			$info[] =  sprintf(_('%d on %1$d match'), $this->rules->getTeamSize() ?: 1);
		$info[] = _('Rules');
		$info[] = $this->rules->getInfo();
		return $info;
	}
	
	function getScheduleNames()
	{
		return array(_('Match time'));
	}
	
	function getIcon()
	{
		
	}
	
	function getAction()
	{
		return 'match';
	}
	
	function onCreate()
	{
		$this->matches = array();
		$match = new Match();
		$match->stageId = $this->stageId;
		$match->name = $this->rules->getName().' Match';
		$match->startTime = $this->schedule->startTime;
		$service = new MatchService();
		$service->create($match);
		
		$this->matches[] = $match->matchId;
	}
	
	function onReady($participants)
	{
		$this->participants = $participants;
		$service = new MatchService();
		$service->assignParticipants(reset($this->matches), $this->participants, $this->rules->getDefaultScore());
		$service->setState(reset($this->matches), Constants\State::READY);
		\CompetitionManager\Services\WebServicesProxy::onMatchReady(reset($this->matches));
	}
	
	function onMatchOver($match)
	{
		$match->fetchParticipants();
		$service = new ParticipantService();
		foreach($match->participants as $participantId => $participant)
			$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score);
		
		$service = new MatchService();
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
		return sprintf(_('%d of previous stage'), $rank);
	}
}

?>
