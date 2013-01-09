<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

use ManiaLive\DedicatedApi\Callback\Event;

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
		return Event::ON_END_ROUND | Event::ON_END_MATCH;
	}
	
	function onEndRound()
	{
		return ++$this->roundsDone == $this->roundsLimit;
	}
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		
		foreach($rankings as $ranking)
		{
			if(isset($match->participants[$ranking['Login']]))
				$match->participants[$ranking['Login']]->score += $ranking['Score'];
		}
		
		$self = $this;
		usort($match->participants, function($a, $b) use ($self) { return $self->compare($a->score, $b->score); });
		$rank = $realRank = 0;
		$lastScore = null;
		foreach($match->participants as $player)
		{
			if(!$player->score)
				break;
			
			++$realRank;
			if($player->score != $lastScore)
				$rank = $realRank;
			$player->rank = $rank;
			$lastScore = $player->score;
		}
		
		return ++$this->mapsDone == count(\ManiaLive\Data\Storage::getInstance()->maps);
	}
	
	function _json_wakeup()
	{
		$this->scoringSystem = \CompetitionManager\Services\JSON::unserialize($this->scoringSystem);
	}
}

?>
