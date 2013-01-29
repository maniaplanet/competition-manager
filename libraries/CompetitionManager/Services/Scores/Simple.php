<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Scores;

class Simple extends \CompetitionManager\Services\Score
{
	/** @var int */
	public $points;
	
	function isVisible()
	{
		return true;
	}
	
	function __toString()
	{
		return $this->points === null ? '-' : $this->points;
	}
	
	protected function _compareTo(Simple $score)
	{
		return $this->_compareNullable($this->points, $score->points);
	}
}

?>
