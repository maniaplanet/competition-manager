<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Controls;

use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;

class HighlightedLabel extends \ManiaLive\Gui\Control
{
	/** @var Quad */
	public $highlight;
	/** @var Label */
	public $label;
	/** @var float */
	private $labelMargin = 0;
	
	function __construct($sizeX = 20, $sizeY = 5)
	{
		$this->highlight = new Quad($sizeX, $sizeY);
		$this->highlight->setBgcolor('0008');
		$this->label = new Label($sizeX, 0);
		$this->label->setStyle(null);
		$this->label->setTextSize(1);
		$this->label->setValign('center2');
		$this->label->setPosition(0, -$sizeY/2, .1);
		$this->addComponent($this->highlight);
		$this->addComponent($this->label);
		$this->setSize($sizeX, $sizeY);
	}
	
	function setLabelMargin($margin)
	{
		$this->labelMargin = $margin;
		$this->label->setSizeX(($this->sizeX - 2*$this->labelMargin) / ($this->label->getScale() ?: 1));
	}
	
	function onResize($oldX, $oldY)
	{
		$this->highlight->setSize($this->sizeX, $this->sizeY);
		$this->label->setScale($this->sizeY / 6.5);
		$this->label->setSizeX(($this->sizeX - 2*$this->labelMargin) / ($this->label->getScale() ?: 1));
	}
	
	function onDraw()
	{
		if(substr($this->label->getText(), 0, 2) != '$s')
			$this->label->setText('$s'.$this->label->getText());
	}
}

?>
