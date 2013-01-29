<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\CompetitionManager\Event;

class Joust extends Script
{
	public $name = 'Joust.Script.txt';
	public $fixedSlots = 2;
	public $timeLimit = 300;
	public $hitsLimit = 7;
	public $hitsGap = 2;
	public $hitsMax = 11;
	public $roundsLimit = 3;
	public $mapsLimit = 2;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_RoundTimeLimit'] = (int) $this->timeLimit;
		$settings['S_RoundPointsToWin'] = (int) $this->hitsLimit;
		$settings['S_RoundPointsGap'] = (int) $this->hitsGap;
		$settings['S_RoundPointsLimit'] = (int) $this->hitsMax;
		$settings['S_MatchPointsToWin'] = (int) $this->roundsLimit;
		$dedicated->setModeScriptSettings($settings);
	}
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		$logins = array_keys($match->participants);
		if( ($loginIndex = array_search($rankings[0]['Login'], $logins)) !== false )
		{
			if(++$match->participants[$logins[$loginIndex]]->score == $this->mapsLimit)
			{
				$match->participants[$logins[$loginIndex]]->rank = 1;
				$match->participants[$logins[1 - $loginIndex]]->rank = 2;
				Dispatcher::dispatch(new Event(Event::ON_RULES_END_MATCH));
			}
		}
	}
	
	function getForfeitWinnerScore()
	{
		return $this->mapsLimit;
	}
}

?>
