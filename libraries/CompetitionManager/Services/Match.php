<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9148 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-19 16:57:08 +0100 (mer., 19 déc. 2012) $:
 */

namespace CompetitionManager\Services;

class Match extends \CompetitionManager\Services\AbstractObject
{
	/** @var int */
	public $matchId;
	/** @var string */
	public $name;
	/** @var int */
	public $stageId;
	/** @var \DateTime */
	public $startTime;
	/** @var \DateTime */
	public $endTime;
	/** @var Rules */
	public $rules;
	/** @var int */
	public $state;
	
	/** @var Participant[] */
	public $participants = array();
	/** @var Map[] */
	public $maps = array();
	/** @var Server */
	public $server = null;
	
	/** @var bool */
	private $participantsFetched = false;
	/** @var bool */
	private $mapsFetched = false;
	/** @var bool */
	private $serverFetched = false;
	
	function onFetchObject()
	{
		if($this->startTime)
			$this->startTime = new \DateTime($this->startTime);
		if($this->endTime)
			$this->endTime = new \DateTime($this->endTime);
		$this->rules = JSON::unserialize($this->rules);
		if(!$this->rules)
		{
			$service = new StageService();
			$this->rules = $service->get($this->stageId)->rules;
		}
	}
	
	function fetchParticipants()
	{
		if(!$this->participantsFetched)
		{
			$service = new ParticipantService();
			$this->participants = $service->getByMatch($this->matchId);
		}
		
		$this->participantsFetched = true;
	}
	
	function fetchMaps()
	{
		if(!$this->mapsFetched)
		{
			$service = new MapService();
			$this->maps = $service->getByMatch($this->matchId);
			if(empty($this->maps))
				$this->maps = $service->getByStage($this->stageId);
		}
		
		$this->mapsFetched = true;
	}
	
	function fetchServer()
	{
		if(!$this->serverFetched)
		{
			$service = new ServerService();
			$this->server = $service->getByMatch($this->matchId);
		}
		
		$this->serverFetched = true;
	}
	
	function getManialink()
	{
		$request = \ManiaLib\Application\Request::getInstance();
		$request->set('m', $this->matchId);
		$link = $request->createAbsoluteLinkArgList(\ManiaLib\Application\Config::getInstance()->manialink, 'm', 'external');
		$request->restore('m');
		
		return $link;
	}
	
	/**
	 * @param DedicatedAccount $accountObj
	 */
	private function createConfig($accountObj)
	{
		$service = new \DedicatedManager\Services\ConfigFileService();
		$stageService = new StageService();
		$stage = $stageService->get($this->stageId);
		$matchService = new MatchService();
		$isLan = $matchService->isLAN($this->matchId);

		$options = new \DedicatedManager\Services\ServerOptions();
		$options->name = '#'.$this->matchId.' '.($this->name ?: ($stage instanceof Stages\Lobby ? 'Lobby' : 'Match'));
		$options->nextMaxPlayers = (int) !$stage->previousId;
		$options->allowMapDownload = false;
		$options->autoSaveReplays = !($stage instanceof Stages\Lobby);
		$options->callVoteRatio = -1;
		$options->disableHorns = true;
		$options->hideServer = true;
		$options->isP2PDownload = false;
		$options->isP2PUpload = false;
		$options->nextLadderMode = 0;
		
		$account = new \DedicatedManager\Services\Account();
		$account->login = $accountObj->login;
		$account->password = $accountObj->password;

		$system = new \DedicatedManager\Services\SystemConfig();
		$system->allowSpectatorRelays = true;
		$system->guestlistFilename = 'GuestLists/competition.match-'.$this->matchId.'.txt';
		if($isLan)
			$system->forceIpAddress = 'LAN';
		$system->title = $this->rules->getTitle();

		$filename = $service->save('competition.match-'.$this->matchId, $options, $account, $system);
		
		\CompetitionManager\Utils\UnixPermission::fix($filename);
	}
	
	private function createGuestList()
	{
		$this->fetchParticipants();
		$service = new GuestListFileService();
		
		$logins = array();
		foreach($this->participants as $participant)
		{
			if($participant instanceof Player)
			{
				$logins[] = $participant->login;
			}
			elseif ($participant instanceof Team)
			{
				$participant->updatePlayers();
				$logins = array_merge($logins, $participant->players);
			}
		}

		$filename = $service->save('competition.match-'.$this->matchId, $logins);
		
		\CompetitionManager\Utils\UnixPermission::fix($filename);
	}
	
	private function createMatchSettings()
	{
		$service = new \DedicatedManager\Services\MatchSettingsFileService();
		$gameInfos = new \DedicatedManager\Services\GameInfos();
		$gameInfos->gameMode = $this->rules->gameMode;
		if($gameInfos->gameMode == \DedicatedManager\Services\GameInfos::GAMEMODE_SCRIPT)
			$gameInfos->scriptName = $this->rules->name;

		$this->fetchMaps();
		$maps = array_map(function ($m) { return $m->path.$m->filename; }, $this->maps);

		$filename = $service->save('competition.match-'.$this->matchId, $gameInfos, $maps);
		
		\CompetitionManager\Utils\UnixPermission::fix($filename);
	}
	
	/**
	 * @param DedicatedAccount $account
	 */
	function startServer($account)
	{
		$this->createConfig($account);
		$this->createGuestList();
		$this->createMatchSettings();
			
		$service = new ServerService();
		$server = new Server();
		$server->rpcHost = '127.0.0.1';
		$server->rpcPort = $service->start('competition.match-'.$this->matchId, 'competition.match-'.$this->matchId, false);
		$server->rpcPassword = 'SuperAdmin';
		
		sleep(10);
		
		$service->register($server);
		$service->useAccount($account->login, $server->rpcHost, $server->rpcPort);
		$service->assignMatch($server->rpcHost, $server->rpcPort, $this->matchId);
		$server->closeConnection();
	}
}

?>
