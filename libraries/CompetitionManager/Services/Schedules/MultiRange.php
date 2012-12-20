<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9040 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-04 13:05:22 +0100 (mar., 04 déc. 2012) $:
 */

namespace CompetitionManager\Services\Schedules;

class MultiRange extends AbstractSchedule
{
	public $startTime;
	public $timePeriod;
	public $nbMatches = 0;
	
	function getTimesLimit()
	{
		if($this->nbMatches)
		{
			$endTime = new \DateTime($this->startTime);
			$interval = \DateInterval::createFromDateString($this->timePeriod);
			for($i=0; $i<=$nbMatches; ++$i)
				$endTime = $endTime->add($interval);
		}
		else
			$endTime = null;
		return array($this->startTime, $endTime);
	}
}

?>
