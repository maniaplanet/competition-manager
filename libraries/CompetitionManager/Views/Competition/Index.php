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
	const CANCELLED = 0;
	const UPCOMING = 1;
	const OPENED_REGISTERED_QUALIFIERS = 2;
	const OPENED_REGISTERED_LOBBY = 3;
	const OPENED_REGISTERED_DEFAULT = 4;
	const OPENED_ALLOWED = 5;
	const OPENED_FULL = 6;
	const OPENED_FORBIDDEN = 7;
	const CLOSED_PLAYER = 8;
	const CLOSED_VISITOR = 9;
	const OVER = 10;
	
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
		
		switch($this->response->displayState)
		{
			case self::CANCELLED:
				$card = $this->progress(_('Cancelled'), _('Not enough players to start...'));
				$card->setTitleBackground('810');
				$frame->add($card);
				break;
			
			case self::UPCOMING:
				if($this->response->time < new \DateTime())
					$card = $this->progress(
							_('Upcoming'),
							_('Registrations will be opened soon!')
						);
				else
					$card = $this->progress(
							_('Upcoming'),
							sprintf(_('Registrations will be opened %s'), Formatting::timeIn($this->response->time->getTimestamp()))
						);
				$card->setTitleBackground('08fa');
				$frame->add($card);
				break;
				
			case self::OPENED_REGISTERED_QUALIFIERS:
				//$this->displayQualifiers();
				break;
			
			case self::OPENED_REGISTERED_LOBBY:
				$card = $this->progress(
						_('Currently: ').$this->response->competition->getCurrentStage()->getName(),
						$this->response->nextUserEvent->message,
						array(
							array(_('Join'), $this->response->nextUserEvent->link)
						)
					);
				$frame->add($card);
				break;
			
			case self::OPENED_REGISTERED_DEFAULT:
				$card = $this->progress(
						_('Currently: ').$this->response->competition->getCurrentStage()->getName(),
						$this->response->nextUserEvent->message
					);
				$frame->add($card);
				break;
			
			case self::OPENED_ALLOWED:
				$buttons = array();
				if($this->response->competition->isTeam)
				{
					foreach($this->response->teams as $uniqId => $team)
					{
						$this->request->set('team', $uniqId);
						$buttons[] = array(sprintf(_('Register %s'), '$<'.$team->name.'$>'), $this->request->createLinkArgList('../register', 'c', 'team', 'external'));
					}
					$this->request->restore('team');
				}
				else
					$buttons[] = array(_('Register'), $this->request->createLinkArgList('../register', 'c', 'external'));
				
				$card = $this->progress(
						_('Currently: ').$this->response->competition->getCurrentStage()->getName(),
						_('Registrations are opened, don\'t wait!'),
						$buttons
					);
				$frame->add($card);
				break;
				
			case self::OPENED_FULL:
				$card = $this->progress(
						_('Currently: ').$this->response->competition->getCurrentStage()->getName(),
						_('All slots have already been taken')
					);
				$frame->add($card);
				break;
			
			case self::OPENED_FORBIDDEN:
				$card = $this->progress(
						_('Currently: ').$this->response->competition->getCurrentStage()->getName(),
						$this->response->competition->isTeam ? _('You cannot register, as you are not an administrator of any team') : _('You cannot register, check the conditions to enter for more details')
					);
				$frame->add($card);
				break;
			
			case self::CLOSED_PLAYER:
				$card = $this->progress(
						_('Currently: ').$this->response->competition->getCurrentStage()->getName(),
						$this->response->nextUserEvent->message,
						array(
							array($this->response->nextUserEvent->match->name, $this->response->nextUserEvent->link)
						)
					);
				$frame->add($card);
				if($this->response->nextForUser)
					break;
				
			case self::CLOSED_VISITOR:
				if($this->response->runningMatches)
				{
					$message = _('There are matches playing right now, go watch some!');
					$buttons = array_map(function($m) { return array($m->name, $m->getManialink()); }, array_slice($this->response->runningMatches, 0, 5));
				}
				else if($this->response->nextMatches)
				{
					$message = _('Check the next upcoming matches!');
					$buttons = array_map(function($m) { return array($m->name, $m->getManialink()); }, array_slice($this->response->nextMatches, 0, 5));
				}
				else
				{
					$message = _('No matches to watch at the moment, come back later!');
					$buttons = array();
				}
					
				
				$card = $this->progress(_('Spectate!'), $message, $buttons);
				$frame->add($card);
				break;
				
			case self::OVER:
				$card = $this->progress(
						_('Over'),
						_('This competition is over, congratulations to all participants'),
						array(
							array(_('See full results'), $this->request->createLinkArgList('../results', 'c', 'external'))
						)
					);
				$frame->add($card);
				break;
		}
		
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
	private function progress($title, $message, $buttons=array())
	{
		$card = new Cards\Window(Constants\UI::STANDARD_WIDTH, Constants\UI::TITLE_HEIGHT + 10);
		$card->setTitleBackground('060a');
		$card->setTitle($title);
		
		$ui = new Label(Constants\UI::STANDARD_WIDTH-10, 0);
		$ui->setRelativeAlign('center');
		$ui->setAlign('center', 'center2');
		$ui->setPosY(-5);
		$ui->setTextSize(2);
		$ui->setTextColor('dddd');
		$ui->setText($message);
		$card->content->add($ui);
		
		if(!$buttons)
			return $card;
		
		$layout = new Layouts\Column(80, -2);
		$layout->setMarginHeight(2);
		$frame = new Frame(80, -2);
		$frame->setLayout($layout);
		$frame->setRelativeAlign('center', 'bottom');
		$frame->setAlign('center', 'bottom');
		$frame->setPosY(2);
		$card->content->add($frame);
		
		foreach($buttons as $button)
		{
			list($text, $link) = $button;
			
			$ui = new Cards\HighlightedLabel(80, 8);
			$ui->highlight->setBgcolor('0606');
			$ui->highlight->setBgcolorFocus('060a');
			$ui->highlight->setManialink($link);
			$ui->label->setRelativeHalign('center');
			$ui->label->setHalign('center');
			$ui->label->setTextSize(2);
			$ui->label->setText($text);
			$frame->add($ui);
			$frame->setSizeY($frame->getSizeY() + 10);
		}
		
		$card->setSizeY($card->getSizeY() + $frame->getSizeY() + 3);

		return $card;
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
