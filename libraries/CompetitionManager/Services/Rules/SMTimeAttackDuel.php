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

class SMTimeAttackDuel extends CumulativeSMTimeAttack
{
	public $fixedSlots = 2;
	/** @setting none Maps needed to win the match */
	public $mapsLimit = 2;
	
	function getName()
	{
		return _('Time Attack Duel');
	}
	
	function getInfo()
	{
		return _('1on1, best time wins the map, first to "maps limit" wins the match');
	}
	
	function getDefaultScore()
	{
		$score = new Scores\Detailed();
		$score->main = new Scores\Points();
		return $score;
	}
}

?>
