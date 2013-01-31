<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9040 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-04 13:05:22 +0100 (mar., 04 déc. 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

use ManiaLive\DedicatedApi\Callback\Event;

abstract class Rules
{
	public $gameMode;
	public $fixedSlots = null;
	
	function compare($scoreA, $scoreB)
	{
		return $scoreB - $scoreA;
	}
	
	function getTeamSize()
	{
		return 0;
	}
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		// Often, it should be already configured in match settings
	}
	
	function getNeededEvents()
	{
		return Event::ON_END_MATCH;
	}
	
	function onPlayerFinish($login, $timeOrScore) {}
	function onEndRound() {}
	function onEndMatch($rankings, $winnerTeamOrMap) {}
	
	function onForfeit($winner, $forfeit)
	{
		$winner->rank = 1;
		$forfeit->rank = null;
	}
}

?>
