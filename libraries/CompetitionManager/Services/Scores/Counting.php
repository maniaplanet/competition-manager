<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Scores;

class Counting extends Composed
{
	/** @var int */
	public $count;
	
	function compareTo(Counting $score)
	{
		return $this->order * $this->_compareNullable($this->count, $score->count) ?: $this->main->compareTo($score->main);
	}
	
	function add(Counting $score)
	{
		$sum = parent::add($score);
		$sum->count = $this->count + $score->count;
		return $sum;
	}
}

?>
