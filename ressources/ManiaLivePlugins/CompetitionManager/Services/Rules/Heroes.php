<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class Heroes extends Script
{
	public $name = 'Heroes.Script.txt';
	public $fixedSlots = 2;
	public $timeLimit = 60;
	public $capturableLimit = 15;
	public $noDefCapturableLimit = 10;
	public $captureLimit = 1.5;
	public $roundsLimit = 10;
	public $roundsGap = 2;
	public $roundsMax = 20;
	public $mapsLimit = 2;
	
	function getTeamSize()
	{
		return 5;
	}
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_TimeLimit'] = (int) $this->timeLimit;
		$settings['S_TimePole'] = (int) $this->capturableLimit;
		$settings['S_TimePoleElimination'] = (int) $this->noDefCapturableLimit;
		$settings['S_TimeCapture'] = (float) $this->captureLimit;
		$settings['S_WinRound'] = (int) $this->roundsLimit;
		$settings['S_WinRoundGap'] = (int) $this->roundsGap;
		$settings['S_WinRoundLimit'] = (int) $this->roundsMax;
		$settings['S_WinMap'] = (int) $this->mapsLimit;
		$dedicated->setModeScriptSettings($settings);
	}
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		$teamIds = array_keys($match->participants);
		if(isset($teamIds[$winnerTeamOrMap]))
		{
			if(++$match->participants[$teamIds[$winnerTeamOrMap]]->score == $this->mapsLimit)
			{
				$match->participants[$teamIds[$winnerTeamOrMap]]->rank = 1;
				$match->participants[$teamIds[1 - $winnerTeamOrMap]]->rank = 2;
				return true;
			}
		}
		
		return false;
	}
}

?>
