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
	/** @setting s How long before offzone is automatically activated */
	public $offzoneAutoStartTime = 90;
	/** @setting s How long the offzone takes to shrink to its minimum */
	public $offzoneShrinkTime = 50;
	/** @setting s How long before end round once offzone is shrunk */
	public $timeLimit = 60;
	/** @setting bool Eliminated players respawn until offzone is activated */
	public $earlyRespawn = true;
	/** @setting s Interval between spawn waves */
	public $spawnWaveInterval = 5;
	
	function getName()
	{
		return _('Royal');
	}
	
	function getInfo()
	{
		$info[] = _('Royal mode');
		$info[] = sprintf(_('First to %d points wins the match'), $this->pointsLimit);
		return $info;
	}
	
	function getTitle()
	{
		return 'SMStormRoyal@nadeolabs';
	}
}

?>
