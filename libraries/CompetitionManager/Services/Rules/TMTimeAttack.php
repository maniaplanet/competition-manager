<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Services\Rules;

use DedicatedApi\Structures\GameInfos;
use ManiaLib\Gui\Elements\Icon;
use ManiaLib\Gui\Elements\Icons128x32_1;

abstract class TMTimeAttack extends AbstractRules
{
	public $gameMode = GameInfos::GAMEMODE_TIMEATTACK;
	
	function compare($scoreA, $scoreB)
	{
		if(!$scoreA)
			return $scoreB;
		if(!$scoreB)
			return -$scoreA;
		return $scoreA - $scoreB;
	}
	
	function getIcon()
	{
		return array(Icon::Icons128x32_1, Icons128x32_1::RT_TimeAttack);
	}
	
	function getTitle()
	{
		return 'TMCanyon';
	}
}

?>
