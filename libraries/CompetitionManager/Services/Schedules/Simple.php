<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9040 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-04 13:05:22 +0100 (mar., 04 déc. 2012) $:
 */

namespace CompetitionManager\Services\Schedules;

class Simple extends AbstractSchedule
{
	public $startTime;
	
	function getTimesLimit()
	{
		return array($this->startTime, $this->startTime);
	}
}

?>
