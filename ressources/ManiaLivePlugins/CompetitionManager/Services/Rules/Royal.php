<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class Royal extends Script
{
	public $name = 'Royal.Script.txt';
	public $pointsLimit = 200;
	public $offzoneActivationTime = 4;
	public $offzoneShrinkTime = 50;
	public $spawnWaveInterval = 5;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_MapPointsLimit'] = (int) $this->pointsLimit;
		$settings['S_OffZoneActivationTime'] = (int) $this->offzoneActivationTime;
		$settings['S_OffZoneTimeLimit'] = (int) $this->offzoneShrinkTime;
		$settings['S_SpawnInterval'] = (int) $this->spawnWaveInterval;
		$dedicated->setModeScriptSettings($settings);
	}
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		foreach($rankings as $ranking)
		{
			if(isset($match->participants[$ranking['Login']]))
				$match->participants[$ranking['Login']]->rank = $ranking['Rank'];
		}
		
		return true;
	}
}

?>
