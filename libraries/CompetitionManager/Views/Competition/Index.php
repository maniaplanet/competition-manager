<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9115 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-14 18:12:50 +0100 (ven., 14 déc. 2012) $:
 */

namespace CompetitionManager\Views\Competition;

use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Button;
use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Layouts;
use CompetitionManager\CardsOld;
use CompetitionManager\Cards;
use CompetitionManager\Constants;
use CompetitionManager\Utils\Formatting;

class Index extends \ManiaLib\Application\View
{
	const CANCELLED                    = 0x00;
	const UPCOMING                     = 0x01;
	const OPENED_REGISTERED_QUALIFIERS = 0x32;
	const OPENED_REGISTERED_LOBBY      = 0x03;
	const OPENED_REGISTERED_DEFAULT    = 0x34;
	const OPENED_ALLOWED               = 0x35;
	const OPENED_FULL                  = 0x26;
	const OPENED_FORBIDDEN             = 0x27;
	const CLOSED_PLAYER                = 0x08;
	const CLOSED_VISITOR               = 0x09;
	const OVER                         = 0x0a;
	
	const SHOW_REGISTER   = 0x10;
	const SHOW_UNREGISTER = 0x20;
	
	/** @var Cards\Window */
	private $progressCard;
	
	function display()
	{
		$this->renderSubView('_Menu');
		
		$layout = new Layouts\Column(Constants\UI::STANDARD_WIDTH, Constants\UI::ACCORDION_HEIGHT);
		$layout->setMarginHeight(6);
		$frame = new Frame(Constants\UI::STANDARD_WIDTH, Constants\UI::ACCORDION_HEIGHT);
		$frame->setLayout($layout);
		$frame->setAlign('center', 'center');
		$frame->setPosX(40);
		
		$frame->add($this->welcome());
		$frame->add($this->progressCard = $this->progress());
		
		switch($this->response->displayState)
		{
			case self::CANCELLED:
				$this->progressSetTitle(_('Cancelled'));
				$this->progressAddLabel(_('Not enough players to start...'));
				$this->progressCard->setTitleBackground('810');
				break;
			
			case self::UPCOMING:
				$this->progressSetTitle(_('Upcoming'));
				if($this->response->time < new \DateTime())
					$this->progressAddLabel(_('Registrations will be opened soon!'));
				else
					$this->progressAddLabel(sprintf(_('Registrations will be opened %s'), Formatting::timeIn($this->response->time->getTimestamp())));
				$this->progressCard->setTitleBackground('08fa');
				break;
				
			case self::OPENED_REGISTERED_QUALIFIERS:
				//$this->displayQualifiers();
				break;
			
			case self::OPENED_REGISTERED_LOBBY:
				$this->progressSetTitle(_('Currently: ').$this->response->competition->getCurrentStage()->getName());
				$this->progressAddLabel($this->response->nextUserEvent->message);
				$this->progressAddButton(_('Join'), $this->response->nextUserEvent->link);
				break;
			
			case self::OPENED_REGISTERED_DEFAULT:
				$this->progressSetTitle(_('Currently: ').$this->response->competition->getCurrentStage()->getName());
				$this->progressAddLabel($this->response->nextUserEvent->message);
				break;
			
			case self::OPENED_ALLOWED:
				$this->progressSetTitle(_('Currently: ').$this->response->competition->getCurrentStage()->getName());
				$this->progressAddLabel(_('Registrations are opened, don\'t wait!'));
				break;
				
			case self::OPENED_FULL:
				$this->progressSetTitle(_('Currently: ').$this->response->competition->getCurrentStage()->getName());
				$this->progressAddLabel(_('All slots have already been taken'));
				break;
			
			case self::OPENED_FORBIDDEN:
				$this->progressSetTitle(_('Currently: ').$this->response->competition->getCurrentStage()->getName());
				if($this->response->competition->isTeam)
					$this->progressAddLabel(_('You cannot register, as you are not an administrator of any team'));
				else
					$this->progressAddLabel(_('You cannot register, check the conditions to enter for more details'));
				break;
			
			case self::CLOSED_PLAYER:
				$this->progressSetTitle(_('Currently: ').$this->response->competition->getCurrentStage()->getName());
				$this->progressAddLabel($this->response->nextUserEvent->message);
				if($this->response->nextUserEvent->match)
				{
					$this->progressAddButton($this->response->nextUserEvent->match->name, $this->response->nextUserEvent->link);
					break;
				}
				
			case self::CLOSED_VISITOR:
				$this->progressSetTitle(_('Spectate!'));
				if($this->response->runningMatches)
				{
					$this->progressAddLabel(_('There are matches playing right now, go watch some!'));
					foreach(array_slice($this->response->runningMatches, 0, 5) as $match)
						$this->progressAddButton($match->name, $match->getManialink());
				}
				else if($this->response->nextMatches)
				{
					$this->progressAddLabel(_('Check the next upcoming matches!'));
					foreach(array_slice($this->response->nextMatches, 0, 5) as $match)
						$this->progressAddButton($match->name, $match->getManialink());
				}
				else
					$this->progressAddLabel(_('No matches to watch at the moment, come back later!'));
				break;
				
			case self::OVER:
				$this->progressSetTitle(_('Over'));
				$this->progressAddLabel(_('This competition is over, congratulations to all participants'));
				$this->progressAddButton(_('See full results'), $this->request->createLinkArgList('../results', 'c', 'external'));
				break;
		}
		
		// (Un)Register buttons need to be handled a bit differently
		if($this->response->displayState & self::SHOW_REGISTER && $this->response->canRegister)
		{
			if(!$this->response->competition->isTeam)
				$this->progressAddButton(_('Register'), $this->request->createLinkArgList('../register', 'c', 'external'));
			else if($this->response->registrableTeams)
			{
				$this->progressAddLabel(_('These are the teams you can register:'));
				foreach($this->response->registrableTeams as $uniqId => $team)
				{
					$this->request->set('team', $uniqId);
					$this->progressAddButton(
							sprintf(_('Register %s'), '$<'.$team->name.'$>'),
							$this->request->createLinkArgList('../register', 'c', 'team', 'external')
						);
				}
			}
			
		}
		if($this->response->displayState & self::SHOW_UNREGISTER && $this->response->canUnregister)
		{
			if(!$this->response->competition->isTeam)
				$this->progressAddButton(_('Unregister'), $this->request->createLinkArgList('../unregister', 'c', 'external'));
			else if($this->response->unregistrableTeams)
			{
				$this->progressAddLabel(_('You still have time to unregister the following teams:'));
				foreach($this->response->unregistrableTeams as $uniqId => $team)
				{
					$this->request->set('team', $uniqId);
					$this->progressAddButton(
							sprintf(_('Unregister %s'), '$<'.$team->name.'$>'),
							$this->request->createLinkArgList('../unregister', 'c', 'team', 'external')
						);
				}
			}
		}
		$this->request->restore('team');
		
		//$frame->add($this->sponsors());
		$frame->save();
	}
	
	/**
	 * @return Cards\Window
	 */
	private function welcome()
	{
		$card = new Cards\Window();
		$card->setTitle(sprintf(_('Welcome in %s'), '$<'.$this->response->competition->name.'$>'));
		$card->setTitleBackground('008a');
		return $card;
	}
	
	private function sponsors()
	{
		
	}
	
	/**
	 * @return Cards\Window
	 */
	private function progress()
	{
		$card = new Cards\Window(Constants\UI::STANDARD_WIDTH, Constants\UI::TITLE_HEIGHT+2);
		$card->setTitleBackground('060a');
		$layout = new Layouts\Column();
		$layout->setMarginHeight(2);
		$layout->setBorderHeight(2);
		$card->content->setLayout($layout);

		return $card;
	}
	
	private function progressSetTitle($title)
	{
		$this->progressCard->setTitle($title);
	}
	
	private function progressAddLabel($message)
	{
		$ui = new Label(Constants\UI::STANDARD_WIDTH-10, 6);
		$ui->setRelativeAlign('center');
		$ui->setAlign('center');
		$ui->setTextSize(2);
		$ui->setTextColor('dddd');
		$ui->setText($message);
		$this->progressCard->content->add($ui);
		$this->progressCard->setSizeY($this->progressCard->getSizeY() + 8);
	}
	
	private function progressAddButton($text, $link)
	{
		$ui = new Cards\HighlightedLabel(80, 8);
		$ui->setRelativeHalign('center');
		$ui->setHalign('center');
		$ui->highlight->setBgcolor('0606');
		$ui->highlight->setBgcolorFocus('060a');
		$ui->highlight->setManialink($link);
		$ui->label->setRelativeHalign('center');
		$ui->label->setHalign('center');
		$ui->label->setTextSize(2);
		$ui->label->setText($text);
		$this->progressCard->content->add($ui);
		$this->progressCard->setSizeY($this->progressCard->getSizeY() + 10);
	}

	// FIXME
	private function displayQualifiers()
	{
		$card = new CardsOld\BasicWindow();
		$card->titleBackground->setSubStyle(Bgs1::BgTitle3_4);
		$card->title->setText(_('Progress'));
		$card->text->setPosition(0, -20, .1);
		$card->text->setRelativeHalign('center');
		$card->text->setAlign('center');
		$card->text->enableAutonewline();
		$card->text->setText(sprintf(_('You are registered and currently ranked %s in the qualifiers.'),
				\CompetitionManager\Utils\Formatting::ordinal($this->response->ranking->rank)));
		$ui = new Button();
		$ui->setPosition(0, -33, .1);
		$ui->setRelativeHalign('center');
		$ui->setHalign('center');
		$ui->setScale(1.2);
		$ui->setStyle(Button::CardButtonMediumWide);
		$ui->setText(_('Try to improve your standing'));
		$ui->setManialink($this->response->link);
		$card->add($ui);
		$card->setSizeY(47);
		$card->save();
	}
}

?>
