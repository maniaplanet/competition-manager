<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Scores;

class Detailed extends Composed
{
	/** @var \CompetitionManager\Services\Score[] */
	public $details = array();
	
	function add(Detailed $score)
	{
		$sum = parent::add($score);
		$sum->details = array_merge($this->details, $score->details);
		return $sum;
	}
	
	function _json_sleep()
	{
		parent::_json_sleep();
		$this->details = array_map('\CompetitionManager\Services\JSON::serialize', $this->details);
	}
	
	function _json_wakeup()
	{
		parent::_json_wakeup();
		$this->details = array_map('\CompetitionManager\Services\JSON::unserialize', $this->details);
	}
}

?>
