<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Scores;

class Summary extends \ManiaLivePlugins\CompetitionManager\Services\Score
{
	/** @var \ManiaLivePlugins\CompetitionManager\Services\Score */
	public $main;
	/** @var int[] */
	public $summary = array();
	
	function isVisible()
	{
		return $this->main->isVisible();
	}
	
	function compareTo(Summary $score)
	{
		return $this->main->compareTo($score->main);
	}
	
	function add(Summary $score)
	{
		$sum = parent::add($score);
		$sum->main = $this->main->add($score->main);
		$sum->summary = array_merge($this->summary, $score->summary);
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
		$this->main = \ManiaLivePlugins\CompetitionManager\Services\JSON::serialize($this->main);
	}
	
	function _json_wakeup()
	{
		$this->main = \ManiaLivePlugins\CompetitionManager\Services\JSON::unserialize($this->main);
	}
}

?>
