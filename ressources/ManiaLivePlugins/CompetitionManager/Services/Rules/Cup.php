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

class Cup extends AbstractRules
{
	public $gameMode = GameInfos::GAMEMODE_CUP;
	public $finishTimeLimit = 1;
	public $roundsLimit = 5;
	public $pointsLimit = 110;
	public $scoringSystem = null;
	public $disableRespawn = false;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$gameInfos = $dedicated->getCurrentGameInfo();
		$gameInfos->finishTimeout = (int) $this->finishTimeLimit;
		$gameInfos->cupRoundsPerMap = (int) $this->roundsLimit;
		$gameInfos->cupPointsLimit = (int) $this->pointsLimit;
		$gameInfos->disableRespawn = (bool) $this->disableRespawn;
		$dedicated->setGameInfos($gameInfos);
		
		if($this->scoringSystem)
			$dedicated->setRoundCustomPoints($this->scoringSystem->points);
	}
	
	function _json_wakeup()
	{
		$this->scoringSystem = \CompetitionManager\Services\JSON::unserialize($this->scoringSystem);
	}
}

?>
