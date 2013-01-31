<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

abstract class Score
{
	const NATURAL = 1;
	const INVERSE = -1;
	
	/** @var int */
	protected $order;
	
	function __construct($order=self::INVERSE)
	{
		$this->order = $order;
	}
	
	/**
	 * @return bool
	 */
	function isVisible()
	{
		return false;
	}
	
	/**
	 * @param Score $score
	 * @return int
	 */
	function compareTo($score)
	{
		return 0;
	}
	
	/**
	 * Compare two values which can be null (needs a special handling because of type conversions)
	 * @param numeric $a
	 * @param numeric $b
	 * @return int
	 */
	protected final function _compareNullable($a, $b)
	{
		return $a === null ? ($b === null ? 0 : 1) : ($b === null || $a < $b ? -1 : ($b < $a ? 1 : 0));
	}
	
	/**
	 * @param Score $score
	 * @return Score
	 */
	function add($score)
	{
		return new static;
	}
	
	/**
	 * @return string
	 */
	function __toString()
	{
		return '';
	}
}

?>
