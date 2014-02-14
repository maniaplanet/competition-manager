<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

use DedicatedApi\Structures\GameInfos;

class Cup extends \ManiaLivePlugins\CompetitionManager\Services\Rules
{
	public $gameMode = GameInfos::GAMEMODE_CUP;
	public $finishTimeLimit = 1;
	public $roundsLimit = 5;
	public $pointsLimit = 110;
	public $scoringSystem = null;
	public $disableRespawn = false;
	
	function configure(\Maniaplanet\DedicatedServer\Connection $dedicated)
	{
		$dedicated->setFinishTimeout((int) $this->finishTimeLimit, true);
		$dedicated->setCupRoundsPerMap((int) $this->roundsLimit, true);
		$dedicated->setCupPointsLimit((int) $this->pointsLimit, true);
		if($this->scoringSystem)
			$dedicated->setRoundCustomPoints($this->scoringSystem->points, false, true);
		$dedicated->setDisableRespawn((bool) $this->disableRespawn, true);
		$dedicated->executeMulticall();
	}
	
	function _json_wakeup()
	{
		$this->scoringSystem = \ManiaLivePlugins\CompetitionManager\Services\JSON::unserialize($this->scoringSystem);
	}
}

?>
