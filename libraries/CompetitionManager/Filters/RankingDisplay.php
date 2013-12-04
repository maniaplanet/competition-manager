<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Filters;

use CompetitionManager\Constants\State;
use CompetitionManager\Cards\GroupFull;
use CompetitionManager\Cards\MatchFull;
use CompetitionManager\Cards\RankingFull;
use CompetitionManager\Utils\Formatting;

class RankingDisplay extends \ManiaLib\Application\AdvancedFilter
{
	/** @var MatchFull */
	public $card = null;
	
	public $userId = false;
	public $showRanks = true;
	public $showScores = false;
	
	public $participants = array();
	public $linesToShow = 0;
	public $emptyLabels = null;
	public $multipage = null;
	
	function preFilter()
	{
		if(!$this->request->get('m'))
			return;
		
		$service = new \CompetitionManager\Services\MatchService();
		$match = $service->get($this->request->get('m'));
		if($match->stageId != $this->request->get('s'))
		{
			$this->request->delete('m');
			return;
		}
		
		$this->prepareMatch($match);
	}
	
	/**
	 * @param \CompetitionManager\Services\Match $match
	 */
	function prepareMatch($match)
	{
		$this->card = new MatchFull();
		$this->card->setName($match->name);
		$this->card->setState($match->state);
		$this->autoTime($match);
		$this->autoButton($match);
		$this->showScores = $match->state >= State::STARTED && $this->participants && reset($this->participants)->score->isVisible();
		
		// Participants
		$service = new \CompetitionManager\Services\ParticipantService();
		$nbSlots = $service->countByMatch($match->matchId);
		$offset = $length = 0;
		if($nbSlots > 16)
		{
			$this->multipage = new \CompetitionManager\Utils\MultipageList($nbSlots, 16);
			list($offset, $length) = $this->multipage->getLimit();
		}
		$this->participants = $service->getByMatch($match->matchId, $offset, $length);
	}
	
	/**
	 * @param \CompetitionManager\Services\Stage $stage
	 */
	function prepareBasic($stage)
	{
		if($stage instanceof \CompetitionManager\Services\Stages\Championship)
			$this->card = new GroupFull();
		else
			$this->card = new RankingFull();
		$this->card->setName($stage->getName());
		$this->card->setState($stage->state);
		$this->autoTime($stage);
		$this->showScores = $stage->state >= State::STARTED && $this->participants && reset($this->participants)->score->isVisible();
		
		// Participants
		$service = new \CompetitionManager\Services\ParticipantService();
		$nbSlots = $service->countByStage($stage->stageId);
		$offset = $length = 0;
		if($nbSlots > 16)
		{
			$this->multipage = new \CompetitionManager\Utils\MultipageList($nbSlots, 16);
			list($offset, $length) = $this->multipage->getLimit();
		}
		$this->participants = $service->getByStage($stage->stageId, $offset, $length);
	}
	
	/**
	 * @param \CompetitionManager\Services\Stage $stage
	 */
	function prepareChampionship($stage, $group)
	{
		$this->card = new GroupFull();
		$this->card->setName(sprintf(_('Group %s'), \CompetitionManager\Services\Stages\Groups::getGroupLetter($group)));
		$this->card->setState($stage->state);
		$this->autoTime($stage);
		$this->showScores = $stage->state >= State::STARTED && $this->participants && reset($this->participants)->score->isVisible();
		
		// Participants
		$service = new \CompetitionManager\Services\ParticipantService();
		$offset = $length = 0;
		$nbSlots = count($stage->parameters['groupParticipants'][$group]);
		if($nbSlots > 16)
		{
			$this->multipage = new \CompetitionManager\Utils\MultipageList($nbSlots, 16);
			list($offset, $length) = $this->multipage->getLimit();
		}
		$this->participants = $service->getByStage($stage->stageId);
		$this->participants = array_intersect_key($this->participants, array_flip($stage->parameters['groupParticipants'][$group]));
		$this->participants = array_slice($this->participants, $offset, $length, true);
	}
	
	function autoTime($obj)
	{
		$currentTime = new \DateTime();
		$time = null;
		if($obj->startTime > $currentTime)
		{
			$time = '$iStarts $o'.Formatting::timeIn($obj->startTime->getTimestamp());
			$time2 = $obj->startTime->format('j F Y $\o\a\t$\o G:i T');
		}
		else if($obj->endTime > $currentTime)
		{
			$time = '$iEnds $o'.Formatting::timeIn($obj->endTime->getTimestamp());
			$time2 = $obj->endTime->format('j F Y $\o\a\t$\o G:i T');
		}
		else if($obj->endTime)
		{
			$time = '$iEnded $o'.Formatting::timeAgo($obj->endTime->getTimestamp());
			$time2 = $obj->endTime->format('j F Y $\o\a\t$\o G:i T');
		}
		else if($obj->startTime)
		{
			$time = '$iStarted on $o'.$obj->startTime->format('j F Y $\o\a\t$\o G:i T');
			$time2 = $obj->startTime->format('j F Y $\o\a\t$\o G:i T');
		}
		
		$this->card->setTime($time."  -  ".$time2);
	}
	
	function autoButton($obj)
	{
		if(!($obj instanceof \CompetitionManager\Services\Match))
		{
			$this->card->setButton('', null);
			return;
		}
		
		$obj->fetchServer();
		
		$service = new \CompetitionManager\Services\ParticipantService();
		$link = $obj->state == State::READY || $obj->state == State::STARTED;
		if($service->isRegisteredInMatch($this->userId, $obj->matchId))
		{
			$label = _('Play!');
			if($link && $obj->server && $obj->server->isReady())
				$link = $obj->server->getLink('qjoin');
		}
		else
		{
			$label = _('Spectate');
			if($link && $obj->server && $obj->server->isReady())
				$link = $obj->server->getLink('qspectate');
		}
		
		$this->card->setButton($label, $link, $obj->server ? $obj->server->startTime : null);
	}
	
	function isPrepared()
	{
		return (bool) $this->card;
	}
	
	function postFilter()
	{
		foreach($this->participants as $participant)
			$this->card->addParticipant($participant, $this->showRanks, $this->showScores, $this->userId == $participant->participantId);
		
		for($i=count($this->participants); $i<$this->linesToShow; ++$i)
		{
			if($this->emptyLabels)
			{
				if(is_array($this->emptyLabels))
					$this->card->addEmpty(array_shift($this->emptyLabels));
				else
					$this->card->addEmpty($this->emptyLabels);
			}
			else
				$this->card->addEmpty('');
		}
		
		if($this->multipage)
			$this->card->addPageNavigator($this->multipage->createNavigator());
		
		$this->response->rankingCard = $this->card;
	}
}

?>
