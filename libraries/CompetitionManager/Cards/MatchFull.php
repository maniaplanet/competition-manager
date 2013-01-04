<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Cards;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Layouts;
use CompetitionManager\Constants;
use CompetitionManager\Utils\Formatting;

class MatchFull extends Frame
{
	const BG_COLOR = 0;
	const BG_COLOR_FOCUS = 1;
	
	/** @var Bgs1InRace */
	private $shadow;
	/** @var Quad */
	private $background;
	/** @var Frame */
	private $content;
	/** @var Frame */
	private $title;
	
	/** @var HighlightedLabel */
	private $name;
	/** @var HighlightedLabel */
	private $time;
	/** @var HighlightedLabel */
	private $button;
	/** @var HighlightedLabel */
	private $close;
	/** @var \CompetitionManager\Services\Participant */
	private $lastParticipant;
	/** @var \DateTime */
	private $serverStartTime;
	
	function __construct()
	{
		parent::__construct(Constants\UI::STANDARD_WIDTH, Constants\UI::TITLE_HEIGHT-Constants\UI::PIXEL);
		
		// Background
		$this->shadow = new Bgs1InRace();
		$this->shadow->setSubStyle(Bgs1InRace::BgButtonShadow);
		$this->shadow->setRelativeAlign('center', 'center');
		$this->shadow->setAlign('center', 'center');
		$this->background = new Quad();
		$this->background->setPosZ(.1);
		$this->background->setBgcolor('111');
		
		// Content
		$layout = new Layouts\Column();
		$layout->setMarginHeight(Constants\UI::PIXEL);
		$this->content = new Frame();
		$this->content->setLayout($layout);
		$this->content->setPosZ(.2);
		
		// Title
		$layout = new Layouts\VerticalFlow($this->sizeX, Constants\UI::TITLE_HEIGHT);
		$layout->setMarginWidth(Constants\UI::PIXEL);
		$this->title = new Frame($this->sizeX, Constants\UI::TITLE_HEIGHT);
		$this->title->setLayout($layout);
		$this->name = new HighlightedLabel();
		$this->name->setLabelMargin(2);
		$this->name->label->setPosX(2);
		$this->time = new HighlightedLabel(0, Constants\UI::TITLE_HEIGHT / 3);
		$this->time->setLabelMargin(2);
		$this->time->label->setPosX(2);
		$this->button = new HighlightedLabel(3*Constants\UI::TITLE_HEIGHT, Constants\UI::TITLE_HEIGHT);
		$this->button->label->setRelativeHalign('center');
		$this->button->label->setHalign('center');
		$this->close = new HighlightedLabel(Constants\UI::TITLE_HEIGHT, Constants\UI::TITLE_HEIGHT);
		$this->close->label->setRelativeHalign('center');
		$this->close->label->setHalign('center');
		$this->close->label->setText('$oX');
		
		$this->add($this->shadow);
		$this->add($this->background);
		$this->add($this->content);
		$this->content->add($this->title);
		$this->title->add($this->name);
		$this->title->add($this->time);
		$this->title->add($this->button);
		$this->title->add($this->close);
	}
	
	function setName($name)
	{
		$this->name->label->setText($name);
	}
	
	function setTime($time)
	{
		$this->time->label->setText($time);
		$this->time->setVisibility((bool) $time);
		$this->name->label->setTextSize($time ? 2 : 1);
	}
	
	function setButton($label, $manialink, $serverStartTime=null)
	{
		$this->button->label->setText('$t'.$label);
		$this->button->highlight->setManialink($manialink);
		$this->button->setVisibility((bool) $manialink);
		$this->serverStartTime = $serverStartTime;
	}
	
	function setCloseLink($manialink)
	{
		$this->close->highlight->setManialink($manialink);
		$this->close->setVisibility((bool) $manialink);
	}
	
	function setState($state)
	{
		static $customizations = array(
			Constants\State::UNKNOWN => array(
				self::BG_COLOR => '0445',
				self::BG_COLOR_FOCUS => '0449'
			),
			Constants\State::READY => array(
				self::BG_COLOR => '0885',
				self::BG_COLOR_FOCUS => '0889'
			),
			Constants\State::STARTED => array(
				self::BG_COLOR => '08f5',
				self::BG_COLOR_FOCUS => '08f9'
			),
			Constants\State::OVER => array(
				self::BG_COLOR => '00f5',
				self::BG_COLOR_FOCUS => '00f9'
			),
			Constants\State::ARCHIVED => array(
				self::BG_COLOR => '0085',
				self::BG_COLOR_FOCUS => '0089'
			),
			Constants\State::CANCELLED => array(
				self::BG_COLOR => '0005',
				self::BG_COLOR_FOCUS => '0009'
			),
		);
		
		$this->name->highlight->setBgcolor($customizations[$state][self::BG_COLOR]);
		$this->time->highlight->setBgcolor($customizations[$state][self::BG_COLOR]);
		$this->button->highlight->setBgcolor($customizations[$state][self::BG_COLOR]);
		$this->button->highlight->setBgcolorFocus($customizations[$state][self::BG_COLOR_FOCUS]);
		$this->close->highlight->setBgcolor($customizations[$state][self::BG_COLOR]);
		$this->close->highlight->setBgcolorFocus($customizations[$state][self::BG_COLOR_FOCUS]);
	}
	
	/**
	 * @param \CompetitionManager\Services\Participant $participant
	 * @param bool $isUser
	 */
	function addParticipant($participant, $showRank=false, $showScore=false, $isUser=false)
	{
		$ui = new Participant($this->sizeX, 7);
		$ui->setName($participant->name);
		if($this->lastParticipant && $this->lastParticipant->rank === $participant->rank)
			$ui->setRank('=');
		else
			$ui->setRank($participant->rank);
		$ui->setScore($participant->formatScore(null), $participant->scoreDetails && $participant->scoreDetails->isTime);
		$ui->setVisibilities($showRank, $showScore);
		$ui->setCustomization($isUser, $participant->qualified);
		
		$this->content->add($ui);
		$this->setSizeY($this->sizeY + 7 + Constants\UI::PIXEL);
		$this->lastParticipant = $participant;
	}
	
	function addEmpty($label)
	{
		$ui = new EmptySlot($this->sizeX, 7);
		$ui->setLabel($label);
		$this->content->add($ui);
		$this->setSizeY($this->sizeY + 7 + Constants\UI::PIXEL);
	}
	
	function addPageNavigator($navigator)
	{
		$navigator->setRelativeHalign('center');
		$navigator->setHalign('center');
		$this->content->add($navigator);
		$this->setSizeY($this->sizeY + $navigator->getRealSizeY() + Constants\UI::PIXEL);
	}
	
	function onResize($oldX, $oldY)
	{
		parent::onResize($oldX, $oldY);
		$this->shadow->setSize($this->sizeX+11, $this->sizeY+11);
		$this->background->setSize($this->sizeX, $this->sizeY);
		$this->content->setSize($this->sizeX, $this->sizeY-Constants\UI::TITLE_HEIGHT);
	}
	
	function preFilter()
	{
		$nameWidth = $this->getNameWidth();
		$this->name->setSize($nameWidth, $this->getNameHeight());
		$this->time->setSize($nameWidth);
		if($this->button->highlight->getManialink() === true)
		{
			$this->button->highlight->setManialink(null);
			$this->button->highlight->setId('match-full:button');
			$this->button->highlight->setScriptEvents();
			if($this->serverStartTime)
				\ManiaLib\ManiaScript\UI::tooltip('match-full:button', sprintf(
						_('Server will be available %s'),
						Formatting::timeIn($this->serverStartTime->getTimestamp()+120)
					));
			else
				\ManiaLib\ManiaScript\UI::tooltip('match-full:button', _('Server will be available soon'));
		}
	}
	
	private function getNameWidth()
	{
		$availableWidth = $this->sizeX;
		
		if($this->button->isVisible())
			$availableWidth -= $this->button->getSizeX() + Constants\UI::PIXEL;
		if($this->close->isVisible())
			$availableWidth -= $this->close->getSizeX() + Constants\UI::PIXEL;
		
		return $availableWidth;
	}
	
	private function getNameHeight()
	{
		return Constants\UI::TITLE_HEIGHT - ($this->time->isVisible() ? $this->time->getSizeY() : 0);
	}
}

?>
