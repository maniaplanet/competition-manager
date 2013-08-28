<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Cards;

use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Layouts;
use CompetitionManager\Constants;

class RankingFull extends Shadowed
{
	/** @var Frame */
	private $content;
	/** @var Frame */
	protected $title;
	
	/** @var HighlightedLabel */
	private $name;
	/** @var HighlightedLabel */
	private $time;
	/** @var \CompetitionManager\Services\Participant */
	private $lastParticipant;
	
	function __construct($sizeX=Constants\UI::MEDIUM_WIDTH, $sizeY=0)
	{
		parent::__construct($sizeX, $sizeY);
		
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
		
		$this->add($this->content);
		$this->content->add($this->title);
		$this->title->add($this->name);
		$this->title->add($this->time);
		
		$this->setSizeY($this->sizeY + Constants\UI::TITLE_HEIGHT - $sizeY);
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
	
	function setState($state)
	{
		$this->name->highlight->setBgcolor(Constants\UI::STATE_COLOR($state));
		$this->time->highlight->setBgcolor(Constants\UI::STATE_COLOR($state));
	}
	
	/**
	 * @param \CompetitionManager\Services\Participant $participant
	 * @param bool $showRank
	 * @param bool $showScore
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
		$ui->setScore($participant->score);
		if($participant instanceof \CompetitionManager\Services\Team)
			$ui->setTeamLink($participant->teamId);
		$ui->setVisibilities($showRank, $showScore);
		$ui->setCustomization($isUser, $participant->qualified);
		
		$this->content->add($ui);
		\ManiaLib\Utils\Logger::info($this->getSizeY());
		$this->setSizeY($this->sizeY + 7 + Constants\UI::PIXEL);
		\ManiaLib\Utils\Logger::info($this->getSizeY());
		\ManiaLib\Utils\Logger::info('--');
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
		$this->content->setSize($this->sizeX, $this->sizeY-Constants\UI::TITLE_HEIGHT);
	}
	
	function preFilter()
	{
		$this->name->setSize($this->getNameWidth(), $this->getNameHeight());
		$this->time->setSize($this->getTimeWidth());
	}
	
	protected function getNameWidth()
	{
		return $this->sizeX;
	}
	
	protected function getNameHeight()
	{
		return Constants\UI::TITLE_HEIGHT - ($this->time->isVisible() ? $this->time->getSizeY() : 0);
	}
	
	protected function getTimeWidth()
	{
		return $this->getNameWidth();
	}
}

?>
