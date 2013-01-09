<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class LobbyTMTimeAttack extends TMTimeAttack
{
	public $timeLimit = 300000;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$dedicated->setTimeAttackLimit((int) $this->timeLimit);
	}
	
	function getNeededEvents()
	{
		return 0;
	}
}

?>
