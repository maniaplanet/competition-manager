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
use ManiaLivePlugins\CompetitionManager\Constants\Transaction;
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
	/** @var string[int] */
	private $matches = array();
	
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
	}
	
	function onPlayerConnect($login, $isSpectator)
	{
		if(isset($this->quitters[$login]))
			unset($this->quitters[$login]);
	}
	
	function onPlayerDisconnect($login)
	{
		$this->quitters[$login] = 0;
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
				unset($this->quitters[$participant->login]);
			else if(!isset($this->quitters[$participant->login]))
				$this->quitters[$participant->login] = 0;
			else if(++$this->quitters[$participant->login] == 3)
			{
				$this->unregisterParticipant($participant);
				unset($this->quitters[$participant->login]);
				unset($this->lobby->participants[$key]);
				unset($this->lobby->stage->participants[$key]);
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
				'SELECT SUM(IF(type & %d, -amount, amount)) '.
				'FROM Transactions '.
				'WHERE competitionId=%d AND login=%s AND type&~%1$d=%d AND remoteId IS NOT NULL',
				Transaction::REFUND,
				$this->lobby->stage->competitionId,
				$this->db->quote($participant->login),
				Transaction::REGISTRATION
			)->fetchSingleValue(0);
		if($due > 0)
			$this->db->execute(
					'INSERT INTO Transactions(competitionId, login, amount, type, message) VALUES (%d, %s, %d, %d, %s)',
					$this->lobby->stage->competitionId,
					$this->db->quote($participant->login),
					$due,
					Transaction::REGISTRATION | Transaction::REFUND,
					$this->db->quote(sprintf('Refund of registration in $<%s$> (reason: left before start)', $this->lobby->stage->competition->name))
				);
	}
	
	private function closeRegistrations()
	{
		$this->db->execute('UPDATE Stages SET state=%d WHERE stageId=%d', State::OVER, $this->lobby->stageId);
		$this->disableDedicatedEvents();
		$this->connection->chatSendServerMessage(self::PREFIX.'Competition is starting now!');
		Windows\CountDown::EraseAll();
		Windows\Gauge::EraseAll();
	}
	
	private function syncMatches()
	{
		// Handle finished matches
		$overIds = $this->db->execute(
				'SELECT M.matchId FROM Matches M INNER JOIN Stages USING(stageId) WHERE competitionId=%d AND M.state>%d',
				$this->lobby->stage->competitionId,
				State::OVER
			)->fetchArrayOfSingleValues();
		
		foreach(array_diff($overIds, array_keys($this->matches)) as $overId)
			Group::Erase('match-'.$overId);
		$this->matches = array_diff_key($this->matches, array_fill_keys($overIds, null));
		
		// Handle new matches
		$startedRaw = $this->db->execute(
				'SELECT M.matchId, M.name '.
				'FROM Matches M '.
					'INNER JOIN Stages St USING(stageId) '.
					'INNER JOIN Servers Se USING(matchId) '.
				'WHERE St.competitionId=%d AND M.state=%d AND DATE_ADD(Se.startTime, INTERVAL 2 MINUTE) < NOW()',
				$this->lobby->stage->competitionId,
				State::STARTED
			)->fetchArrayOfRow();
		$startedIds = array();
		foreach($startedRaw as $started)
			$startedIds[$started[0]] = $started[1];
		
		foreach(array_diff_key($startedIds, $this->matches, array($this->lobby->matchId => null)) as $startedId => $name)
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
		
		$this->matches = array_diff_key($startedIds, array($this->lobby->matchId => null));
	}
	
	private function prepareJumps()
	{
		foreach($this->matches as $matchId => $name)
		{
			$result = $this->db->execute('SELECT * FROM Servers WHERE matchId=%d LIMIT 1', $matchId);
			if(!$result->recordAvailable())
				continue;
			$server = Server::fromRecordSet($result);
			
			$group = Group::Get('match-'.$matchId);
			$message = Windows\BigMessage::Create($group);
			$message->set(sprintf('$bbbTransfer to $<$fff» %s «$> in', $name));
			$message->show();
			$countdown = Windows\AudioCountDown::Create($group);
			$countdown->start(5);
			$countdown->show();
			$jumper = Windows\ForceManialink::Create($group);
			$jumper->set($server->getLink());
			
			$this->jumpsPrepared = true;
		}
		
		if($this->jumpsPrepared)
			$this->tickInterval = new \DateInterval('PT5S');
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
