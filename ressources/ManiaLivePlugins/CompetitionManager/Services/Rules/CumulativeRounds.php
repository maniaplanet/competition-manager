<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

use ManiaLive\DedicatedApi\Callback;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\CompetitionManager\Event;

class CumulativeRounds extends Rounds
{
	public $finishTimeLimit = 1;
	public $roundsLimit = 5;
	public $scoringSystem = null;
	public $disableRespawn = false;
	
	private $roundsDone = 0;
	private $mapsDone = 0;
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		$dedicated->setFinishTimeout((int) $this->finishTimeLimit, true);
		if($this->scoringSystem)
			$dedicated->setRoundCustomPoints($this->scoringSystem->points, false, true);
		$dedicated->setDisableRespawn((bool) $this->disableRespawn, true);
		$dedicated->executeMulticall();
	}
	
	function getNeededEvents()
	{
		return Callback\Event::ON_END_ROUND | Callback\Event::ON_END_MATCH;
	}
	
	function onEndRound()
	{
		if(++$this->roundsDone == $this->roundsLimit)
			Dispatcher::dispatch(new Event(Event::ON_RULES_END_MAP));
	}
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		
		foreach($rankings as $ranking)
		{
			if(isset($match->participants[$ranking['Login']]))
			{
				$mapScore = new \ManiaLivePlugins\CompetitionManager\Services\Scores\Points();
				$mapScore->points = $ranking['Score'];
				$match->participants[$ranking['Login']]->score->points += $ranking['Score'];
				$match->participants[$ranking['Login']]->score->details[] = $mapScore;
			}
		}
		usort($match->participants, function($a, $b) { return $a->score->compareTo($b->score); });
		
		$rank = $realRank = 0;
		$lastScore = null;
		foreach($match->participants as $player)
		{
			if(!$player->score->points)
				break;
			
			++$realRank;
			if($player->score->points != $lastScore->points)
				$rank = $realRank;
			$player->rank = $rank;
			$lastScore = $player->score;
		}
		
		if(++$this->mapsDone == count(\ManiaLive\Data\Storage::getInstance()->maps))
			Dispatcher::dispatch(new Event(Event::ON_RULES_END_MATCH));
	}
	
	function _json_wakeup()
	{
		$this->scoringSystem = \CompetitionManager\Services\JSON::unserialize($this->scoringSystem);
	}
}

?>
