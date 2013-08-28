<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Cards;

use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Layouts\Column;
use CompetitionManager\Constants;

class Ranking extends Shadowed
{
	/** @var Frame */
	protected $content;
	/** @var int */
	private $lines = 0;
	
	/** @var \CompetitionManager\Services\Participant */
	protected $lastParticipant;
	
	function __construct($sizeX=0, $sizeY=0)
	{
		parent::__construct($sizeX, $sizeY);
		$this->setShadowScale(.5);
		
		// Content
		$layout = new Column();
		$layout->setMarginHeight(Constants\UI::PIXEL);
		$this->content = new Frame();
		$this->content->setLayout($layout);
		$this->content->setPosZ(.2);
		
		$this->add($this->content);
	}
	
	/**
	 * @param \CompetitionManager\Services\Participant $participant
	 * @param bool $showRank
	 * @param bool $showScore
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
		if($this->lastParticipant && $this->lastParticipant->rank === $participant->rank)
			$ui->setRank('=');
		else
			$ui->setRank($participant->rank);
		$ui->setScore($participant->score);
		$ui->setVisibilities($showRank, $showScore);
		$ui->setCustomization($isUser, $participant->qualified);
		
		$this->content->add($ui);
		$this->setSizeY($this->sizeY + 5 + Constants\UI::PIXEL);
		$this->lastParticipant = $participant;
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
	
	function setManialink($manialink)
	{
		$this->background->setManialink($manialink);
	}
}

?>
