<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class BattleWaves extends Script
{
	public $name = 'BattleWaves.Script.txt';
	public $fixedSlots = 2;
	public $slotsPerTeam = 5;
	public $waveLimit = 15;
	public $captureLimit = 30000;
	public $roundsLimit = 3;
	public $roundsGap = 2;
	public $roundsMax = 5;
	public $mapsLimit = 2;
	
	function getTeamSize()
	{
		return $this->slotsPerTeam;
	}
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_AutoBalance'] = false;
		$settings['S_WaveDuration'] = (int) $this->waveLimit;
		$settings['S_CaptureMaxValue'] = (int) $this->captureLimit;
		$settings['S_RoundsToWin'] = (int) $this->roundsLimit;
		$settings['S_RoundGapToWin'] = (int) $this->roundsGap;
		$settings['S_RoundsLimit'] = (int) $this->roundsMax;
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
