<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

class GuestListFileService extends \DedicatedManager\Services\DedicatedFileService
{
	function __construct()
	{
		$this->directory = \DedicatedManager\Config::getInstance()->dedicatedPath.'UserData/Config/GuestLists/';
		if(!file_exists($this->directory))
			mkdir($this->directory);
		$this->rootTag = '<guestlist>';
	}
	
	function get($filename)
	{
		if(!file_exists($this->directory.$filename.'.txt'))
		{
			throw new \InvalidArgumentException('File does not exists');
		}

		$listObj = simplexml_load_file($this->directory.$filename.'.txt');
		$logins = array();
		foreach($listObj->player as $player)
			$logins[] = (string) $player->login;
		
		return $logins;
	}
	
	function save($filename, $logins)
	{
		$dom = new \DOMDocument('1.0', 'utf-8');
		$guestlist = simplexml_import_dom($dom->createElement('guestlist'));
		
		foreach($logins as $login)
			$guestlist->addChild('player')->addChild('login', (string) $login);
		
		$guestlist->asXML($this->directory.$filename.'.txt');
	}
}

?>
