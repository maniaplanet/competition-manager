<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

use CompetitionManager\Services\Scores;

class CumulativeRounds extends Rounds
{
	/** @setting ms Time limit after the first cross the line (0 to disable, 1 for automatic) */
	public $finishTimeLimit = 1;
	/** @setting /map How many rounds to play per map */
	public $roundsLimit = 5;
	/** @setting scoring Points to give depending on ranking at the end of each round */
	public $scoringSystem = null;
	/** @setting bool Allow or forbid respawn */
	public $disableRespawn = false;
	
	function getName()
	{
		return _('Cumulative Rounds');
	}
	
	function getInfo()
	{
		return _('Results are accumulated between maps');
	}
	
	function getDefaultScore()
	{
		$score = new Scores\Detailed();
		$score->main = new Scores\Points();
		return $score;
	}
	
	function _json_sleep()
	{
		$this->scoringSystem = \CompetitionManager\Services\JSON::serialize($this->scoringSystem);
	}
	
	function _json_wakeup()
	{
		$this->scoringSystem = \CompetitionManager\Services\JSON::unserialize($this->scoringSystem);
	}
}

?>
