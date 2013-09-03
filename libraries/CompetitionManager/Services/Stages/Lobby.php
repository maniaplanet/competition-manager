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
use CompetitionManager\Services\Player;
use CompetitionManager\Services\StageService;

class Lobby extends \CompetitionManager\Services\Stage implements FirstCompliant, IntermediateCompliant
{
	function __construct()
	{
		$this->type = Constants\StageType::LOBBY;
		$this->schedule = new \CompetitionManager\Services\Schedules\Simple();
	}
	
	function getName()
	{
		if($this->state < Constants\State::STARTED)
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
		if($this->state > Constants\State::STARTED)
			return 'registrations';
		return 'lobby';
	}
	
	function onCreate()
	{
		$this->matches = array();
		$match = new Match();
		$match->stageId = $this->stageId;
		$match->name = 'Lobby';
		$match->startTime = $this->schedule->startTime;
		$service = new MatchService();
		$service->create($match);
		
		$this->matches[] = $match->matchId;
	}
	
	function onReady($participants)
	{
		$service = new MatchService();
		$service->setState($this->matches[0], Constants\State::READY);
	}
	
	function onRun() { /* Done in ManiaLive plugin */ }
	
	function onMatchOver($match)
	{
		$service = new MatchService();
		$service->setState($match->matchId, Constants\State::ARCHIVED);
	}
	
	function onEnd() {}
	
	///////////////////////////////////////////////////////////////////////////
	// Interfaces implementation
	///////////////////////////////////////////////////////////////////////////
	
	function onRegistration($participantId)
	{
		$service = new StageService();
		$service->assignParticipants($this->stageId, array($participantId), $this->getDefaultScore());
		$service = new MatchService();
		$service->assignParticipants(reset($this->matches), array($participantId), $this->rules->getDefaultScore());
		$service = new ParticipantService();
		$service->updateStageInfo($this->stageId, $participantId, rand(1, $this->maxSlots), $this->getDefaultScore());
		$participant = $service->get($participantId);
		
		$this->fetchMatches();
		reset($this->matches)->fetchServer();
		if( ($server = reset($this->matches)->server))
		{
			try
			{
				$server->openConnection();
				
				if($participant instanceof Player)
					$server->connection->addGuest($participant->login);
				else
				{
					// TODO
					\ManiaLib\Utils\Logger::info('unknown participant type:');
					\ManiaLib\Utils\Logger::info($participant);
				}
			}
			catch(\Exception $e) 
			{
				\ManiaLib\Application\ErrorHandling::logException($e);
			}
			
			$server->closeConnection();
		}
	}
	
	function onUnregistration($participantId)
	{
		// Done in ManiaLive plugin
	}
	
	function getPlaceholder($rank, $max)
	{
		return sprintf(_('#%d of random seed'), $rank);
	}
}

?>
