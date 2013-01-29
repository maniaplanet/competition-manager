<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

use DedicatedApi\Structures\GameInfos;
use ManiaLib\Gui\Elements\Icon;
use ManiaLib\Gui\Elements\Icons128x32_1;

class Cup extends \CompetitionManager\Services\Rules
{
	public $gameMode = GameInfos::GAMEMODE_CUP;
	/** @setting ms Time limit after the first cross the line (0 to disable, 1 for automatic) */
	public $finishTimeLimit = 1;
	/** @setting /map How many rounds to play per map */
	public $roundsLimit = 5;
	/** @setting none Points needed to become finalist */
	public $pointsLimit = 110;
	/** @setting scoring Points to give depending on ranking at the end of each round */
	public $scoringSystem = null;
	/** @setting bool Allow or forbid respawn */
	public $disableRespawn = false;
	
	function getName()
	{
		return _('Cup');
	}
	
	function getInfo()
	{
		return _('Results are accumulated between maps, first to "points limit" become a finalist and need to win a round to finish');
	}
	
	function getIcon()
	{
		return array(Icon::Icons128x32_1, Icons128x32_1::RT_Cup);
	}
	
	function getTitle()
	{
		return 'TMCanyon';
	}
	
	function _json_sleep()
	{
		$this->scoringSystem = \CompetitionManager\Services\JSON::serialize($this->scoringSystem);
	}
	
	function _json_wakeup()
	{
		$this->scoringSystem = \CompetitionManager\Services\JSON::unserialize($this->scoringSystem);
	}
}

?>
