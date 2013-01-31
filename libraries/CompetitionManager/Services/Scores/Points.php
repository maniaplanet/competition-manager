<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Scores;

class Points extends \CompetitionManager\Services\Score
{
	/** @var int */
	public $points;
	
	function isVisible()
	{
		return true;
	}
	
	function isNull()
	{
		return $this->points === null;
	}
	
	function compareTo(Points $score)
	{
		return $this->order * $this->_compareNullable($this->points, $score->points);
	}
	
	function add(Points $score)
	{
		$sum = parent::add($score);
		$sum->points = $this->points + $score->points;
		return $sum;
	}
	
	function __toString()
	{
		return $this->points === null ? '-' : (string) $this->points;
	}
}

?>
