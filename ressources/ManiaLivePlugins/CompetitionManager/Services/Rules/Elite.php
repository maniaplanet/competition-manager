<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class Elite extends Script
{
	public $name = 'Elite.Script.txt';
	public $maxSlots = 2;
	public $timeLimit = 60;
	public $timePole = 15;
	public $captureLimit = 1.5;
	public $roundsLimit = 6;
	public $roundsMax = 8;
	public $deciderRoundsMax = 16;
	public $mapsLimit = 2;
	public $useDraft = false;
	
	function getTeamSize()
	{
		return 3;
	}
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_TimeLimit'] = (int) $this->timeLimit;
		$settings['S_TimePole'] = (int) $this->timePole;
		$settings['S_TimeCapture'] = (float) $this->captureLimit;
		$settings['S_TurnWin'] = (int) $this->roundsLimit;
		$settings['S_TurnLimit'] = (int) $this->roundsMax;
		$settings['S_DeciderTurnLimit'] = (int) $this->deciderRoundsMax;
		$settings['S_MapWin'] = (int) $this->mapsLimit;
		$settings['S_UseDraft'] = (bool) $this->useDraft;
		$settings['S_DraftBanNb'] = (int) -1;
		$settings['S_DraftPickNb'] = (int) 2*$this->mapsLimit-1;
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
