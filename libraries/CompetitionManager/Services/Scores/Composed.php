<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Scores;

abstract class Composed extends \CompetitionManager\Services\Score
{
	/** @var \CompetitionManager\Services\Score */
	public $main;
	
	function isVisible()
	{
		return $this->main->isVisible();
	}
	
	function isNull()
	{
		return $this->main->isNull();
	}
	
	function compareTo(Detailed $score)
	{
		return $this->main->compareTo($score->main);
	}
	
	function add(Detailed $score)
	{
		$sum = parent::add($score);
		$sum->main = $this->main->add($score->main);
		return $sum;
	}
	
	function __toString()
	{
		return (string) $this->main;
	}
	
	function __get($name)
	{
		return $this->main->$name;
	}
	
	function __set($name, $value)
	{
		return $this->main->$name = $value;
	}
	
	function _json_sleep()
	{
		$this->main = \CompetitionManager\Services\JSON::serialize($this->main);
	}
	
	function _json_wakeup()
	{
		$this->main = \CompetitionManager\Services\JSON::unserialize($this->main);
	}
}

?>