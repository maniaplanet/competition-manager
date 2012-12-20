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
use ManiaLib\Gui\Layouts\Column;

class BulletList extends Frame
{
	/** @var int */
	private $level = 0;
	
	function __construct($sizeX=0, $sizeY=0)
	{
		parent::__construct($sizeX, $sizeY);
		
		$layout = new Column($sizeX, $sizeY);
		$layout->setBorder(4, 4);
		$layout->setMarginHeight(6);
		$this->setLayout($layout);
	}
	
	function beginLevel()
	{
		++$this->level;
	}
	
	function endLevel()
	{
		--$this->level;
	}
	
	function addBullet($text)
	{
		static $bullets = array('$<$o$06b+$> ', '$<$o$0af+$> ');
		
		$bullet = new Label($this->sizeX - 4*($this->level+2), 0);
		$bullet->setPosX($this->level*4);
		$bullet->setTextSize(2);
		$bullet->setTextColor('fff');
		$bullet->setText($bullets[$this->level].$text);
		$this->add($bullet);
	}
}

?>
