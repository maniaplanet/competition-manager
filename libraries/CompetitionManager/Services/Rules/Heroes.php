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

class Heroes extends Script
{
	public $name = 'Heroes.Script.txt';
	public $fixedSlots = 2;
	/** @setting s Round duration (including when poles are capturable) */
	public $timeLimit = 60;
	/** @setting s How long poles are capturable */
	public $capturableLimit = 15;
	/** @setting s How long the pole is capturable once the defense has been eliminated */
	public $noDefCapturableLimit = 10;
	/** @setting s How long it takes to capture a pole */
	public $captureLimit = 1.5;
	/** @setting none Rounds needed to win the map */
	public $roundsLimit = 10;
	/** @setting none Gap needed between teams */
	public $roundsGap = 2;
	/** @setting none Max rounds */
	public $roundsMax = 20;
	/** @setting none Maps needed to win the match */
	public $mapsLimit = 2;
	
	function getName()
	{
		return _('Heroes');
	}
	
	function getInfo()
	{
		return _('5on5, first to "rounds limit" wins the map, first to "maps limit" wins the match');
	}
	
	function getTitle()
	{
		return 'SMStormHeroes@nadeolabs';
	}
	
	function getTeamSize()
	{
		return 5;
	}
	
	function getDefaultScore()
	{
		$score = new Scores\Detailed();
		$score->main = new Scores\Points();
		return $score;
	}
}

?>
