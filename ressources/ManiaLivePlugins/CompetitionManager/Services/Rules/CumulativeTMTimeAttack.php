<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class CumulativeTMTimeAttack extends TMTimeAttack
{
	public $timeLimit = 300000;
	
	private $mapsDone = 0;
	
	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		$match = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		
		foreach($rankings as $ranking)
		{
			if(isset($match->participants[$ranking['Login']]))
				$match->participants[$ranking['Login']]->score += $ranking['BestTime'];
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
}

?>
