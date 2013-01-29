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
	
	function __construct($sort = self::NATURAL)
	{
		parent::__construct($sort);
	}
	
	function isVisible()
	{
		return true;
	}
	
	function __toString()
	{
		return $this->time ? \CompetitionManager\Utils\Formatting::milliseconds($this->time) : '-:--.---';
	}
	
	protected function _compareTo(Time $score)
	{
		return $this->_compareNullable($this->time, $score->time);
	}
}

?>
