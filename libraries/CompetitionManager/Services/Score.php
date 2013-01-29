<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

abstract class Score
{
	const NATURAL = 1;
	const INVERSE = -1;
	
	/** @var int */
	protected $sort;
	
	function __construct($sort=self::INVERSE)
	{
		$this->sort = $sort;
	}
	
	/**
	 * @return bool
	 */
	function isVisible()
	{
		return false;
	}
	
	/**
	 * @return string
	 */
	function __toString()
	{
		return '';
	}
	
	/**
	 * @param Score $score
	 * @return int
	 */
	final function compareTo($score)
	{
		return $this->sort * $this->_compareTo($score);
	}
	
	protected function _compareTo($score)
	{
		return 0;
	}
	
	protected function _compareNullable($a, $b)
	{
		return $a === null ? $b : ($b === null ? -$a : $a - $b);
	}
}

?>
