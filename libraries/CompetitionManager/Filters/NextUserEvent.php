<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Filters;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Icons128x128_Blink;
use ManiaLib\Gui\Elements\Frame;
use CompetitionManager\Cards;
use CompetitionManager\Constants;
use CompetitionManager\Services\Stages;
use CompetitionManager\Utils\Formatting;

class NextUserEvent extends \ManiaLib\Application\AdvancedFilter
{
	/** @var \CompetitionManager\Services\Competition */
	public $competition;
	/** @var int */
	public $userId;
	/** @var bool */
	public $showNotification = true;
	
	function postFilter()
	{
		if($this->userId && $this->competition)
		{
			$stage = $this->competition->getCurrentStage();
			if(!$stage)
				return;
			
			if($this->showNotification)
				$this->response->registerView(__NAMESPACE__.'\_NextUserEventView');
			
			$this->response->nextUserEvent = (object) array('message' => '', 'link' => null, 'blink' => false, 'match' => null);
			if($stage instanceof Stages\Registrations)
			{
				if($stage->endTime > new \DateTime())
				{
					$this->response->nextUserEvent->message = sprintf(
							_('Registrations end %s, refresh later for your first match!'),
							'$<$o'.Formatting::timeIn($stage->endTime->getTimestamp()).'$>'
						);
				}
				else
				{
					$this->response->nextUserEvent->message = sprintf(
							_('Registrations ended, next stage is starting!'),
							'$<$o'.Formatting::timeIn($stage->endTime->getTimestamp()).'$>'
						);
				}
			}
			else if($stage instanceof Stages\Lobby)
			{
				$stage->fetchMatches();
				/* @var $lobby \CompetitionManager\Services\Match */
				$lobby = reset($stage->matches);
				$lobby->fetchServer();
				if($lobby->server && $lobby->server->isReady())
				{
					$this->response->nextUserEvent->message = _('If you are not already on the lobby, join it to keep your slot!');
					$this->response->nextUserEvent->link = $lobby->server->getLink('qjoin');
					$this->response->nextUserEvent->blink = true;
				}
				else
					$this->response->nextUserEvent->message = _('Lobby is down... Stay tuned and join it when it comes back.');
			}
			else
			{
				$service = new \CompetitionManager\Services\ParticipantService();
				if(!$service->isRegisteredInStage($this->userId, $stage->stageId))
					return;
				
				if($stage instanceof Stages\OpenStage)
				{
					// TODO
				}
				else
				{
					$service = new \CompetitionManager\Services\MatchService();
					$match = $service->getNextForParticipant($this->userId, $this->competition->competitionId);
					if($match)
					{
						$match->fetchServer();
						if($match->server)
						{
							if($match->server->isReady())
							{
								$this->response->nextUserEvent->message = _('Server for your next match is open, join it now!');
								$this->response->nextUserEvent->link = $match->server->getLink('qjoin');
								$this->response->nextUserEvent->blink = true;
							}
							else
							{
								$this->response->nextUserEvent->message = sprintf(
										_('Server for your next match will be available %s'),
										Formatting::timeIn($match->server->startTime->getTimestamp()+120)
									);
								$this->response->nextUserEvent->link = $match->getManialink();
								$this->response->nextUserEvent->blink = true;
							}
						}
						else
						{
							if($match->startTime > new \DateTime())
								$this->response->nextUserEvent->message = sprintf(_('Your next match is starting %s.'), Formatting::timeIn($match->startTime->getTimestamp()));
							else
								$this->response->nextUserEvent->message = _('Server for your next match will be opened soon.');
							$this->response->nextUserEvent->link = $match->getManialink();
						}
						$this->response->nextUserEvent->match = $match;
					}
					else if($stage->nextId)
						$this->response->nextUserEvent->message = _('You don\'t have any match planned at the moment, wait for the next stage.');
					else
						$this->response->nextUserEvent->message = _('You don\'t have any match left, wait for results.');
				}
			}
		}
	}
}

class _NextUserEventView extends \ManiaLib\Application\View
{
	function display()
	{
		$frame = new Frame(Constants\UI::EVENT_WIDTH, Constants\UI::EVENT_HEIGHT);
		$frame->setHalign('right');
		$frame->setPosition(25, 80);
		
		if(true /* $this->response->nextUserEvent->blink */)
		{
			$ui = new Icons128x128_Blink();
			$ui->setSubStyle(Icons128x128_Blink::ShareBlink);
			$ui->setSize(2*Constants\UI::EVENT_WIDTH, 2*Constants\UI::EVENT_HEIGHT);
			$ui->setScale(.5);
			$ui->setRelativeAlign('center', 'center');
			$ui->setAlign('center', 'center');
			$ui->setPosZ(-.2);
			$frame->add($ui);
		}
		
		$ui = new Bgs1InRace(Constants\UI::EVENT_WIDTH+11, Constants\UI::EVENT_HEIGHT+11);
		$ui->setSubStyle(Bgs1InRace::BgButtonShadow);
		$ui->setRelativeAlign('center', 'center');
		$ui->setAlign('center', 'center');
		$ui->setPosZ(-.1);
		$frame->add($ui);
		
		$ui = new Cards\HighlightedLabel(Constants\UI::EVENT_WIDTH, Constants\UI::EVENT_HEIGHT);
		$ui->setRelativeAlign('center', 'center');
		$ui->setAlign('center', 'center');
		$ui->highlight->setBgcolor('0008');
		$ui->highlight->setBgcolorFocus('1148');
		if($this->response->nextUserEvent->link)
		{
			$ui->highlight->setManialink($this->response->nextUserEvent->link);
		}
		$ui->label->setRelativeHalign('center');
		$ui->label->setHalign('center');
		$ui->label->setTextColor('8f0');
		$ui->label->setText($this->response->nextUserEvent->message);
		$ui->setLabelMargin(1);
		$frame->add($ui);
		
		$frame->save();
	}
}

?>
