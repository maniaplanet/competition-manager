<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\LobbyControl;

use ManiaLive\DedicatedApi\Callback\Event as ServerEvent;
use ManiaLive\Gui\CustomUI;
use ManiaLive\Gui\Group;
use ManiaLivePlugins\CompetitionManager\Constants\State;
use ManiaLivePlugins\CompetitionManager\Services\Match;
use ManiaLivePlugins\CompetitionManager\Services\Participant;
use ManiaLivePlugins\CompetitionManager\Services\Player;
use ManiaLivePlugins\CompetitionManager\Services\Server;
use ManiaLivePlugins\CompetitionManager\Windows;

class LobbyControl extends \ManiaLive\PluginHandler\Plugin
{
	const PREFIX = 'Lobby$08fInfo$000»$8f0 ';
	
	/** @var Match */
	private $lobby;
	/** @var int[string] */
	private $quitters = array();
	/** @var int[string] */
	private $intruders = array();
	/** @var int[] */
	private $matchIds = array();
	
	/** @var \DateTime */
	private $nextTick;
	/** @var \DateInterval */
	private $tickInterval;
	/** @var bool */
	private $registrationsPaused = false;
	/** @var int */
	private $jumpsPrepared = false;
	
	function onInit()
	{
		$this->setVersion('1.0');
	}
	
	function onLoad()
	{
		$this->lobby = Match::getInstance();
		$this->nextTick = new \DateTime();
		$this->tickInterval = new \DateInterval('PT20S');
		
		// FIXME last API doesn't handle well TM at the moment...
		if(!($this->lobby->rules instanceof \ManiaLivePlugins\CompetitionManager\Services\Rules\Script))
			$this->connection->setApiVersion('2011-10-06');
		$this->lobby->rules->configure($this->connection);
		$this->connection->setServerName('[$<'.$this->lobby->stage->competition->name.'$>] '.($this->lobby->name ?: 'Lobby'));
		$this->connection->setHideServer(0);
		
		foreach($this->storage->players as $player)
			$this->onPlayerConnect($player->login, false);
		foreach($this->storage->spectators as $player)
			$this->onPlayerConnect($player->login, true);
		
		if(!$this->isLobbyOver())
			$this->enableDedicatedEvents(ServerEvent::ON_PLAYER_CONNECT | ServerEvent::ON_PLAYER_DISCONNECT);
		$this->enableTickerEvent();
		$this->enableDatabase();
	}
	
	function onReady()
	{
		CustomUI::HideForAll(CustomUI::CHALLENGE_INFO);
		Windows\Header::Create()->show();
		Windows\Status::Create()->show();
		$this->updateStatus();
		$gauge = Windows\Gauge::Create();
		$gauge->setLevel(count($this->lobby->participants));
		$gauge->show();
		if(!$this->isLobbyOver())
		{
			$register = Windows\Confirm::Create(Group::Create('intruders'));
			$register->set('Register', null, $this->lobby->stage->competition->getManialink('register'));
			$register->blink();
			$register->show();
		}
	}
	
	function onPlayerConnect($login, $isSpectator)
	{
		if(isset($this->lobby->participants[$login]))
		{
			if(isset($this->quitters[$login]))
				unset($this->quitters[$login]);
		}
		else
		{
			$this->intruders[$login] = 0;
			Group::Create('intruders')->add($login, true);
		}
	}
	
	function onPlayerDisconnect($login)
	{
		if(isset($this->lobby->participants[$login]))
			$this->quitters[$login] = 0;
		else if(isset($this->intruders[$login]))
		{
			unset($this->intruders[$login]);
			Group::Create('intruders')->remove($login);
		}
	}
	
	function onTick()
	{
		$now = new \DateTime();
		if($now > $this->nextTick)
		{
			$this->syncStates();
			
			if($this->isCompetitionOver())
				$this->closeServer();
			else if($this->isLobbyOver())
			{
				if($this->jumpsPrepared)
					$this->doJumps();
				else
				{
					$this->syncMatches();
					$this->prepareJumps();
				}
			}
			else
			{
				static $waitTicks = 0;
				$count = count($this->lobby->participants) - count($this->quitters);
				
				$this->syncParticipants();
				$gauge = Windows\Gauge::Create();
				$gauge->setLevel($count);
				$gauge->redraw();
				
				if($count >= $this->lobby->stage->minSlots)
				{
					if($waitTicks == 6 || $count == $this->lobby->stage->maxSlots)
						$this->closeRegistrations();
					else if($waitTicks == 0)
					{
						$countdown = Windows\CountDown::Create();
						$countdown->start(new \DateTime('2 minutes'));
						$countdown->show();
						$this->connection->chatSendServerMessage(self::PREFIX.'Required number of players reached, competition should start soon!');
					}
					
					++$waitTicks;
				}
				else if($waitTicks)
				{
					$waitTicks = 0;
					$this->connection->chatSendServerMessage(self::PREFIX.'Some players leaved... competition is postponed...');
				}
				
				if(count($this->lobby->participants) == $this->lobby->stage->maxSlots)
					$this->pauseRegistrations();
				else
					$this->resumeRegistrations();
			}
			
			$this->nextTick->add($this->tickInterval);
			$this->updateStatus();
		}
	}
	
	private function syncStates()
	{
		$this->lobby->stage->state = $this->db->execute(
				'SELECT state FROM Stages WHERE stageId=%d',
				$this->lobby->stageId
			)->fetchSingleValue();
		$this->lobby->stage->competition->state = $this->db->execute(
				'SELECT state FROM Competitions WHERE competitionId=%d',
				$this->lobby->stage->competitionId
			)->fetchSingleValue();
	}
	
	private function isLobbyOver()
	{
		return $this->lobby->stage->state >= State::OVER;
	}
	
	private function isCompetitionOver()
	{
		return $this->lobby->stage->competition->state >= State::OVER;
	}
	
	private function syncParticipants()
	{
		$this->lobby->updateParticipantList();
		
		foreach($this->lobby->participants as $key => $participant)
		{
			if($this->storage->getPlayerObject($participant->login))
			{
				unset($this->intruders[$participant->login]);
				unset($this->quitters[$participant->login]);
				Group::Create('intruders')->remove($participant->login);
			}
			else if(!isset($this->quitters[$participant->login]))
				$this->quitters[$participant->login] = 0;
			else if(++$this->quitters[$participant->login] == 3)
			{
				$this->unregisterParticipant($participant);
				unset($this->quitters[$participant->login]);
				unset($this->lobby->participants[$key]);
			}
		}
		
		if($this->registrationsPaused)
			return;
		
		foreach($this->intruders as $login => &$times)
		{
			switch(++$times)
			{
				case 1:
					$this->connection->chatSendServerMessage(self::PREFIX.'Welcome! If you did not register yet, please consider doing it to enter the competition.', $login);
					break;
				case 3:
					$this->connection->chatSendServerMessage(self::PREFIX.'Reminder: you need to register.', $login);
					break;
				case 5:
					$this->connection->chatSendServerMessage(self::PREFIX.'This is the last reminder: you cannot stay without being registered.', $login);
					break;
				case 7:
					$this->connection->kick($login, 'Please register before coming back');
					break;
			}
		}
	}
	
	private function unregisterParticipant($participant)
	{
		$this->db->execute(
				'DELETE FROM MatchParticipants WHERE matchId=%d AND participantId=%d',
				$this->lobby->matchId,
				$participant->participantId
			);
		$this->db->execute(
				'DELETE FROM StageParticipants WHERE stageId=%d AND participantId=%d',
				$this->lobby->stageId,
				$participant->participantId
			);
		$due = $this->db->execute(
				'SELECT SUM(IF(type & 0x80, -amount, amount)) '.
				'FROM Transactions '.
				'WHERE competitionId=%d AND login=%s AND type IN (0, 0x81) remoteId IS NOT NULL', // FIXME constants
				$this->lobby->stage->competitionId,
				$this->db->quote($participant->login)
			)->fetchSingleValue(0);
		if($due > 0)
			$this->db->execute(
					'INSERT INTO Transactions(competitionId, login, amount, type, message) VALUES (%d, %s, %d, %d, %s)',
					$this->lobby->stage->competitionId,
					$this->db->quote($participant->login),
					$due,
					0x81, // FIXME constant
					$this->db->quote(sprintf('Refund of registration in $<%s$> (reason: left before start)', $this->lobby->stage->competition->name))
				);
	}
	
	private function closeRegistrations()
	{
		$this->db->execute('UPDATE Stages SET state=%d WHERE stageId=%d', State::OVER, $this->lobby->stageId);
		$this->disableDedicatedEvents();
		$this->connection->chatSendServerMessage(self::PREFIX.'Competition is starting now!');
		Windows\Confirm::Erase(Group::Get('intruders'));
		Windows\CountDown::EraseAll();
		Windows\Gauge::EraseAll();
	}
	
	private function pauseRegistrations()
	{
		if(!$this->registrationsPaused)
		{
			$this->registrationsPaused = true;
			Windows\Confirm::Create(Group::Get('intruders'))->hide();
			if($this->intruders)
				$this->connection->chatSendServerMessage(
						self::PREFIX.'All slots have been taken but some participants are not on the lobby. If they are not coming back soon, you may be able to register.',
						array_keys($this->intruders)
					);
		}
	}
	
	private function resumeRegistrations()
	{
		if($this->registrationsPaused)
		{
			$this->registrationsPaused = false;
			Windows\Confirm::Create(Group::Get('intruders'))->show();
			if($this->intruders)
			{
				$this->connection->chatSendServerMessage(self::PREFIX.'Some slots has been released, you can try again to register.', array_keys($this->intruders));
				$this->intruders = array_fill_keys(array_keys($this->intruders), 1);
			}
		}
	}
	
	private function syncMatches()
	{
		// Handle finished matches
		$overIds = $this->db->execute(
				'SELECT M.matchId FROM Matches M INNER JOIN Stages USING(stageId) WHERE competitionId=%d AND M.state>%d',
				$this->lobby->stage->competitionId,
				State::OVER
			)->fetchArrayOfSingleValues();
		
		foreach(array_diff($overIds, $this->matchIds) as $overId)
			Group::Erase('match-'.$overId);
		$this->matchIds = array_diff($this->matchIds, $overIds);
		
		// Handle new matches
		$startedIds = $this->db->execute(
				'SELECT M.matchId '.
				'FROM Matches M '.
					'INNER JOIN Stages St USING(stageId) '.
					'INNER JOIN Servers Se USING(matchId) '.
				'WHERE St.competitionId=%d AND M.state=%d AND DATE_ADD(Se.startTime, INTERVAL 2 MINUTE) < NOW()',
				$this->lobby->stage->competitionId,
				State::STARTED
			)->fetchArrayOfSingleValues();
		
		foreach(array_diff($startedIds, $this->matchIds, array($this->lobby->matchId)) as $startedId)
		{
			$result = $this->db->execute(
					'SELECT Pa.participantId, Pl.*, T.* '.
					'FROM MatchParticipants MP '.
						'INNER JOIN Participants Pa USING(participantId) '.
						'LEFT JOIN Players Pl USING(login) '.
						'LEFT JOIN Teams T USING(teamId) '.
					'WHERE MP.matchId=%d',
					$startedId);
			
			$group = Group::Create('match-'.$startedId);
			foreach(Participant::arrayFromRecordSet($result) as $participant)
			{
				if($participant instanceof Player)
					$group->add($participant->login);
				else
				{
					// TODO
				}
			}
		}
		
		$this->matchIds = array_diff($startedIds, array($this->lobby->matchId));
	}
	
	private function prepareJumps()
	{
		$logins = array();
		foreach($this->matchIds as $matchId)
		{
			$result = $this->db->execute('SELECT * FROM Servers WHERE matchId=%d LIMIT 1', $matchId);
			if(!$result->recordAvailable())
				continue;
			$server = Server::fromRecordSet($result);
			
			$group = Group::Get('match-'.$matchId);
			$logins = array_merge($logins, array_intersect($group->toArray(), array_merge(array_keys($this->storage->players), array_keys($this->storage->spectators))));
			$jumper = Windows\ForceManialink::Create($group);
			$jumper->set($server->getLink());
		}
		
		$logins = array_unique($logins);
		if($logins)
		{
			$this->connection->chatSendServerMessage(
					self::PREFIX.'Your next match is ready, you will be transfered soon to the server. If something goes wrong, come back to the lobby.',
					$logins
				);
			$this->tickInterval = new \DateInterval('PT5S');
			$this->jumpsPrepared = true;
		}
		else
			$this->tickInterval = new \DateInterval('PT20S');
	}
	
	private function doJumps()
	{
		foreach(Windows\ForceManialink::GetAll() as $jumper)
			$jumper->show();
		
		$this->tickInterval = new \DateInterval('PT15S');
		$this->jumpsPrepared = false;
	}
	
	private function closeServer()
	{
		static $warn = 0;
		switch($warn++)
		{
			case 0:
				$this->db->execute('UPDATE Matches SET state=%d WHERE matchId=%d', State::OVER, $this->lobby->matchId);
				$this->connection->chatSendServerMessage(self::PREFIX.'Competition is over, lobby will be closed soon. Thanks for participating!');
				$this->tickInterval = new \DateInterval('PT30S');
				break;
			case 3:
				$jump = Windows\ForceManialink::Create();
				$jump->set($this->lobby->stage->competition->getManialink('results', false));
				$jump->show();
				$this->tickInterval = new \DateInterval('PT5S');
				break;
			case 4:
				$this->connection->stopServer();
		}
	}
	
	private function updateStatus()
	{
		$status = Windows\Status::Create();
		if($this->isCompetitionOver())
			$status->set('Over', 'f008');
		else if($this->isLobbyOver())
			$status->set('Playing', '0a08');
		else if($this->registrationsPaused)
			$status->set('Waiting', 'f508');
		else
			$status->set('Preparing', '08f8');
		$status->redraw();
	}
}

?>
