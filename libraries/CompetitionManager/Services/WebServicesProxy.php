<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

use ManiaLib\Gui\Elements\Icons128x128_1;
use Maniaplanet\WebServices as Ws;
use CompetitionManager\Utils\Formatting;

abstract class WebServicesProxy
{
	/**
	 * @return \ManiaLib\Application\Session
	 */
	static private function session()
	{
		return \ManiaLib\Application\Session::getInstance();
	}
	
	/**
	 * @return Ws\ManiaConnect\Player
	 */
	static private function oAuth()
	{
		static $oauth = null;
		
		if(!$oauth)
			$oauth = new Ws\ManiaConnect\Player();
		
		return $oauth;
	}
	
	/**
	 * @return Ws\Teams
	 */
	static private function teams()
	{
		static $teams = null;
		
		if(!$teams)
			$teams = new Ws\Teams();
		
		return $teams;
	}
	
	/**
	 * @return Ws\Competitions
	 */
	static private function competitions()
	{
		static $competitions = null;
		
		if(!$competitions)
			$competitions = new Ws\Competitions();
		
		return $competitions;
	}
	
	/**
	 * @return Ws\ManiaHome\ManialinkPublisher
	 */
	static private function publisher()
	{
		static $publisher = null;
		
		if(!$publisher)
			$publisher = new Ws\ManiaHome\ManialinkPublisher();
		
		return $publisher;
	}
	
	/**
	 * @return Ws\Links
	 */
	static private function links()
	{
		static $links = null;
		
		if(!$links)
			$links = new Ws\Links();
		
		return $links;
	}
	
	/**
	 * @return Ws\Titles
	 */
	static private function titles()
	{
		static $titles = null;
		
		if(!$titles)
			$titles = new Ws\Titles();
		
		return $titles;
	}
	
	/**
	 * @return Player
	 */
	static function getUser()
	{
		$player = self::session()->get('player');
		
		if($player === null)
		{
			$service = new ParticipantService();
			
			$player = new Player();
			$player->login = self::session()->login;
			$player->name = self::session()->nickname;
			$player->path = self::session()->path;
			$player->participantId = $service->createPlayer($player);
			
			$titles = array_map(function($t) { return $t->idString; }, self::oAuth()->getOwnedTitles());
			$service->updateTitles($player->participantId, $titles);
			
			self::session()->set('player', $player);
		}
		
		return $player;
	}
	
	/**
	 * @return Team[]
	 */
	static function getUserTeams()
	{
		$teams = self::session()->get('teams');
		
		if($teams === null)
		{
			$service = new ParticipantService();
			$teams = array();
			
			$teamsAdmin = self::oAuth()->getTeams();
			$teamsContract = self::oAuth()->getContracts();
			
			$rawTeams = array();
			foreach ($teamsAdmin as $team)
				$rawTeams[$team->id] = $team;
			foreach ($teamsContract as $contract)
				$rawTeams[$contract->team->id] = $contract->team;
			
			foreach($rawTeams as $rawTeam)
			{
				$team = new Team();
				$team->teamId = $rawTeam->id;
				$team->tag = $rawTeam->tag;
				$team->name = $rawTeam->name;
				$team->path = $rawTeam->zone->path;
				$team->city = $rawTeam->city;
				$team->participantId = $service->createTeam($team);
				
				$service->updateTitles($team->participantId, array($rawTeam->title->idString));
				
				$teams[uniqid()] = $team;
			}
			
			self::session()->set('teams', $teams);
		}
		
		return $teams;
	}
	
	static function getUserTeam($uniqId)
	{
		$teams = static::getUserTeams();
		
		if (array_key_exists($uniqId, $teams))
		{
			return $teams[$uniqId];
		}
		else
		{
			return false;
		}
	}
	
	static function resetUserTeams()
	{
		if (self::session()->exists('teams'))
		{
			self::session()->delete('teams');
			return true;
		}
		return false;
	}
	
	/**
	 * @return Team[]
	 */
	static function getUserContracts()
	{
		$teams = self::session()->get('contracts');
		
		if($teams === null)
		{
			$service = new ParticipantService();
			$teams = array();
			
			foreach(self::oAuth()->getContracts() as $rawContract)
			{
				$team = new Team();
				$team->teamId = $rawContract->team->id;
				$team->tag = $rawContract->team->tag;
				$team->name = $rawContract->team->name;
				$team->path = $rawContract->team->zone->path;
				$team->city = $rawContract->team->city;
				$team->participantId = $service->createTeam($team);
				
				$service->updateTitles($team->participantId, array($rawContract->team->title->idString));
				
				$teams[uniqid()] = $team;
			}
			
			self::session()->set('contracts', $teams);
		}
		
		return $teams;
	}
	
	static function getTeamPlayers($teamId)
	{
		$players = self::teams()->getContracts($teamId);
		return array_map(function($p) { return $p->login; }, $players);
	}
	
	static function getTitleName($title)
	{
		return self::titles()->get($title)->name;
	}
	
	// FIXME use Ws when available instead of hardcoding...
	static function getTitleEnvironment($title)
	{
		switch($title)
		{
			case 'TMValley':
				return 'Valley';
			case 'TMCanyon':
				return 'Canyon';
			case 'TMStadium':
				return 'Stadium';
			case 'SMStorm':
			case 'SMStormElite@nadeolabs':
			case 'SMStormHeroes@nadeolabs':
			case 'SMStormJoust@nadeolabs':
			case 'SMStormRoyal@nadeolabs':
				return 'Storm';
		}
	}
	
	/**
	 * @param int $competitionId
	 */
	static function onCreate($competitionId)
	{
		$service = new CompetitionService();
		$competition = $service->get($competitionId);

		$remoteId = self::competitions()->create($competition->name, $competition->title, (bool) $competition->isLan, Ws\Competitions::REGISTRATION_MODE_CLOSED);
		$service->setRemoteId($competitionId, $remoteId);
		
		$registrationStage = reset($competition->stages);
		$firstStage = $competition->getFirstPlayStage();
		$lastStage = end($competition->stages);
		self::competitions()->update(
				$remoteId, $competition->name, $competition->title, $competition->isLan, Ws\Competitions::REGISTRATION_MODE_CLOSED,
				$registrationStage->startTime->format('c'), $registrationStage->endTime->format('c'),
				$firstStage->startTime->format('c'), $lastStage->endTime->format('c'),
				$registrationStage->minSlots, $registrationStage->maxSlots, 1, 0
			);
		
		self::links()->createForCompetition($remoteId, $competition->getManialink(), $competition->name, Ws\Links::CATEGORY_REGISTRATION);
		self::links()->createForCompetition($remoteId, $competition->getManialink(), $competition->name, Ws\Links::CATEGORY_MANIALINK, true);
	}
	
	/**
	 * @param int $competitionId
	 * @param int $participantId
	 */
	static function onRegistration($competitionId, $participantId)
	{
		$service = new CompetitionService();
		$competition = $service->get($competitionId);
		$service = new ParticipantService();
		$participant = $service->get($participantId);
		
		if($competition->remoteId)
		{
			self::competitions()->inviteTeam($competition->remoteId, $participant->teamId);
			\ManiaLib\Application\Request::getInstance()->redirectAbsolute(
					'http://maniamatch.maniaplanet.com/competitions/do-validation/?'.http_build_query(array(
						'teamId' => $participant->teamId, 
						'competitionId' => $competition->remoteId,
						'redirectUrl' => \ManiaLib\Application\Request::getInstance()->createLinkArgList('..','c', 'external')
					))
				);
		}
		else if(!$competition->isTeam && \CompetitionManager\Config::getInstance()->postToManiaHome)
		{
			self::publisher()->postPersonalNotification(
					'You are registered in $<'.$competition->name.'$>',
					$participant->login,
					$competition->getManialink(),
					Icons128x128_1::Icons128x128_1,
					Icons128x128_1::Invite
				);
		}
	}
	
	/**
	 * @param int $competitionId
	 * @param int $participantId
	 */
	static function onUnregistration($competitionId, $participantId)
	{
		$service = new CompetitionService();
		$competition = $service->get($competitionId);
		$service = new ParticipantService();
		$participant = $service->get($participantId);
		
		if($competition->remoteId)
			self::competitions()->removeTeam($competition->remoteId, $participant->teamId);
	}
	
	/**
	 * @param int $matchId
	 */
	static function onMatchReady($matchId)
	{
		$service = new MatchService();
		$match = $service->get($matchId);
		$match->fetchParticipants();
		$service = new StageService();
		$stage = $service->get($match->stageId);
		$service = new CompetitionService();
		$competition = $service->get($stage->competitionId);
		
		if(!$competition->isScheduled() || $competition->isTeam || !$match->participants)
			return;
		
		if (\CompetitionManager\Config::getInstance()->postToManiaHome)
		{
			self::publisher()->postPrivateEvent(
					$match->name,
					$match->startTime->getTimestamp(),
					array_values(\ManiaLib\Utils\Arrays::getProperty($match->participants, 'login')),
					$match->getManialink()
				);
		}
	}
	
	/**
	 * @param int $competitionId
	 */
	static function onResults($competitionId)
	{
		$service = new CompetitionService();
		$competition = $service->get($competitionId);
		$lastStage = end($competition->stages);
		$lastStage->fetchParticipants();
		
		if($competition->remoteId)
		{
			$results = array();
			foreach($lastStage->participants as $participant)
			{
				if($participant->rank)
					$results[$participant->rank][] = $participant->teamId;
			}

			self::competitions()->registerResults($competition->remoteId, $results);
		}
		else if(!$competition->isTeam && \CompetitionManager\Config::getInstance()->postToManiaHome)
		{
			foreach($lastStage->participants as $participant)
			{
				self::publisher()->postPersonalNotification(
						'finished $<$o'.Formatting::ordinal($participant->rank).'$> of $<'.$competition->name.'$>',
						$participant->login,
						$competition->getManialink('results'),
						Icons128x128_1::Icons128x128_1,
						Icons128x128_1::Rankings
					);
			}
		}
	}
}

?>
