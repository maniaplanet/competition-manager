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

class Elite extends Script
{
	public $name = 'Elite.Script.txt';
	public $fixedSlots = 2;
	/** @setting s Round duration (including when poles are capturable) */
	public $timeLimit = 60;
	/** @setting s How long poles are capturable */
	public $timePole = 15;
	/** @setting s How long it takes to capture a pole */
	public $captureLimit = 1.5;
	/** @setting none Rounds needed to win the map */
	public $roundsLimit = 6;
	/** @setting none Max rounds */
	public $roundsMax = 8;
	/** @setting none Max rounds on decider map */
	public $deciderRoundsMax = 16;
	/** @setting none Maps needed to win the match */
	public $mapsLimit = 2;
	/** @setting bool Use draft mode before match */
	public $useDraft = false;
	
	function getName()
	{
		return _('Elite');
	}
	
	function getInfo()
	{
		$info[] = _('Elite mode');
//		$info[] = _('1 offender has to capture the pole or eliminate all defenders');
		$info[] = sprintf(ngettext('Best of %d map', 'Best of %d maps', $this->mapsLimit), $this->mapsLimit*2-1);
		$info[] = sprintf(_('%d points to wins the map'), $this->roundsLimit);
		return $info;
	}
	
	function getTitle()
	{
		return 'SMStormElite@nadeolabs';
	}
	
	function getTeamSize()
	{
		return 3;
	}
	
	function getDefaultScore()
	{
		$score = new Scores\Detailed();
		$score->main = new Scores\Points();
		return $score;
	}
}

?>
