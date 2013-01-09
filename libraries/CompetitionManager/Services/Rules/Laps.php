<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

use DedicatedApi\Structures\GameInfos;
use ManiaLib\Gui\Elements\Icon;
use ManiaLib\Gui\Elements\Icons128x32_1;

class Laps extends AbstractRules
{
	public $gamemode = GameInfos::GAMEMODE_LAPS;
	/** @setting none Laps number (0 to use map default) */
	public $lapsLimit = 0;
	/** @setting ms Time limit to complete the number of laps (0 means no limit) */
	public $timeLimit = 0;
	/** @setting ms Time limit after the first cross the line (0 to disable, 1 for automatic) */
	public $finishTimeLimit = 1;
	/** @setting bool Allow or forbid respawn */
	public $disableRespawn = false;
	
	function getName()
	{
		return _('Laps');
	}
	
	function getInfo()
	{
		$info[] = _('Laps mode');
		if($this->lapsLimit)
			$info[] = sprintf(_('%d laps per map'), $this->lapsLimit);
		else
			$info[] = _('Number of laps depending on the map');
		return $info;
	}
	
	function getIcon()
	{
		return array(Icon::Icons128x32_1, Icons128x32_1::RT_Rounds);
	}
	
	function getTitle()
	{
		return 'TMCanyon';
	}
	
	function getDefaultDetails()
	{
		$details = new \CompetitionManager\Services\ScoreDetails\BasicDetails();
		$details->isTime = true;
		return $details;
	}
}

?>
