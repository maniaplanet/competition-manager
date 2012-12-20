<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9142 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-18 15:09:58 +0100 (mar., 18 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

use CompetitionManager\Constants\Qualified;
use CompetitionManager\Constants\State;
use CompetitionManager\Utils\Formatting;

class EliminationTree extends \CompetitionManager\Services\Stage
{
	const WINNERS_BRACKET = 0;
	const LOSERS_BRACKET = 1;
	
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::ELIMINATION_TREE;
		$this->schedule = new \CompetitionManager\Services\Schedules\MultiSimple();
		$this->parameters['slotsPerMatch'] = 8;
		$this->parameters['withLosersBracket'] = false;
		$this->parameters['withSmallFinal'] = false;
	}
	
	function getName()
	{
		return _('Brackets');
	}
	
	function getInfo()
	{
		if($this->parameters['withLosersBracket'])
			$info[] = _('Loser\'s bracket').($this->parameters['withSmallFinal'] ? _(' for the 3rd place') : _(' qualifying for the Grand final'));
		else
			$info[] = _('Single elimination tree').($this->parameters['withSmallFinal'] ? _(' with a 3rd place match') : '');
		$info[] = $this->parameters['slotsPerMatch'] == 2 ? sprintf(_('%d on %1$d matches'), $this->rules->getTeamSize()) : sprintf(_('%d players per match'), $this->parameters['slotsPerMatch']);
		$info[] = _('Rules:');
		$info[] = $this->rules->getInfo();
		return $info;
	}
	
	function getRoundsCount()
	{
		return $this->getWBRoundsCount() + ($this->parameters['withLosersBracket'] ? $this->getLBRoundsCount() : 0);
	}
	
	function getWBRoundsCount()
	{
		if(!$this->maxSlots)
			return 2;
		return (int) ceil(log($this->maxSlots / $this->parameters['slotsPerMatch'], 2)) + 1 + ($this->parameters['withLosersBracket'] && !$this->parameters['withSmallFinal']);
	}
	
	function getLBRoundsCount()
	{
		return ($this->getWBRoundsCount() << 1) - 3 - !$this->parameters['withSmallFinal'];
	}
	
	function getScheduleNames()
	{
		$matchNames = array();
		
		for($round = 0; $round < $this->getWBRoundsCount(); ++$round)
			$matchNames[] = $this->roundName($round, false, true);
		if($this->parameters['withLosersBracket'])
		{
			for($round = 0; $round < $this->getLBRoundsCount(); ++$round)
				$matchNames[] = $this->roundName($round, true, true);
		}
		
		return $matchNames;
	}
	
	function getIcon()
	{
		
	}
	
	function getAction()
	{
		return 'brackets';
	}
	
	function getDefaultDetails()
	{
		return null;
	}
	
	function findMatch($matchId)
	{
		foreach($this->matches as $bracket => $bracketRounds)
			foreach($bracketRounds as $round => $roundMatches)
				foreach($roundMatches as $offset => $matchOrId)
					if(($matchOrId instanceof \CompetitionManager\Services\Match && $matchOrId->matchId == $matchId) || $matchOrId == $matchId)
						return array($bracket, $round, $offset);
	}
	
	function getEmptyLabels($bracket, $round, $offset)
	{
		$previousMatches = array();
		$nbQualified = $this->parameters['slotsPerMatch']>>1;
		
		if($bracket == self::WINNERS_BRACKET)
		{
			if($round == 0)
			{
				$ranks = self::seed(range(1, $this->maxSlots));
				$ranks = array_chunk($ranks, $this->parameters['slotsPerMatch']);
				$ranks = $ranks[$offset];
				sort($ranks);
				
				$emptyLabels = array();
				foreach($ranks as $rank)
					$emptyLabels[] = '#'.$rank.' of previous stage';
				return $emptyLabels;
			}
			
			$previousMatches[] = array($this->matches[self::WINNERS_BRACKET][$round-1][$offset<<1], 0);
			if($this->getWBRoundsCount() - $round - ($this->parameters['withLosersBracket'] && !$this->parameters['withSmallFinal']) == 0)
				$previousMatches[] = array(@reset(end($this->matches[self::LOSERS_BRACKET])), 0);
			else
				$previousMatches[] = array($this->matches[self::WINNERS_BRACKET][$round-1][$offset<<1^1], 0);
		}
		else if($round == 0)
		{
			$previousMatches[] = array($this->matches[self::WINNERS_BRACKET][0][$offset<<1], $nbQualified);
			$previousMatches[] = array($this->matches[self::WINNERS_BRACKET][0][$offset<<1^1], $nbQualified);
		}
		else if($round & 1)
		{
			$swaps = $this->getWBRoundsCount() - 1 - !$this->parameters['withSmallFinal'];
			$swapOffset = self::swap(range(0, count($this->matches[$bracket][$round]) - 1), $swaps ^ ($swaps - ($round>>1) - 1));
			$previousMatches[] = array($this->matches[self::WINNERS_BRACKET][($round>>1)+1][$swapOffset[$offset]], $nbQualified);
			$previousMatches[] = array($this->matches[self::LOSERS_BRACKET][$round-1][$offset], 0);
		}
		else
		{
			$previousMatches[] = array($this->matches[self::LOSERS_BRACKET][$round-1][$offset<<1], 0);
			$previousMatches[] = array($this->matches[self::LOSERS_BRACKET][$round-1][$offset<<1^1], 0);
		}
		
		$emptyLabels = array();
		reset($previousMatches);
		while(list($match, $offset) = current($previousMatches))
		{
			if($match->state == State::UNKNOWN)
			{
				for($i=1; $i<=$nbQualified; ++$i)
					$emptyLabels[] = '#'.($i+$offset).' of '.$match->name;
			}
			next($previousMatches);
		}
		return $emptyLabels;
	}
	
	function onCreate()
	{
		$this->matches = array();
		$service = new \CompetitionManager\Services\MatchService();
		
		if($this->parameters['withLosersBracket'])
		{
			$roundWB = $roundLB = 0;$roundMaxWB = $this->getWBRoundsCount() - 1;
			$matchesToCreate = 1 << ($roundMaxWB - !$this->parameters['withSmallFinal']);
			for($roundWB = $roundLB = 0; $roundWB <= $roundMaxWB; ++$roundWB)
			{
				$this->matches[self::WINNERS_BRACKET][] = $this->createMatches($service, $this->roundName($roundWB), $roundWB, $matchesToCreate);
				if($roundWB == 0)
				{
					$matchesToCreate >>= 1;
					$this->matches[self::LOSERS_BRACKET][] = $this->createMatches($service, $this->roundName($roundLB++, true), $roundLB + $roundMaxWB, $matchesToCreate);
				}
				else if(!$this->parameters['withSmallFinal'] && $roundWB == $roundMaxWB - 1)
					$this->matches[self::LOSERS_BRACKET][] = $this->createMatches($service, $this->roundName($roundLB++, true), $roundLB + $roundMaxWB, $matchesToCreate);
				else if($roundWB != $roundMaxWB)
				{
					$this->matches[self::LOSERS_BRACKET][] = $this->createMatches($service, $this->roundName($roundLB++, true), $roundLB + $roundMaxWB, $matchesToCreate);
					$matchesToCreate >>= 1;
					$this->matches[self::LOSERS_BRACKET][] = $this->createMatches($service, $this->roundName($roundLB++, true), $roundLB + $roundMaxWB, $matchesToCreate);
				}
			}
		}
		else
		{
			$roundMaxWB = $this->getWBRoundsCount() - 1;
			$matchesToCreate = 1 << $roundMaxWB;
			for($roundWB = 0; $roundWB <= $roundMaxWB; ++$roundWB)
			{
				$this->matches[self::WINNERS_BRACKET][] = $this->createMatches($service, $this->roundName($roundWB), $roundWB, $matchesToCreate);
				$matchesToCreate >>= 1;
			}
			if($this->parameters['withSmallFinal'])
				$this->matches[self::WINNERS_BRACKET][$roundMaxWB][] = @reset($this->createMatches($service, $this->roundName($roundWB+1), $roundWB, 1));
		}
	}
	
	private function createMatches($service, $name, $round, $number)
	{
		$matches = array();

		for($i=1; $i<=$number; ++$i)
		{
			$match = new \CompetitionManager\Services\Match();
			$match->name = $name.($number > 1 ? ' #'.$i : '');
			$match->stageId = $this->stageId;
			if(isset($this->schedule->startTimes[$round]))
				$match->startTime = $this->schedule->startTimes[$round];
			$service->create($match);

			$matches[] = $match->matchId;
		}
		
		return $matches;
	}
	
	function onReady($participants)
	{
		$this->participants = $participants;
		$nbParticipants = count($this->participants);
		$qualified = $this->parameters['slotsPerMatch'] >> 1;
		$stageService = new \CompetitionManager\Services\StageService();
		$matchService = new \CompetitionManager\Services\MatchService();
		
		// Remove unnecessary rounds
		while(count($this->matches[self::WINNERS_BRACKET]) > 1 && $nbParticipants <= count($this->matches[self::WINNERS_BRACKET][0]) * $qualified)
		{
			foreach(array_shift($this->matches[self::WINNERS_BRACKET]) as $matchId)
				$matchService->delete($matchId);
			if($this->parameters['withLosersBracket'])
			{
				foreach(array_shift($this->matches[self::LOSERS_BRACKET]) as $matchId)
					$matchService->delete($matchId);
				foreach(array_splice($this->matches[self::LOSERS_BRACKET], 1, 1) as $matchId)
					$matchService->delete($matchId);
			}
		}
		$stageService->update($this);
		
		// Splitting up participants
		$participants = array_values($participants);
		$participantsSeed = self::seed(range(0, count($this->matches[self::WINNERS_BRACKET][0]) * $this->parameters['slotsPerMatch'] - 1));
		$participantsSeed = array_combine($this->matches[self::WINNERS_BRACKET][0], array_chunk($participantsSeed, $this->parameters['slotsPerMatch']));
		$participantsByMatchId = array();
		foreach($participantsSeed as $matchId => $indexes)
		{
			sort($indexes);
			foreach($indexes as $index)
			{
				if($index >= $nbParticipants)
					break;
				$participantsByMatchId[$matchId][] = $participants[$index];
			}
		}
		
		// Assign participants to their first match (may skip some if needed)
		$isRealTree = count($this->matches[self::WINNERS_BRACKET]) > 1;
		foreach($this->matches[self::WINNERS_BRACKET][0] as $offset => $matchId)
		{
			if($isRealTree && count($participantsByMatchId[$matchId]) <= $qualified)
			{
				$this->continueBracket($matchService, $participantsByMatchId[$matchId],
						$this->matches[self::WINNERS_BRACKET][1][$offset>>1],
						$this->matches[self::WINNERS_BRACKET][0][$offset^1]
					);
				$matchService->setState($matchId, State::ARCHIVED);
			}
			else
			{
				$matchService->assignParticipants($matchId, $participantsByMatchId[$matchId], $this->rules->getDefaultDetails());
				$matchService->setState($matchId, State::READY);
				\CompetitionManager\Services\WebServicesProxy::onMatchReady($matchId);
			}
		}
	}
	
	function onMatchOver($match)
	{
		list($bracket, $round, $offset) = $this->findMatch($match->matchId);
		$match->fetchParticipants();
		
		$playerService = new \CompetitionManager\Services\ParticipantService();
		$matchService = new \CompetitionManager\Services\MatchService();
		// Final or Grand final case
		if($bracket == self::WINNERS_BRACKET && $round == count($this->matches[$bracket]) - 1 && $offset == 0)
		{
			foreach($match->participants as $participantId => $participant)
				$playerService->updateStageInfo($this->stageId, $participantId, $participant->rank, count($this->matches[$bracket]) * 2, null);
		}
		// Small final case
		else if($this->parameters['withSmallFinal'] && $round == count($this->matches[$bracket]) - 1)
		{
			foreach($match->participants as $participantId => $participant)
				$playerService->updateStageInfo($this->stageId, $participantId, $participant->rank + $this->parameters['slotsPerMatch'], count($this->matches[$bracket]), null);
		}
		else
		{
			$maxQualified = $this->parameters['slotsPerMatch'] >> 1;
			$qualified = array();
			$falling = array();
			foreach($match->participants as $participantId => $participant)
			{
				if($participant->rank == null)
				{
					$playerService->setMatchQualification($match->matchId, $participantId, Qualified::NO);
					$playerService->updateStageInfo($this->stageId, $participantId, 0, null, null);
				}
				else if(count($qualified) < $maxQualified)
				{
					$qualified[] = $participantId;
					$playerService->setMatchQualification($match->matchId, $participantId, Qualified::YES);
				}
				else
				{
					$isRescued =
							$bracket == self::WINNERS_BRACKET
							&& ($this->parameters['withLosersBracket']
								|| ($this->parameters['withSmallFinal'] && $round == count($this->matches[$bracket]) - 2));
					if($isRescued)
						$falling[] = $participantId;
					else
						$playerService->updateStageInfo($this->stageId, $participantId, 0, $round+1, null);
					$playerService->setMatchQualification($match->matchId, $participantId, Qualified::NO);
				}
			}
			
			if($bracket == self::WINNERS_BRACKET)
			{
				$isWBFinal = $round == count($this->matches[$bracket]) - 2 && $this->parameters['withLosersBracket'] && !$this->parameters['withSmallFinal'];
				$this->continueBracket($matchService, $qualified,
						$this->matches[self::WINNERS_BRACKET][$round+1][$offset>>1],
						$isWBFinal ? $this->matches[self::LOSERS_BRACKET][($round<<1)-1][0] : $this->matches[self::WINNERS_BRACKET][$round][$offset^1],
						$round != count($this->matches[$bracket]) - 2
					);
				if($this->parameters['withLosersBracket'])
				{
					if($round == 0)
						$this->continueBracket($matchService, $falling,
								$this->matches[self::LOSERS_BRACKET][0][$offset>>1],
								$this->matches[self::WINNERS_BRACKET][0][$offset^1]
							);
					else
					{
						$swaps = $this->getWBRoundsCount() - 1 - !$this->parameters['withSmallFinal'];
						$swapOffset = self::swap(range(0, count($this->matches[$bracket][$round]) - 1), $swaps ^ ($swaps - $round));
						$this->continueBracket($matchService, $falling,
								$this->matches[self::LOSERS_BRACKET][($round<<1)-1][$swapOffset[$offset]],
								$this->matches[self::LOSERS_BRACKET][($round-1)<<1][$swapOffset[$offset]]
							);
					}
				}
				else if($this->parameters['withSmallFinal'] && $round == count($this->matches[$bracket]) - 2)
					$this->continueBracket($matchService, $falling,
							$this->matches[self::WINNERS_BRACKET][$round+1][1],
							$this->matches[self::WINNERS_BRACKET][$round][$offset^1],
							false
						);
			}
			else // Losers bracket
			{
				// LB final special case (up back to winners bracket)
				if($round == count($this->matches[$bracket]) - 1)
					$this->continueBracket($matchService, $qualified,
							$this->matches[self::WINNERS_BRACKET][($round>>1)+2][0],
							$this->matches[self::WINNERS_BRACKET][($round>>1)+1][0],
							false
						);
				// Odd rounds are like winners bracket rounds (except first one which has a tiny difference)
				else if($round & 1)
					$this->continueBracket($matchService, $qualified,
							$this->matches[self::LOSERS_BRACKET][$round+1][$offset>>1],
							$this->matches[self::LOSERS_BRACKET][$round][$offset^1]
						);
				// Even rounds are bring-in rounds so offset stay the same but we have to check the winners bracket to set ready
				else
				{
					$swaps = $this->getWBRoundsCount() - 1 - !$this->parameters['withSmallFinal'];
					$swapOffset = self::swap(range(0, count($this->matches[$bracket][$round]) - 1), $swaps ^ ($swaps - ($round>>1) - 1));
					$this->continueBracket($matchService, $qualified,
							$this->matches[self::LOSERS_BRACKET][$round+1][$offset],
							$this->matches[self::WINNERS_BRACKET][($round>>1)+1][$swapOffset[$offset]]
						);
				}
			}
		}
		
		$matchService->setState($match->matchId, State::ARCHIVED);
	}
	
	private function continueBracket($service, $participants, $matchId, $pairedId, $skippable=true)
	{
		$service->assignParticipants($matchId, $participants, $this->rules->getDefaultDetails());
		if($service->getState($pairedId) == State::ARCHIVED)
		{
			$match = $service->get($matchId);
			$match->fetchParticipants();
			if(count($match->participants) == 1 || ($skippable && count($match->participants) <= $this->parameters['slotsPerMatch'] >> 1))
				$this->onMatchOver($match);
			else
			{
				$service->setState($matchId, State::READY);
				\CompetitionManager\Services\WebServicesProxy::onMatchReady($matchId);
			}
		}
	}
	
	function onEnd()
	{
		$this->fetchParticipants();
		uasort($this->participants, function($p1, $p2) { return $p2->score - $p1->score; });
		
		$service = new \CompetitionManager\Services\ParticipantService();
		$rank = $realRank = 1;
		$lastScore = 0;
		foreach($this->participants as $login => $player)
		{
			if($player->rank)
			{
				$lastScore = $player->score;
				$rank++;
				continue;
			}
			if($player->score != $lastScore)
			{
				$realRank = $rank;
				$lastScore = $player->score;
			}
			$service->updateStageInfo($this->stageId, $login, $realRank, $lastScore, null);
			$this->participants[$login]->rank = $realRank;
			$rank++;
		}
	}
	
	private function roundName($round, $losersBracket=false, $plural=false)
	{
		if($losersBracket)
		{
			$nbRounds = $this->getLBRoundsCount();
			$reversedRound = $nbRounds - $round + $this->parameters['withSmallFinal'];
			switch($reversedRound)
			{
				case 1: return _('LB Final');
				case 2: return $this->parameters['withSmallFinal'] ? _('3rd place match') : _('LB Pre Final');
				case 3: return $plural ? _('LB Semi-finals') : _('LB Semi-final');
				case 4: return $plural ? _('LB Pre Semi-finals') : _('LB Pre Semi-final');
				case 5: return $plural ? _('LB Quarter-finals') : _('LB Quarter-final');
				case 6: return $plural ? _('LB Pre Quarter-finals') : _('LB Pre Quarter-final');
				default:
					if($reversedRound & 1)
						return sprintf($plural ? _('LB %s-finals') : _('LB %s-final'), Formatting::ordinal(1 << ($reversedRound >> 1)));
					else
						return sprintf($plural ? _('LB Pre %s-finals') : _('LB Pre %s-final'), Formatting::ordinal(1 << (($reversedRound >> 1) - 1)));
			}
		}
		else
		{
			$nbRounds = $this->getWBRoundsCount();
			$reversedRound = $nbRounds - $round - ($this->parameters['withLosersBracket'] && !$this->parameters['withSmallFinal']);
			switch($reversedRound)
			{
				case 0: return $this->parameters['withSmallFinal'] ? _('3rd place match') : _('Grand final');
				case 1:
					if($this->parameters['withLosersBracket'])
					{
						if($this->parameters['withSmallFinal'])
							return _('Final');
						else
							return _('WB Final');
					}
					else
						return $plural && $this->parameters['withSmallFinal'] ? _('Final and 3rd place match') : _('Final');
				case 2: return $plural ? _('Semifinals') : _('Semi-final');
				case 3: return $plural ? _('Quarter-finals') : _('Quarter-final');
				default: return sprintf($plural ? _('%s-finals') : _('%s-final'), Formatting::ordinal(1 << ($reversedRound-1)));
			}
		}
	}
	
	static function seed($array)
	{
		$count = count($array);
		for($splice = 1; $splice < $count / 2; $splice <<= 1)
		{
			$tempArray = array();
			for($i = 0; $i < $count / $splice; ++$i)
				$tempArray = array_merge($tempArray, array_splice($array, 0, $splice), array_splice($array, -$splice));
			$array = $tempArray;
		}
		return $array;
	}
	
	static function swap($array, $swaps)
	{
		$doSwap = function($array) { return array_merge(array_splice($array, count($array) / 2), $array); };
		
		for($chunks = 1, $size = count($array); $chunks <= $swaps && $chunks < $size; $chunks <<= 1)
		{
			if($swaps & $chunks)
			{
				if($chunks == 1)
					$array = $doSwap($array);
				else
					$array = call_user_func_array('array_merge', array_map($doSwap, array_chunk($array, $size / $chunks)));
			}
		}
		return $array;
	}
}

?>
