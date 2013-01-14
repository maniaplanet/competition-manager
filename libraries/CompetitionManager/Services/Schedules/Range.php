<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Services\Schedules;

class Range extends AbstractSchedule
{
	/** @var \DateTime */
	public $startTime;
	/** @var \DateTime */
	public $endTime;
	
	function getTimesLimit()
	{
		return array($this->startTime, $this->endTime);
	}
	
	function _json_sleep()
	{
		if(is_object($this->startTime))
			$this->startTime = $this->startTime->format('Y-m-d H:i:s');
		if(is_object($this->endTime))
			$this->endTime = $this->endTime->format('Y-m-d H:i:s');
	}
	
	function _json_wakeup()
	{
		if($this->startTime)
			$this->startTime = new \DateTime($this->startTime);
		if($this->endTime)
			$this->endTime = new \DateTime($this->endTime);
	}
}

?>
