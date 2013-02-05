<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Scores;

class Summary extends Composed
{
	/** @var int[] */
	public $summary = array();
	
	function add(Summary $score)
	{
		$sum = parent::add($score);
		$sum->summary = array_merge($this->summary, $score->summary);
		return $sum;
	}
}

?>
