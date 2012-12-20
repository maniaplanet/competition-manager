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
	public $pointsLimit = 5;
	public $scoringSystem = null;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		if($this->scoringSystem)
			$dedicated->setRoundCustomPoints($this->scoringSystem->points);
	}
	
	function getNeededEvents()
	{
		return 0;
	}
	
	function _json_wakeup()
	{
		$this->scoringSystem = \CompetitionManager\Services\JSON::unserialize($this->scoringSystem);
	}
}

?>
