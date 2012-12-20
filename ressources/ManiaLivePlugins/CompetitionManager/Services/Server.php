<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 125 $:
 * @author      $Author: martin.gwendal $:
 * @date        $Date: 2012-09-24 12:59:22 +0200 (lun., 24 sept. 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

class Server extends AbstractObject
{
	/** @var string */
	public $login;
	/** @var string */
	public $name;
	/** @var string */
	public $titleId;
	/** @var string */
	public $rpcHost;
	/** @var int */
	public $rpcPort;
	/** @var string */
	public $rpcPassword;
	/** @var string */
	public $joinIp;
	/** @var int */
	public $joinPort;
	/** @var string */
	public $joinPassword;
	/** @var string */
	public $specPassword;
	/** @var bool */
	public $isRelay;
	
	protected function onFetchObject()
	{
		$connection = \DedicatedApi\Connection::factory($this->rpcHost, $this->rpcPort, 5, 'SuperAdmin', $this->rpcPassword);
		$info = $connection->getSystemInfo();
		$this->login = $info->serverLogin;
		$this->titleId = $info->titleId;
		$this->joinIp = $info->publishedIp;
		$this->joinPort = $info->port;
		$this->joinPassword = $connection->getServerPassword();
		$this->specPassword = $connection->getServerPasswordForSpectator();
		$this->isRelay = $connection->isRelayServer();
		\DedicatedApi\Connection::delete($this->rpcHost, $this->rpcPort);
	}
	
	/**
	 * @return string
	 */
	function getLink($method='qjoin')
	{
		$isLan = preg_match('/_\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}_\d{1,5}/', $this->login);
		$password = preg_match('/^q?join$/i', $method) ? $this->joinPassword : $this->specPassword;
		return 'maniaplanet://#'.$method.'='.($isLan ? $this->joinIp : $this->login).($password ? ':'.$password : '').'@'.$this->titleId;
	}
}

?>