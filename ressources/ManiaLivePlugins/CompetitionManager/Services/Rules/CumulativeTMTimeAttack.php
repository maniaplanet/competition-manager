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

class CumulativeTMTimeAttack extends TMTimeAttack
{
	public $timeLimit = 300000;
	
	private $mapsDone = 0;
	
	function configure(\Maniaplanet\DedicatedServer\Connection $dedicated)
	{
		$dedicated->setTimeAttackLimit((int) $this->timeLimit);
	}
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		
		foreach($rankings as $ranking)
		{
			if(isset($match->participants[$ranking['Login']]))
			{
				$mapScore = new \ManiaLivePlugins\CompetitionManager\Services\Scores\Time();
				$mapScore->time = $ranking['BestTime'];
				$match->participants[$ranking['Login']]->score->time += $ranking['BestTime'];
				$match->participants[$ranking['Login']]->score->details[] = $mapScore;
				++$match->participants[$ranking['Login']]->score->count;
			}
		}
		usort($match->participants, function($a, $b) { return $a->score->compareTo($b->score); });
		
		$rank = $realRank = 0;
		$lastScore = null;
		foreach($match->participants as $player)
		{
			if(!$player->score)
				break;
			
			++$realRank;
			if($player->score->time != $lastScore->time)
				$rank = $realRank;
			$player->rank = $rank;
			$lastScore = $player->score;
		}
		
		if(++$this->mapsDone == count(\ManiaLive\Data\Storage::getInstance()->maps))
			Dispatcher::dispatch(new Event(Event::ON_RULES_END_MATCH));
	}
}

?>
