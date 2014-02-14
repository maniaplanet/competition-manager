<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class LobbyJoust extends Script
{
	public $name = 'Joust.Script.txt';
	
	function configure(\Maniaplanet\DedicatedServer\Connection $dedicated)
	{
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_UseLobby'] = true;
		$dedicated->setModeScriptSettings($settings);
	}
	
	function getNeededEvents()
	{
		return 0;
	}
}

?>
