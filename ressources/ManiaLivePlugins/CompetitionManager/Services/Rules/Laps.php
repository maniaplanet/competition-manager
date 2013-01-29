<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\CompetitionManager\Event;

class Laps extends \ManiaLivePlugins\CompetitionManager\Services\Rules
{
	public $gamemode = GameInfos::GAMEMODE_LAPS;
	public $lapsLimit = 0;
	public $timeLimit = 0;
	public $finishTimeLimit = 1;
	public $disableRespawn = false;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$dedicated->setNbLaps((int) $this->lapsLimit, true);
		$dedicated->setLapsTimeLimit((int) $this->timeLimit, true);
		$dedicated->setFinishTimeout((int) $this->finishTimeLimit, true);
		$dedicated->setDisableRespawn((bool) $this->disableRespawn, true);
		$dedicated->executeMulticall();
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
		
		Dispatcher::dispatch(new Event(Event::ON_RULES_END_MATCH));
	}
}

?>
