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

class CumulativeSMTimeAttack extends SMTimeAttack
{
	/** @setting s Map duration */
	public $timeLimit = 360;
	
	function getName()
	{
		return _('Cumulative Time Attack');
	}
	
	function getInfo()
	{
		return _('Results are accumulated between maps');
	}
	
	function getDefaultScore()
	{
		$score = new Scores\Counting();
		$score->main = new Scores\Detailed();
		$score->main->main = new Scores\Time();
		return $score;
	}
}

?>
