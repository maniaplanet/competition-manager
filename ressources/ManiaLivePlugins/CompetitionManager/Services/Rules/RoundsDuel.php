<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class RoundsDuel extends Rounds
{
	public $maxSlots = 2;
	public $roundsLimit = 5;
	public $mapsLimit = 2;
	public $disableRespawn = false;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$gameInfos = $dedicated->getCurrentGameInfo();
		$gameInfos->roundsUseNewRules = true;
		$gameInfos->roundsPointsLimit = $gameInfos->roundsPointsLimitNewRules = (int) $this->roundsLimit;
		$gameInfos->disableRespawn = (bool) $this->disableRespawn;
		$dedicated->setGameInfos($gameInfos);
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
				return true;
			}
		}
		
		return false;
	}
}

?>
