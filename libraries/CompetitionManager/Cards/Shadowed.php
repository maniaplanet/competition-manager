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
use ManiaLib\Gui\Layouts;

class Shadowed extends Frame
{
	/** @var Bgs1InRace */
	private $shadow;
	/** @var Quad */
	protected $background;
	
	function __construct($sizeX=0, $sizeY=0)
	{
		parent::__construct($sizeX, $sizeY);
		$this->setId('card:'.uniqid());
		$this->setLayout(new Layouts\Spacer($this->sizeX, $this->sizeY));
		
		$this->shadow = new Bgs1InRace($sizeX+11, $sizeY+11);
		$this->shadow->setSubStyle(Bgs1InRace::BgButtonShadow);
		$this->shadow->setRelativeAlign('center', 'center');
		$this->shadow->setAlign('center', 'center');
		$this->background = new Quad($sizeX, $sizeY);
		$this->background->setPosZ(.1);
		$this->background->setBgcolor('0008');
		$this->background->setBgcolorFocus('4448');
		
		$this->add($this->shadow);
		$this->add($this->background);
	}
	
	function setShadowScale($scale)
	{
		$this->shadow->setScale($scale);
		$this->shadow->setSize($this->sizeX / ($scale ?: 1) + 11, $this->sizeY / ($scale ?: 1) + 11);
	}
	
	function onResize($oldX, $oldY)
	{
		parent::onResize($oldX, $oldY);
		$this->shadow->setSize($this->sizeX / ($this->shadow->getScale() ?: 1) + 11, $this->sizeY / ($this->shadow->getScale() ?: 1) + 11);
		$this->background->setSize($this->sizeX, $this->sizeY);
	}
}

?>
