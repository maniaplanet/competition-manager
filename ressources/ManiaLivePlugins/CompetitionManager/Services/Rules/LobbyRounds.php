<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class LobbyRounds extends Rounds
{
	public $pointsLimit = 100;
	public $scoringSystem = null;
	
	function configure(\Maniaplanet\DedicatedServer\Connection $dedicated)
	{
		$dedicated->setRoundPointsLimit((int) $this->pointsLimit, true);
		if($this->scoringSystem)
			$dedicated->setRoundCustomPoints($this->scoringSystem->points, false, true);
		$dedicated->executeMulticall();
	}
	
	function getNeededEvents()
	{
		return 0;
	}
	
	function _json_wakeup()
	{
		$this->scoringSystem = \ManiaLivePlugins\CompetitionManager\Services\JSON::unserialize($this->scoringSystem);
	}
}

?>
