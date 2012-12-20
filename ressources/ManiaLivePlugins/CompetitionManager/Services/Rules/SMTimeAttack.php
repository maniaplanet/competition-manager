<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8504 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 14:38:50 +0200 (lun., 15 oct. 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

abstract class SMTimeAttack extends Script
{
	public $name = 'TimeAttack.Script.txt';
	
	function compare($scoreA, $scoreB)
	{
		if(!$scoreA)
			return $scoreB;
		if(!$scoreB)
			return -$scoreA;
		return $scoreA - $scoreB;
	}
}

?>
