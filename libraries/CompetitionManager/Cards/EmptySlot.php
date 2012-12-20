<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9011 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-11-29 18:57:57 +0100 (jeu., 29 nov. 2012) $:
 */

namespace CompetitionManager\Cards;

class EmptySlot extends HighlightedLabel
{
	function __construct($sizeX = 20, $sizeY = 7)
	{
		parent::__construct($sizeX, $sizeY);
		$this->highlight->setBgcolor('2225');
		$this->label->setTextColor('fff8');
		$this->label->setPosX(1);
		$this->setLabelMargin(1);
	}
	
	function setLabel($text)
	{
		$this->label->setText('$s$i'.$text);
	}
}

?>
