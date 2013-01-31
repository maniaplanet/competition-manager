<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Cards;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Layouts\Column;
use CompetitionManager\Constants;

class Match extends Frame
{
	/** @var Bgs1InRace */
	private $shadow;
	/** @var Quad */
	private $background;
	/** @var Frame */
	private $content;
	/** @var int */
	private $lines = 0;
	
	function __construct()
	{
		parent::__construct(Constants\UI::MATCH_WIDTH, -Constants\UI::PIXEL);
		
		// Background
		$this->shadow = new Bgs1InRace();
		$this->shadow->setScale(.5);
		$this->shadow->setSubStyle(Bgs1InRace::BgButtonShadow);
		$this->shadow->setRelativeAlign('center', 'center');
		$this->shadow->setAlign('center', 'center');
		$this->background = new Quad();
		$this->background->setPosZ(.1);
		$this->background->setBgcolor('0008');
		$this->background->setBgcolorFocus('4448');
		
		// Content
		$layout = new Column();
		$layout->setMarginHeight(Constants\UI::PIXEL);
		$this->content = new Frame();
		$this->content->setLayout($layout);
		$this->content->setPosZ(.2);
		
		$this->add($this->shadow);
		$this->add($this->background);
		$this->add($this->content);
	}
	
	/**
	 * @param \CompetitionManager\Services\Participant $participant
	 * @param bool $isUser
	 */
	function addParticipant($participant, $showRank=false, $showScore=false, $isUser=false)
	{
		if(++$this->lines > 6)
		{
			if($this->lines == 7)
				$this->addEllipsis();
			return;
		}
		
		$ui = new Participant($this->sizeX, 5);
		$ui->setName($participant->name);
		$ui->setRank($participant->rank);
		$ui->setScore($participant->score);
		$ui->setVisibilities($showRank, $showScore);
		$ui->setCustomization($isUser, $participant->qualified);
		
		$this->content->add($ui);
		$this->setSizeY($this->sizeY + 5 + Constants\UI::PIXEL);
	}
	
	function addEmpty($label)
	{
		if(++$this->lines > 6)
		{
			if($this->lines == 7)
				$this->addEllipsis();
			return;
		}
		
		$ui = new EmptySlot($this->sizeX, 5);
		$ui->setLabel($label);
		$this->content->add($ui);
		$this->setSizeY($this->sizeY + 5 + Constants\UI::PIXEL);
	}
	
	private function addEllipsis()
	{
		$ui = new EmptySlot($this->sizeX, 5);
		$ui->setLabel('...');
		$this->content->add($ui);
		$this->setSizeY($this->sizeY + 5 + Constants\UI::PIXEL);
	}
	
	function setName($name)
	{
		$this->background->setId('match:'.uniqid());
		$this->background->setScriptEvents();
		\ManiaLib\ManiaScript\UI::tooltip($this->background->getId(), $name);
	}
	
	function setManialink($manialink)
	{
		$this->background->setManialink($manialink);
	}
	
	function onResize($oldX, $oldY)
	{
		parent::onResize($oldX, $oldY);
		$this->shadow->setSize(2*($this->sizeX+5.5), 2*($this->sizeY+5.5));
		$this->background->setSize($this->sizeX, $this->sizeY);
	}
}

?>
