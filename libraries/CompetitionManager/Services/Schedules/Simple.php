<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9040 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-04 13:05:22 +0100 (mar., 04 déc. 2012) $:
 */

namespace CompetitionManager\Services\Schedules;

class Simple extends \CompetitionManager\Services\Schedule
{
	/** @var \DateTime */
	public $startTime;
	
	function getTimesLimit()
	{
		return array($this->startTime, $this->startTime);
	}
	
	function _json_sleep()
	{
		if(is_object($this->startTime))
			$this->startTime = $this->startTime->format('Y-m-d H:i:s');
	}
	
	function _json_wakeup()
	{
		if($this->startTime)
			$this->startTime = new \DateTime($this->startTime);
	}
}

?>
