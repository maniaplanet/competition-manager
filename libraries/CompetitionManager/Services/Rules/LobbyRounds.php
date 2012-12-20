<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class LobbyRounds extends Rounds
{
	/** @setting none Points needed to change map */
	public $pointsLimit = 100;
	/** @setting scoring Points to give depending on ranking at the end of each round */
	public $scoringSystem = null;
	
	function getName()
	{
		return _('Rounds');
	}
	
	function getInfo()
	{
		return _('Classic Rounds');
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
