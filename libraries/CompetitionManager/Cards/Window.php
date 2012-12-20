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

class Window extends Frame
{
	/** @var Bgs1InRace */
	private $shadow;
	/** @var HighlightedLabel */
	private $title;
	/** @var Quad */
	private $background;
	/** @var Frame */
	public $content;
	
	function __construct($sizeX=Constants\UI::STANDARD_WIDTH, $sizeY=Constants\UI::TITLE_HEIGHT)
	{
		parent::__construct($sizeX, $sizeY);
		$this->setLayout(new Layouts\Spacer($sizeX, $sizeY));
		
		$this->shadow = new Bgs1InRace($sizeX+11, $sizeY+11);
		$this->shadow->setSubStyle(Bgs1InRace::BgButtonShadow);
		$this->shadow->setRelativeAlign('center', 'center');
		$this->shadow->setAlign('center', 'center');
		
		$this->title = new HighlightedLabel($sizeX, Constants\UI::TITLE_HEIGHT);
		$this->title->setPosZ(.1);
		$this->title->label->setRelativeHalign('center');
		$this->title->label->setHalign('center');
		
		$this->background = new Quad($sizeX, $sizeY-Constants\UI::TITLE_HEIGHT);
		$this->background->setBgcolor('0008');
		$this->background->setPosition(0, -Constants\UI::TITLE_HEIGHT, .1);
		
		$this->content = new Frame($sizeX, $sizeY-Constants\UI::TITLE_HEIGHT);
		$this->content->setPosition(0, -Constants\UI::TITLE_HEIGHT, .2);
		
		$this->add($this->shadow);
		$this->add($this->title);
		$this->add($this->background);
		$this->add($this->content);
	}
	
	function onResize($oldX, $oldY)
	{
		parent::onResize($oldX, $oldY);
		$this->shadow->setSize($this->sizeX+11, $this->sizeY+11);
		$this->title->setSizeX($this->sizeX);
		$this->background->setSize($this->sizeX, $this->sizeY-Constants\UI::TITLE_HEIGHT);
		$this->content->setSize($this->sizeX, $this->sizeY-Constants\UI::TITLE_HEIGHT);
	}
	
	function setTitle($title)
	{
		$this->title->label->setText($title);
	}
	
	function setTitleBackground($bgcolor)
	{
		$this->title->highlight->setBgcolor($bgcolor);
	}
}

?>
