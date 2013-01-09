<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class Laps extends AbstractRules
{
	public $gamemode = GameInfos::GAMEMODE_LAPS;
	public $lapsLimit = 0;
	public $timeLimit = 0;
	public $finishTimeLimit = 1;
	public $disableRespawn = false;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$gameInfos = $dedicated->getCurrentGameInfo();
		$gameInfos->finishTimeout = (int) $this->finishTimeLimit;
		$gameInfos->lapsTimeLimit = (int) $this->timeLimit;
		$gameInfos->lapsNbLaps = (int) $this->lapsLimit;
		$gameInfos->disableRespawn = (bool) $this->disableRespawn;
		$dedicated->setGameInfos($gameInfos);
	}
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		foreach($rankings as $ranking)
			if(isset($match->participants[$ranking['Login']]))
			{
				$match->participants[$ranking['Login']]->rank = $ranking['Rank'];
				$match->participants[$ranking['Login']]->score = $ranking['BestTime'];
			}
		
		return true;
	}
}

?>
