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

class TMTimeAttackDuel extends CumulativeTMTimeAttack
{
	public $fixedSlots = 2;
	public $mapsLimit = 2;
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		$logins = array_keys($match->participants);
		if( ($loginIndex = array_search($rankings[0]['Login'], $logins)) !== false )
		{
			if(++$match->participants[$logins[$loginIndex]]->score->points == $this->mapsLimit)
			{
				$match->participants[$logins[$loginIndex]]->rank = 1;
				$match->participants[$logins[1 - $loginIndex]]->rank = 2;
				$match->participants[$logins[1 - $loginIndex]]->score->points |= 0;
				Dispatcher::dispatch(new Event(Event::ON_RULES_END_MATCH));
			}
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
