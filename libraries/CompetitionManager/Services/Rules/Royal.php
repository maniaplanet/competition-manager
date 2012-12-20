<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class Royal extends Script
{
	public $name = 'Royal.Script.txt';
	/** @setting none Points limit to win the match */
	public $pointsLimit = 200;
	/** @setting s How long it takes to activate the pole */
	public $offzoneActivationTime = 4;
	/** @setting s How long the offzone takes to shrink to its minimum */
	public $offzoneShrinkTime = 50;
	/** @setting s Interval between spawn waves */
	public $spawnWaveInterval = 5;
	
	function getName()
	{
		return _('Royal');
	}
	
	function getInfo()
	{
		return _('Free for all, survive, hit and capture the pole to score points, first to "points limit" wins the match');
	}
	
	function getTitle()
	{
		return 'SMStorm';
	}
}

?>
