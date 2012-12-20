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
use ManiaLib\Gui\Layouts\Column;
use CompetitionManager\Constants;

class Accordion extends Frame
{
	/** @var Bgs1InRace */
	private $shadow;
	/** @var Quad */
	public $background;
	/** @var Frame */
	private $tabsFrame;
	/** @var Frame */
	private $selectedTab;
	/** @var HighlightedLabel */
	private $lastTab;
	
	function __construct($sizeX=Constants\UI::STANDARD_WIDTH, $sizeY=Constants\UI::ACCORDION_HEIGHT)
	{
		parent::__construct($sizeX, $sizeY);
		
		$this->shadow = new Bgs1InRace($sizeX+11, $sizeY+11);
		$this->shadow->setSubStyle(Bgs1InRace::BgButtonShadow);
		$this->shadow->setRelativeAlign('center', 'center');
		$this->shadow->setAlign('center', 'center');
		$this->shadow->setPosZ(-.2);
		
		$this->background = new Quad($sizeX, $sizeY);
		$this->background->setBgcolor('0008');
		$this->background->setRelativeAlign('center', 'center');
		$this->background->setAlign('center', 'center');
		$this->background->setPosZ(-.1);
		
		$layout = new Column($sizeX, $sizeY);
		$layout->setMarginHeight(2*Constants\UI::PIXEL);
		$this->tabsFrame = new Frame($sizeX, $sizeY);
		$this->tabsFrame->setLayout($layout);
		
		$this->selectedTab = new BulletList($sizeX, $sizeY);
		
		$this->add($this->shadow);
		$this->add($this->background);
		$this->add($this->tabsFrame);
	}
	
	function addTab($title, $link)
	{
		$ui = new HighlightedLabel($this->sizeX, Constants\UI::TITLE_HEIGHT);
		$ui->highlight->setBgcolor('2068');
		$ui->highlight->setBgcolorFocus('408c');
		$ui->highlight->setManialink($link);
		$ui->label->setText($title);
		$ui->label->setPosX(2);
		$ui->setLabelMargin(2);
		
		$this->tabsFrame->add($ui);
		$this->lastTab = $ui;
		$this->selectedTab->setSizeY($this->selectedTab->getSizeY()-Constants\UI::TITLE_HEIGHT-2*Constants\UI::PIXEL);
	}
	
	function selectTab()
	{
		$this->tabsFrame->add($this->selectedTab);
		$this->lastTab->highlight->setBgcolor('408c');
		$this->lastTab->highlight->setManialink(null);
		return $this->selectedTab;
	}
}

?>
