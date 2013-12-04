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

class Siege extends Script
{
	public $name = 'Siege.Script.txt';
	public $fixedSlots = 2;
	/** @setting none Max players per team */
	public $slotsPerTeam = 5;
	/** @setting s How long before a pole is capturable */
	public $timeLimit = 45;
	/** @setting s How long a pole is capturable */
	public $capturableLimit = 15;
	/** @setting s How long it takes to capture a pole */
	public $captureLimit = 5;
	/** @setting none Max rounds */
	public $roundsMax = 5;
	/** @setting none Maps needed to win the match */
	public $mapsLimit = 2;
	
	function getName()
	{
		return _('Siege');
	}
	
	function getInfo()
	{
		return _('Attackers has to capture as many poles as possible, most poles or first to capture all poles wins the map, first to "maps limit" wins the match');
	}
	
	function getTeamSize()
	{
		return $this->slotsPerTeam;
	}
	
	function getDefaultScore()
	{
		$score = new Scores\Detailed();
		$score->main = new Scores\Points();
		return $score;
	}
}

?>
