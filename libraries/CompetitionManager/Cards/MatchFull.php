<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Cards;

use CompetitionManager\Constants;
use CompetitionManager\Utils\Formatting;

class MatchFull extends RankingFull
{
	/** @var HighlightedLabel */
	private $button;
	/** @var HighlightedLabel */
	private $close;
	/** @var \DateTime */
	private $serverStartTime;
	
	function __construct()
	{
		parent::__construct(Constants\UI::STANDARD_WIDTH, Constants\UI::TITLE_HEIGHT-Constants\UI::PIXEL);
		
		$this->button = new HighlightedLabel(3*Constants\UI::TITLE_HEIGHT, Constants\UI::TITLE_HEIGHT);
		$this->button->label->setRelativeHalign('center');
		$this->button->label->setHalign('center');
		$this->close = new HighlightedLabel(Constants\UI::TITLE_HEIGHT, Constants\UI::TITLE_HEIGHT);
		$this->close->label->setRelativeHalign('center');
		$this->close->label->setHalign('center');
		$this->close->label->setText('$oX');
		$this->close->setVisibility(false);
		
		$this->title->add($this->button);
		$this->title->add($this->close);
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
		parent::setState($state);
		$this->button->highlight->setBgcolor(Constants\UI::STATE_COLOR($state));
		$this->button->highlight->setBgcolorFocus(Constants\UI::STATE_COLOR($state, true));
		$this->close->highlight->setBgcolor(Constants\UI::STATE_COLOR($state));
		$this->close->highlight->setBgcolorFocus(Constants\UI::STATE_COLOR($state, true));
	}
	
	function preFilter()
	{
		parent::preFilter();
		
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
	
	protected function getNameWidth()
	{
		$availableWidth = $this->sizeX;
		
		if($this->button->isVisible())
			$availableWidth -= $this->button->getSizeX() + Constants\UI::PIXEL;
		if($this->close->isVisible())
			$availableWidth -= $this->close->getSizeX() + Constants\UI::PIXEL;
		
		return $availableWidth;
	}
}

?>
