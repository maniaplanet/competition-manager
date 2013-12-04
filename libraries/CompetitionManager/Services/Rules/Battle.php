<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class Battle extends Script
{
	public $name = 'Battle.Script.txt';
	public $fixedSlots = 2;
	/** @setting none Max players per team */
	public $slotsPerTeam = 5;
	/** @setting s How long a wave lasts */
	public $waveLimit = 15;
	/** @setting ms How long it takes to capture a pole */
	public $captureLimit = 30000;
	/** @setting none Rounds needed to win the map */
	public $roundsLimit = 3;
	/** @setting none Gap needed between teams */
	public $roundsGap = 2;
	/** @setting none Max rounds */
	public $roundsMax = 5;
	/** @setting none Maps needed to win the match */
	public $mapsLimit = 2;
	
	function getName()
	{
		return _('Battle Waves');
	}
	
	function getInfo()
	{
		return _('First to capture all opponent\'s poles wins the round, first to "rounds limit" wins the map, first to "maps limit" wins the match');
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
