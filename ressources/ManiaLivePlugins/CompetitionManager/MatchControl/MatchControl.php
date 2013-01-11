<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\MatchControl;

use ManiaLive\Data\Event as StorageEvent;
use ManiaLive\DedicatedApi\Callback\Event as ServerEvent;
use ManiaLive\Gui\ActionHandler;
use ManiaLive\Gui\CustomUI;
use ManiaLivePlugins\CompetitionManager\Constants;
use ManiaLivePlugins\CompetitionManager\Services\JSON;
use ManiaLivePlugins\CompetitionManager\Services\Match;
use ManiaLivePlugins\CompetitionManager\Windows;

class MatchControl extends \ManiaLive\PluginHandler\Plugin
{
	const WAIT_CANCEL  = -3;
	const WAIT_FORFEIT = -2;
	const WAIT	       = -1;
	const PREPARE      = 0;
	const READY        = 1;
	const PLAY         = 2;
	const OVER         = 3;
	const CANCELLED    = 4;
	
	const PREFIX = 'Match$08fInfo$000»$8f0 ';
	
	/** @var Match */
	private $match;
	/** @var int[string] */
	private $players = array();
	/** @var bool */
	private $isInWarmUp = false;
	
	/** @var int */
	private $state = self::PREPARE;
	/** @var \DateTime */
	private $nextTick = null;
	
	/** @var int */
	private $forfeitAction;
	
	function onInit()
	{
		$this->setVersion('1.0');
		$this->match = Match::getInstance();
		$this->nextTick = $this->match->availabilityTime;
	}
	
	function onLoad()
	{
		$this->enableDatabase();
		
		// FIXME last API doesn't handle well TM at the moment...
		if(!($this->match->rules instanceof \ManiaLivePlugins\CompetitionManager\Services\Rules\Script))
			$this->connection->setApiVersion('2011-10-06');
		$this->match->rules->configure($this->connection);
		$this->connection->setServerName('[$<'.$this->match->stage->competition->name.'$>] '.($this->match->name ?: 'Match'));
		if($this->match->stage->competition->isTeam)
		{
			$team1 = reset($this->match->participants);
			$team2 = next($this->match->participants);
			$this->connection->setTeamInfo($team1->name, 2/3, $team1->path, $team2->name, .0, $team2->path);
		}
		
		if($this->match->state < Constants\State::OVER)
		{
			foreach($this->storage->players as $player)
				$this->onPlayerConnect($player->login, false);
			foreach($this->storage->spectators as $player)
				$this->onPlayerConnect($player->login, true);

			$events = ServerEvent::ON_PLAYER_CONNECT;
			if($this->match->stage->competition->isTeam)
				$events |= ServerEvent::ON_PLAYER_INFO_CHANGED;
			if($this->match->rules->maxSlots == 2)
				$events |= ServerEvent::ON_PLAYER_DISCONNECT;

			$this->enableDedicatedEvents($events);
			$this->enableStorageEvents(StorageEvent::ON_PLAYER_CHANGE_SIDE);
		}
		else
			$this->over();
		$this->enableTickerEvent();
	}
	
	function onReady()
	{
		$this->forfeitAction = ActionHandler::getInstance()->createAction(array($this, 'confirmForfeit'));
		CustomUI::HideForAll(CustomUI::CHALLENGE_INFO);
		Windows\Progress::Create()->show();
		Windows\Status::Create()->show();
		$this->updateStatus();
	}
	
	function onTick()
	{
		if($this->nextTick > new \DateTime())
			return;
		
		switch($this->state)
		{
			case self::PREPARE:
				$this->state = self::READY;
				$this->nextTick = $this->match->startTime;
				if($this->match->startTime > new \DateTime())
				{
					$this->connection->chatSendServerMessage(self::PREFIX.'Match will start soon.');
					$countdown = Windows\CountDown::Create();
					$countdown->start($this->match->startTime);
					$countdown->show();
				}
				break;
				
			case self::READY:
				if($this->isEverybodyHere())
					$this->play();
				else if($this->match->rules->maxSlots == 2)
				{
					if(count($this->getMissing()) == 2)
						$this->waitCancel();
					else
						$this->waitForfeit();
				}
				else if($this->nextTick)
					$this->wait();
				else
					$this->play();
				break;
				
			case self::WAIT:
				if(!$this->players)
					$this->cancel();
				else
					$this->play();
				break;
				
			case self::WAIT_FORFEIT:
				$this->allowForfeit();
				break;
				
			case self::WAIT_CANCEL:
				$this->cancel();
				break;
				
			case self::OVER:
			case self::CANCELLED:
				$this->close();
		}
	}
	
	function onPlayerFinish($playerUid, $login, $timeOrScore)
	{
		$this->match->rules->onPlayerFinish($login, $timeOrScore);
	}
	
	function onEndRound()
	{
		if($this->match->rules->onEndRound())
			$this->connection->nextMap();
	}
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		static $firstCall = true;
		
		if(!$firstCall)
		{
			if($this->isInWarmUp)
				$this->isInWarmUp = false;
			else if($this->match->rules->onEndMatch($rankings, $winnerTeamOrMap))
				$this->over();
		}
		
		$firstCall = false;
	}
	
	function onBeginMap($map, $warmUp, $matchContinuation)
	{
		$this->isInWarmUp = (bool) $warmUp;
	}
	
	///////////////////////////////////////////////////////////////////////////
	// Participants handling
	///////////////////////////////////////////////////////////////////////////
	
	function onPlayerConnect($login, $isSpectator)
	{
		$isAllowed = false;
		$isSeveral = false;
		
		if($this->match->stage->competition->isTeam)
		{
			foreach($this->match->participants as $teamId => $team)
			{
				if(isset($team->players[$login]))
				{
					if($isAllowed)
					{
						$isSeveral = true;
						$isAllowed = false;
						break;
					}
					else
						$isAllowed = $teamId;
				}
			}
		}
		else
		{
			$isAllowed = isset($this->match->participants[$login]);
			if(!$isAllowed && $this->match->stage->type == Constants\StageType::OPEN_STAGE)
			{
				$this->match->updateParticipantList();
				$isAllowed = isset($this->match->participants[$login]);
			}
		}
		
		if($isAllowed)
		{
			if(is_int($isAllowed))
			{
				$this->match->participants[$isAllowed]->players[$login] = true;
				$this->players[$login] = array_search($isAllowed, array_keys($this->match->participants));
				$this->connection->forcePlayerTeam($login, $this->players[$login]);
			}
			else
				$this->players[$login] = -1;
			if($this->state < self::PREPARE)
			{
				if($this->isEverybodyHere())
					$this->play();
				else if($this->match->rules->maxSlots == 2 && count($this->getMissing()) == 1)
					$this->waitForfeit();
			}
		}
		else if($isSeveral)
		{
			$this->connection->chatSendServerMessage(self::PREFIX.'You are in both teams registered for this match. You cannot play thus have been forced to spectator mode.', $login);
			$this->connection->forceSpectator($login, 1);
		}
		else
		{
			if(!$isSpectator) // Shouldn't happen thanks to guestlist
				$this->connection->chatSendServerMessage(self::PREFIX.'You are not registered for this match. You have been forced to spectator mode.', $login);
			$this->connection->forceSpectator($login, 1);
		}
	}
	
	function onPlayerInfoChanged($playerInfo)
	{
		if($playerInfo['TeamId'] == -1)
			return;
		if(!isset($this->players[$playerInfo['Login']]))
			return;
		if($playerInfo['TeamId'] != $this->players[$playerInfo['Login']])
			$this->connection->forcePlayerTeam($playerInfo['Login'], $this->players[$playerInfo['Login']]);
	}
	
	function onPlayerChangeSide($player, $oldSide)
	{
		if($oldSide == 'player')
			$this->connection->spectatorReleasePlayerSlot($player->login);
	}
	
	function onPlayerDisconnect($login)
	{
		if(!isset($this->players[$login]))
			return;
		
		unset($this->players[$login]);

		foreach($this->match->participants as $team)
		{
			if(isset($team->players[$login]))
			{
				$team->players[$login] = false;
				break;
			}
		}
		
		switch(count($this->getMissing()))
		{
			case 1: $this->waitForfeit(); break;
			case 2: $this->waitCancel(); break;
		}
	}
	
	///////////////////////////////////////////////////////////////////////////
	// Utilities
	///////////////////////////////////////////////////////////////////////////
	
	private function play()
	{
		static $isStarted = false;
		
		if(!$isStarted)
		{
			$this->connection->chatSendServerMessage(self::PREFIX.'Match is starting!');
			$this->connection->nextMap();
			$isStarted = true;
		}
		else
			$this->connection->chatSendServerMessage(self::PREFIX.'Match is now playing again.');
			
		$this->state = self::PLAY;
		$this->updateStatus();
		Windows\CountDown::EraseAll();
		
		$this->enableDedicatedEvents($this->match->rules->getNeededEvents() | ServerEvent::ON_BEGIN_MAP);
		$this->disableTickerEvent();
	}
	
	private function wait()
	{
		$this->state = self::WAIT;
		$this->updateStatus();
		$this->nextTick = new \DateTime('2 minutes');
		$countdown = Windows\CountDown::Create();
		$countdown->start($this->nextTick);
		$countdown->show();
		$this->connection->chatSendServerMessage(self::PREFIX.'Waiting players a bit longer.');
	}
	
	private function waitForfeit()
	{
		switch($this->state)
		{
			case self::READY:
			case self::PLAY:
				$this->nextTick = new \DateTime('5 minutes');
				$countdown = Windows\CountDown::Create();
				$countdown->start($this->nextTick);
				$countdown->show();
				break;
			case self::WAIT_CANCEL:
				Windows\Confirm::EraseAll();
				$oneMinute = new \DateTime('1 minute');
				if($this->nextTick < $oneMinute)
				{
					$this->nextTick = $oneMinute;
					$countdown = Windows\CountDown::Create();
					$countdown->start($this->nextTick);
					$countdown->show();
				}
				break;
			default:
				return;
		}
		
		$this->state = self::WAIT_FORFEIT;
		$this->updateStatus();
		
		if($this->match->stage->competition->isTeam)
		{
			$missing = $this->getMissing();
			$presentRecipients = call_user_func_array('array_merge', array_map(function ($t) { return $t->getPresent(); }, array_diff_key($this->match->participants, $missing)));
			$missingRecipients = call_user_func_array('array_merge', array_map(function ($t) { return $t->getPresent(); }, $missing));
			$otherRecipients = array_diff(array_keys($this->storage->spectators), $presentRecipients, $missingRecipients);
			
			if($presentRecipients)
				$this->connection->chatSendServerMessage(self::PREFIX.'The other team is missing players. When the countdown ends, you will be able to declare a win by default.', $presentRecipients);
			if($missingRecipients)
				$this->connection->chatSendServerMessage(self::PREFIX.'Your team is missing players. When the countdown ends, the other team will be able to declare a win by default.', $missingRecipients);
			if($otherRecipients)
				$this->connection->chatSendServerMessage(self::PREFIX.'A team is missing players. When the countdown ends, the other one will be able to declare a win by default.', $otherRecipients);
		}
		else
		{
			$presentRecipients = array_keys($this->players);
			$otherRecipients = array_diff(array_keys($this->storage->spectators), $presentRecipients);
			if($presentRecipients)
				$this->connection->chatSendServerMessage(self::PREFIX.'Your opponent is missing. When the countdown ends, you will be able to declare a win by default.', $presentRecipients);
			if($otherRecipients)
				$this->connection->chatSendServerMessage(self::PREFIX.'An opponent is missing. When the countdown ends, the other one will be able to declare a win by default.', $otherRecipients);
		}
		$this->enableTickerEvent();
	}
	
	private function waitCancel()
	{
		switch($this->state)
		{
			case self::READY:
				$this->nextTick = new \DateTime('5 minutes');
				$countdown = Windows\CountDown::Create();
				$countdown->start($this->nextTick);
				$countdown->show();
				break;
			case self::WAIT_FORFEIT:
				Windows\Confirm::EraseAll();
				$oneMinute = new \DateTime('1 minute');
				if($this->nextTick < $oneMinute)
				{
					$this->nextTick = $oneMinute;
					$countdown = Windows\CountDown::Create();
					$countdown->start($this->nextTick);
					$countdown->show();
				}
				break;
			default:
				return;
		}
		
		$this->state = self::WAIT_CANCEL;
		$this->updateStatus();
		
		if($this->match->stage->competition->isTeam)
			$this->connection->chatSendServerMessage(self::PREFIX.'Both teams are missing players. If none is complete before the countdown ends, match will be cancelled.');
		else
			$this->connection->chatSendServerMessage(self::PREFIX.'Both opponents are missing. If no one connects before the countdown ends, match will be cancelled.');
		$this->enableTickerEvent();
	}
	
	private function allowForfeit()
	{
		Windows\CountDown::Create()->hide();
		if($this->match->stage->competition->isTeam)
		{
			$missing = $this->getMissing();
			$presentRecipients = call_user_func_array('array_merge', array_map(function ($t) { return $t->getPresent(); }, array_diff_key($this->match->participants, $missing)));
			$missingRecipients = call_user_func_array('array_merge', array_map(function ($t) { return $t->getPresent(); }, $missing));
			$otherRecipients = array_diff(array_keys($this->storage->spectators), $presentRecipients, $missingRecipients);
			
			if($presentRecipients)
				$this->connection->chatSendServerMessage(self::PREFIX.'You are now allowed to declare a win by default. You can also wait your opponent a bit longer if you like.', $presentRecipients);
			if($missingRecipients)
				$this->connection->chatSendServerMessage(self::PREFIX.'Other team is now allowed to declare a win by default.', $missingRecipients);
		}
		else
		{
			$presentRecipients = array_keys($this->players);
			$otherRecipients = array_diff(array_keys($this->storage->spectators), $presentRecipients);
			if($presentRecipients)
				$this->connection->chatSendServerMessage(self::PREFIX.'You are now allowed to declare a win by default. You can also wait your opponent a bit longer if you like.', $presentRecipients);
		}
		if($otherRecipients)
			$this->connection->chatSendServerMessage(self::PREFIX.'A win by default can now be declared.', $otherRecipients);
		
		foreach($presentRecipients as $recipient)
		{
			$confirm = Windows\Confirm::Create($recipient);
			$confirm->set('Confirm default win', $this->forfeitAction);
			$confirm->blink();
			$confirm->show();
		}
		$this->disableTickerEvent();
	}
	
	function confirmForfeit($login)
	{
		if(!isset($this->players[$login]))
			return;
		
		if($this->match->stage->competition->isTeam)
		{
			static $confirmed = array();
			if(!in_array($login, $confirmed))
				$confirmed[] = $login;
			if(count($confirmed) < $this->match->rules->getTeamSize())
			{
				$confirm = Windows\Confirm::Create($login);
				$confirm->set('Default win asked');
				$confirm->blink(false);
				$confirm->redraw();
				return;
			}
		}
		
		Windows\Confirm::EraseAll();
		$confirm = Windows\Confirm::Create();
		$confirm->set('Default win confirmed');
		$confirm->show();
		
		$missing = $this->getMissing();
		$winner = @reset(array_diff_key($this->match->participants, $missing));
		$winner->rank = 1;
		$winner->score = 1;
		foreach($missing as $participant)
			$participant->rank = null;
		$this->over();
	}
	
	private function cancel()
	{
		$this->state = self::CANCELLED;
		
		Windows\CountDown::EraseAll();
		$this->updateStatus();
		
		$this->db->execute('UPDATE Matches SET state=%d WHERE matchId=%d', Constants\State::OVER, $this->match->matchId);
		$this->connection->chatSendServerMessage(self::PREFIX.'Match has been cancelled...');
		$this->nextTick = new \DateTime('5 seconds');
		$this->disableDedicatedEvents();
		$this->disableStorageEvents();
	}
	
	private function over()
	{
		$this->state = self::OVER;
		
		Windows\CountDown::EraseAll();
		Windows\Confirm::EraseAll();
		$this->updateStatus();
		
		foreach($this->match->participants as $participant)
		{
			$this->db->execute(
					'UPDATE MatchParticipants SET rank=%s, score=%d, scoreDetails=%s WHERE matchId=%d AND participantId=%d',
					intval($participant->rank) ?: 'NULL',
					intval($participant->rank) ? $participant->score : 'NULL',
					$this->db->quote(JSON::serialize($participant->scoreDetails)),
					$this->match->matchId,
					$participant->participantId
				);
		}
		$this->db->execute('UPDATE Matches SET endTime=NOW(), state=%d WHERE matchId=%d', Constants\State::OVER, $this->match->matchId);
		$this->connection->chatSendServerMessage(self::PREFIX.'Match is over! Thanks for playing.');
		$this->nextTick = new \DateTime('5 seconds');
		$this->enableTickerEvent();
		$this->disableDedicatedEvents();
		$this->disableStorageEvents();
	}
	
	private function close()
	{
		static $hasJumped = false;
		static $hasBeenWarned = false;
		
		if(!$hasJumped)
		{
			if(!$hasBeenWarned)
			{
				if($this->match->stage->competition->getLobby())
					$this->connection->chatSendServerMessage(self::PREFIX.'You will be transfered back to the lobby in a few seconds.');
				$hasBeenWarned = true;
			}
			else
			{
				$jumper = Windows\ForceManialink::Create();
				if(($server = $this->match->stage->competition->getLobby()))
					$jumper->set($server->getLink());
				else
					$jumper->set($this->match->getManialink(false));
				$jumper->show();
				$hasJumped = true;
			}
		}
		else
		{
			$this->connection->cleanGuestList(true);
			$this->connection->saveGuestList('guestlist-trash.txt', true);
			$this->connection->loadGuestList('guestlist-trash.txt', true);
			$this->connection->stopServer(true);
			$this->connection->executeMulticall();
		}
	}
	
	private function getMissing()
	{
		if($this->match->stage->type == Constants\StageType::OPEN_STAGE)
			return array();
		
		if($this->match->stage->competition->isTeam)
		{
			$missing = array();
			$needed = $this->match->rules->getTeamSize();
			
			foreach($this->match->participants as $teamId => $team)
			{
				if(count($team->getPresent()) < $needed)
					$missing[$teamId] = $team;
			}
			
			return $missing;
		}
		
		return array_diff_key($this->match->participants, $this->players);
	}
	
	private function isEverybodyHere()
	{
		return !$this->getMissing();
	}
	
	private function updateStatus()
	{
		$status = Windows\Status::Create();
		switch($this->state)
		{
			case self::WAIT_CANCEL:
				$status->set('Waiting', 'f508');
				break;
			case self::WAIT_FORFEIT:
				$status->set('Waiting', 'fa08');
				break;
			case self::WAIT:
				$status->set('Waiting', 'ff08');
				break;
			case self::PREPARE:
				$status->set('Preparing', '08f8');
				break;
			case self::READY:
				$status->set('Preparing', '08f8');
				break;
			case self::PLAY:
				$status->set('Playing', '0a08');
				break;
			case self::OVER:
				$status->set('Over', 'f008');
				break;
			case self::CANCELLED:
				$status->set('Cancelled', 'f008');
				break;
		}
		$status->redraw();
	}
}

?>
