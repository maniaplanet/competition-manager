<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9012 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-11-30 10:41:17 +0100 (ven., 30 nov. 2012) $:
 */

namespace CompetitionManager\Cards;

use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Layouts\Line;
use CompetitionManager\Constants;

class Participant extends Frame
{
	const RANK_TOOLTIP = 0;
	const BG_COLOR = 1;
	const BG_COLOR_USER = 2;
	
	/** @var HighlightedLabel */
	private $name;
	/** @var HighlightedLabel */
	private $rank;
	/** @var HighlightedLabel */
	private $score;
	/** @var bool */
	private $isUser = false;
	/** @var int */
	private $qualified = Constants\Qualified::UNKNOWN;
	
	function __construct($sizeX = 0, $sizeY = 0)
	{
		parent::__construct($sizeX, $sizeY);
		
		$layout = new Line($sizeX, $sizeY);
		$layout->setMarginWidth(Constants\UI::PIXEL);
		$this->setLayout($layout);
		
		$this->name = new HighlightedLabel($sizeX, $sizeY);
		$this->name->label->setPosX($this->sizeY / 5);
		$this->name->setLabelMargin($this->sizeY / 5);
		$this->rank = new HighlightedLabel($this->sizeY, $this->sizeY);
		$this->rank->label->setRelativeHalign('right');
		$this->rank->label->setHalign('right');
		$this->rank->label->setPosX(-$this->sizeY / 10);
		$this->rank->setLabelMargin($this->sizeY / 10);
		$this->score = new HighlightedLabel(1.5*$this->sizeY, $this->sizeY);
		$this->score->label->setRelativeHalign('center');
		$this->score->label->setHalign('center');
		
		$this->add($this->rank);
		$this->add($this->name);
		$this->add($this->score);
	}
	
	function setName($name)
	{
		$this->name->label->setText($name);
	}
	
	function setRank($rank)
	{
		$this->rank->label->setText($rank ?: '-');
	}
	
	function setScore($score, $isTime)
	{
		$this->score->label->setText($score);
		if($isTime)
			$this->score->setSizeX(2*$this->sizeY);
	}
	
	function setVisibilities($showRank, $showScore)
	{
		$this->rank->setVisibility($showRank);
		$this->score->setVisibility($showScore);
		$this->name->setSize($this->getNameWidth());
	}
	
	function setCustomization($isUser, $qualified)
	{
		$this->isUser = $isUser;
		$this->qualified = $qualified;
	}
	
	function onResize($oldX, $oldY)
	{
		parent::onResize($oldX, $oldY);
		$this->rank->setSize($this->sizeY * $this->rank->getSizeX() / $oldX, $this->sizeY);
		$this->rank->setLabelMargin($this->sizeY / 10);
		$this->rank->label->setPosX(-$this->sizeY / 10);
		$this->score->setSizeY($this->sizeY);
		$this->name->setSize($this->getNameWidth(), $this->sizeY);
		$this->name->setLabelMargin($this->sizeY / 5);
		$this->name->label->setPosX($this->sizeY / 5);
	}
	
	function preFilter()
	{
		static $customizations = array(
			Constants\Qualified::NO => array(
				self::RANK_TOOLTIP => '$f80Not qualified',
				self::BG_COLOR => '8005',
				self::BG_COLOR_USER => '8009'
			),
			Constants\Qualified::YES => array(
				self::RANK_TOOLTIP => '$080Qualified',
				self::BG_COLOR => '0805',
				self::BG_COLOR_USER => '0809'
			),
			Constants\Qualified::UNKNOWN => array(
				self::RANK_TOOLTIP => null,
				self::BG_COLOR => 'aaa5',
				self::BG_COLOR_USER => 'aaa9'
			),
			Constants\Qualified::LEAVED => array(
				self::RANK_TOOLTIP => '$666Did not show',
				self::BG_COLOR => '2225',
				self::BG_COLOR_USER => '2229'
			)
		);
		
		if($this->rank->isVisible() && $customizations[$this->qualified][self::RANK_TOOLTIP])
		{
			$tooltipId = 'participation:'.uniqid().':rank';
			$this->rank->setId($tooltipId);
			$this->rank->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($tooltipId, $customizations[$this->qualified][self::RANK_TOOLTIP]);
		}
		
		$this->rank->highlight->setBgcolor($customizations[$this->qualified][$this->isUser ? self::BG_COLOR_USER : self::BG_COLOR]);
		$this->name->highlight->setBgcolor($customizations[$this->qualified][$this->isUser ? self::BG_COLOR_USER : self::BG_COLOR]);
		$this->score->highlight->setBgcolor($customizations[$this->qualified][$this->isUser ? self::BG_COLOR_USER : self::BG_COLOR]);
	}
	
	private function getNameWidth()
	{
		$availableWidth = $this->sizeX;
		
		if($this->rank->isVisible())
			$availableWidth -= $this->rank->getSizeX() + Constants\UI::PIXEL;
		if($this->score->isVisible())
			$availableWidth -= $this->score->getSizeX() + Constants\UI::PIXEL;
		
		return $availableWidth;
	}
}

?>
