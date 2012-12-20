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
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Layouts\Spacer;

class HighlightedLabel extends Frame
{
	/** @var Quad */
	public $highlight;
	/** @var Label */
	public $label;
	/** @var float */
	private $labelMargin = 0;
	
	function __construct($sizeX = 20, $sizeY = 5)
	{
		parent::__construct($sizeX, $sizeY);
		
		$this->setLayout(new Spacer($sizeX, $sizeY));
		$this->highlight = new Quad($sizeX, $sizeY);
		$this->highlight->setBgcolor('0008');
		$this->label = new Label($sizeX, 0);
		$this->label->setStyle(null);
		$this->label->setTextSize(1);
		$this->label->setRelativeValign('center');
		$this->label->setValign('center2');
		$this->label->setPosZ(.1);
		$this->add($this->highlight);
		$this->add($this->label);
		$this->onResize(0, 0);
	}
	
	function setLabelMargin($margin)
	{
		$this->labelMargin = $margin;
		$this->label->setSizeX(($this->sizeX - 2*$this->labelMargin) / ($this->label->getScale() ?: 1));
	}
	
	function onResize($oldX, $oldY)
	{
		parent::onResize($oldX, $oldY);
		$this->highlight->setSize($this->sizeX, $this->sizeY);
		$this->label->setScale($this->sizeY / 6.5);
		$this->label->setSizeX(($this->sizeX - 2*$this->labelMargin) / ($this->label->getScale() ?: 1));
	}
	
	function preFilter()
	{
		$this->label->setText('$s'.$this->label->getText());
	}
}

?>
