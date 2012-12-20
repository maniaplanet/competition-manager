<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9011 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-11-29 18:57:57 +0100 (jeu., 29 nov. 2012) $:
 */

namespace CompetitionManager\CardsOld;

use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;

class Map extends Bgs1
{
	private $screenshot;
	private $name;
	
	function __construct($imageSize=20)
	{
		parent::__construct($imageSize + 2, $imageSize + 6);
		$this->subStyle = Bgs1::BgButton;
		
		$this->screenshot = new Quad($imageSize, $imageSize);
		$this->screenshot->setPosition(1, -1);
		$this->addCardElement($this->screenshot);
		
		$this->name = new Label($imageSize / .7, 0);
		$this->name->setScale(.7);
		$this->name->setStyle(Label::TextChallengeNameMedal);
		$this->name->setHalign('center');
		$this->name->setPosition(($imageSize + 2) / 2, -($imageSize + 2));
		$this->addCardElement($this->name);
	}
	
	function setName($name)
	{
		$this->name->setText('$08f'.$name);
	}
	
	function setScreenshot($image)
	{
		$this->screenshot->setImage($image);
	}
}

?>
