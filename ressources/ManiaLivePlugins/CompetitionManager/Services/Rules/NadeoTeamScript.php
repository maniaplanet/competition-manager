<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

use Maniaplanet\DedicatedServer\Connection;
use ManiaLive\DedicatedApi\Callback;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\CompetitionManager\Event;
use ManiaLivePlugins\CompetitionManager\Services\Scores;

abstract class NadeoTeamScript extends Script
{
	function configure(Connection $dedicated)
	{
	}
	
	function configureWarmup(Connection $dedicated)
	{
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_UseScriptCallbacks'] = true;
		$dedicated->setModeScriptSettings($settings);
	}
	
	function getNeededEvents()
	{
		return Callback\Event::ON_MODE_SCRIPT_CALLBACK;
	}
	
	function onModeScriptCallback($param1, $param2)
	{
		static $mapScores = array(0, 0);
		static $matchScores = array(0,0);
		static $mapIndex = 1;
		
		switch($param1)
		{
			//Get results
			case 'LibXmlRpc_Scores':
				$matchScores = array($param2[0], $param2[1]);
				$mapScores = array($param2[2],$param2[3]);
				break;
			
			case 'LibXmlRpc_EndMap':
				$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
				$teamIds = array_keys($match->participants);
				foreach($mapScores as $index => $points)
				{
					$mapScore = new Scores\Points();
					$mapScore->points = $points;
					$match->participants[$teamIds[$index]]->score->details[] = $mapScore;
				}
				break;
			
			case 'LibXmlRpc_EndMatch':
				$winnerTeam = $param2->MatchWinnerClan-1;
				$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
				$teamIds = array_keys($match->participants);
				if(isset($teamIds[$winnerTeam]))
				{
					$match->participants[$teamIds[$winnerTeam]]->rank = 1;
					$match->participants[$teamIds[1 - $winnerTeam]]->rank = 2;
				}
				Dispatcher::dispatch(new Event(Event::ON_RULES_END_MATCH));
				break;
				
			case 'LibXmlRpc_EndMap':
				$mapIndex++;
				break;
		}
	}
	
	function onForfeit($winner, $forfeit)
	{
		parent::onForfeit($winner, $forfeit);
		$winner->score->points = $this->mapsLimit;
		$forfeit->score->points = null;
	}
}

?>
