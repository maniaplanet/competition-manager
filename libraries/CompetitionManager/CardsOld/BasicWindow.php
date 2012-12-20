<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\CardsOld;

use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;

class BasicWindow extends \ManiaLib\Gui\Elements\Frame
{
	/** @var Quad */
	public $background;
	/** @var Quad */
	public $titleBackground;
	/** @var Label */
	public $title;
	/** @var Label */
	public $text;
	
	function __construct()
	{
		parent::__construct(150);
		$this->setLayout(new \ManiaLib\Gui\Layouts\Spacer());
		
		$this->background = new Bgs1();
		$this->background->setSubStyle(Bgs1::BgTitle3_3);
		$this->add($this->background);
		
		$this->titleBackground = new Bgs1(null, 18);
		$this->titleBackground->setPosZ(.1);
		$this->titleBackground->setSubStyle(Bgs1::BgTitle3_5);
		$this->add($this->titleBackground);
		
		$this->title = new Label($this->sizeX - 6, 0);
		$this->title->setAlign('center', 'center2');
		$this->title->setRelativeHalign('center');
		$this->title->setPosition(0, -9, .2);
		$this->title->setStyle(Label::TextRankingsBig);
		$this->add($this->title);
		
		$this->text = new Label(($this->sizeX - 6) / 1.2, 0);
		$this->text->setPosition(6, -20, .1);
		$this->text->setScale(1.2);
		$this->add($this->text);
	}
	
	function onResize($oldX, $oldY)
	{
		parent::onResize($oldX, $oldY);
		$this->background->setSize($this->sizeX, $this->sizeY);
		$this->titleBackground->setSizeX($this->sizeX);
		$this->title->setSizeX($this->sizeX - 6);
		$this->text->setSizeX(($this->sizeX - 6) / 1.2);
	}
}

?>
