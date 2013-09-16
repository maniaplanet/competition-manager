<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9107 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-13 15:32:32 +0100 (jeu., 13 déc. 2012) $:
 */

namespace CompetitionManager\Services;

class Server extends \DedicatedManager\Services\Server
{
	/** @var \DateTime */
	public $startTime;
	/** @var int */
	public $matchId;
	
	protected function onFetchObject()
	{
		if($this->startTime)
			$this->startTime = new \DateTime($this->startTime);
	}
	
	function isReady()
	{
		$now = new \DateTime();
		return $this->startTime < $now->sub(new \DateInterval('PT2M'));
	}
	
	private function createConfig()
	{
		$service = new \DedicatedManager\Services\ManialiveFileService();
		$matchService = new MatchService();
		$isLobby = $matchService->isLobby($this->matchId);
		
		$competitionManagerConfig = \ManiaLib\Application\Config::getInstance();

		$dbConfig = \ManiaLib\Database\Config::getInstance();
		$wsConfig = \ManiaLib\WebServices\Config::getInstance();
		$mlConfig = \ManiaLib\Application\Config::getInstance();
		
		$config = new \DedicatedManager\Services\ManialiveConfig();
		if ($competitionManagerConfig->debug)
		{
			$config->config->debug = true;
		}
		$config->database->enable = true;
		$config->database->host = $dbConfig->host;
		$config->database->username = $dbConfig->user;
		$config->database->password = $dbConfig->password;
		$config->database->database = $dbConfig->database;
		$config->wsapi->username = $wsConfig->username;
		$config->wsapi->password = $wsConfig->password;
		$config->config->logsPrefix = 'match-'.$this->matchId;

		$config->plugins[] = $isLobby ? 'CompetitionManager\\LobbyControl' : 'CompetitionManager\\MatchControl';
		$config->__other = <<<CONFIG
alias competition = 'ManiaLivePlugins\CompetitionManager\Config'
competition.matchId = $this->matchId
competition.manialink = '$mlConfig->manialink'
CONFIG;
		$service->save('competition.match-'.$this->matchId, $config);
	}
	
	function startManialive()
	{
		$this->createConfig();
		
		$service = new \DedicatedManager\Services\ManialiveService();
		$service->start('competition.match-'.$this->matchId, array(
			'address' => $this->rpcHost,
			'rpcport' => $this->rpcPort,
			'password' => $this->rpcPassword,
		));
	}
}

?>
