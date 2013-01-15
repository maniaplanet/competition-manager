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

class Team extends AbstractRules
{
	public $gameMode = GameInfos::GAMEMODE_TEAM;
	public $fixedSlots = 2;
	public $slotsPerTeam = 3;
	public $finishTimeLimit = 1;
	public $roundsLimit = 7;
	public $mapsLimit = 2;
	public $disableRespawn = false;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$dedicated->setFinishTimeout((int) $this->finishTimeLimit, true);
		$dedicated->setUseNewRulesTeam(true, true);
		$dedicated->setTeamPointsLimit((int) $this->roundsLimit, true);
		$dedicated->setMaxPointsTeam((int) 2*$this->slotsPerTeam, true);
		$dedicated->setDisableRespawn((bool) $this->disableRespawn, true);
		$dedicated->executeMulticall();
	}
	
	function getTeamSize()
	{
		return $this->slotsPerTeam;
	}
}

?>
