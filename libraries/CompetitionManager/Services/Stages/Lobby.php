<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9115 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-14 18:12:50 +0100 (ven., 14 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

use CompetitionManager\Constants\State;

class Lobby extends \CompetitionManager\Services\Stage
{
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::LOBBY;
		$this->schedule = new \CompetitionManager\Services\Schedules\Simple();
	}
	
	function getName()
	{
		if($this->state <= \CompetitionManager\Constants\State::STARTED)
			return _('Lobby');
		return _('Registrations');
	}
	
	function getInfo()
	{
		return null;
	}
	
	function getScheduleNames()
	{
		return array(_('Opening'));
	}
	
	function getIcon()
	{
		
	}
	
	function getAction()
	{
		if($this->state > State::STARTED)
			return 'registrations';
		return 'lobby';
	}
	
	function onCreate()
	{
		$this->matches = array();
		$match = new \CompetitionManager\Services\Match();
		$match->stageId = $this->stageId;
		$match->name = 'Lobby';
		$match->startTime = $this->schedule->startTime;
		$service = new \CompetitionManager\Services\MatchService();
		$service->create($match);
		
		$this->matches[] = $match->matchId;
	}
	
	function onReady($participants)
	{
		$service = new \CompetitionManager\Services\MatchService();
		$service->setState($this->matches[0], State::READY);
	}
	
	function onRegistration($participantId)
	{
		$service = new \CompetitionManager\Services\StageService();
		$service->assignParticipants($this->stageId, array($participantId));
		$service = new \CompetitionManager\Services\MatchService();
		$service->assignParticipants(reset($this->matches), array($participantId), null);
		$service = new \CompetitionManager\Services\ParticipantService();
		$service->updateStageInfo($this->stageId, $participantId, rand(1, $this->maxSlots), null, null);
	}
	
	function onRun() { /* Done in ManiaLive plugin */ }
	
	function onMatchOver($match)
	{
		$service = new \CompetitionManager\Services\MatchService();
		$service->setState($match->matchId, State::ARCHIVED);
	}
	
	function onEnd() {}
}

?>
