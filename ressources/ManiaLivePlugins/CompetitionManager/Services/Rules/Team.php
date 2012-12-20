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
	public $maxSlots = 2;
	public $slotsPerTeam = 3;
	public $roundsLimit = 7;
	public $mapsLimit = 2;
	
	function getTeamSize()
	{
		return $this->slotsPerTeam;
	}
}

?>
