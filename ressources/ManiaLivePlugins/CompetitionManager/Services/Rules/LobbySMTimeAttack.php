<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class LobbySMTimeAttack extends SMTimeAttack
{
	public $timeLimit = 360;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_TimeLimit'] = (int) $this->timeLimit;
		$dedicated->setModeScriptSettings($settings);
	}
	
	function getNeededEvents()
	{
		return 0;
	}
}

?>
