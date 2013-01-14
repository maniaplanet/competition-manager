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
use CompetitionManager\Cards\MatchFull;
use CompetitionManager\Utils\Formatting;

class MatchDisplay extends \ManiaLib\Application\AdvancedFilter
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
		
		$this->prepare($match);
	}
	
	function prepare($obj)
	{
		$this->card = new MatchFull();
		
		$service = new \CompetitionManager\Services\ParticipantService();
		
		// Participants
		if($obj instanceof \CompetitionManager\Services\Stage)
			$nbSlots = $service->countByStage($obj->stageId);
		else
			$nbSlots = $service->countByMatch($obj->matchId);
		$offset = $length = 0;
		if($nbSlots > 16)
		{
			$this->multipage = new \CompetitionManager\Utils\MultipageList($nbSlots, 16);
			list($offset, $length) = $this->multipage->getLimit();
		}
		if($obj instanceof \CompetitionManager\Services\Stage)
		{
			$this->card->setName($obj->getName());
			$this->participants = $service->getByStage($obj->stageId, $offset, $length);
		}
		else
		{
			$this->card->setName($obj->name);
			$this->participants = $service->getByMatch($obj->matchId, $offset, $length);
		}
		
		$this->autoTime($obj);
		$this->autoButton($obj);
		$this->card->setCloseLink(null);
		$this->card->setState($obj->state);
		
		$this->showScores = $obj->state >= State::STARTED && $this->participants && reset($this->participants)->hasScore();
	}
	
	function autoTime($obj)
	{
		$currentTime = new \DateTime();
		$time = null;
		if($obj->startTime > $currentTime)
			$time = '$iStarts $o'.Formatting::timeIn($obj->startTime->getTimestamp());
		else if($obj->endTime > $currentTime)
			$time = '$iEnds $o'.Formatting::timeIn($obj->endTime->getTimestamp());
		else if($obj->endTime)
			$time = '$iEnded $o'.Formatting::timeAgo($obj->endTime->getTimestamp());
		else if($obj->startTime)
			$time = '$iStarted on $o'.$obj->startTime->format('j F Y $\o\a\t$\o G:i T');
		
		$this->card->setTime($time);
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
		
		$this->response->matchCard = $this->card;
	}
}

?>
