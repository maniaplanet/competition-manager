<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Scores;

class Time extends \CompetitionManager\Services\Score
{
	/** @var int */
	public $time;
	
	function __construct($order=self::NATURAL)
	{
		parent::__construct($order);
	}
	
	function isVisible()
	{
		return true;
	}
	
	function isNull()
	{
		return $this->time === null;
	}
	
	function compareTo(Time $score)
	{
		return $this->order * $this->_compareNullable($this->time, $score->time);
	}
	
	function add(Time $score)
	{
		$sum = parent::add($score);
		$sum->time = $this->time + $score->time;
		return $sum;
	}
	
	function __toString()
	{
		return $this->time ? \CompetitionManager\Utils\Formatting::milliseconds($this->time) : '-:--.---';
	}
}

?>
