<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 7620 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-06-29 16:35:07 +0200 (ven., 29 juin 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Controls;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;

class Counter extends \ManiaLive\Gui\Control
{
	/** @var Label */
	private $label;
	/** @var Label */
	private $counter;
	
	function __construct()
	{
		$this->setSize(10, 10);
		$ui = new Bgs1InRace(9, 6);
		$ui->setSubStyle(Bgs1InRace::BgList);
		$ui = new Quad(8, 5.5);
		$ui->setBgcolor('0008');
		$ui->setAlign('center', 'center');
		$ui->setPosition(5, -6.5);
		$this->addComponent($ui);
		
		$this->label = new Label(33);
		$this->label->setStyle(Label::TextRaceChrono);
		$this->label->setScale(.3);
		$this->label->setAlign('center', 'center');
		$this->label->setPosition(5, -1.75);
		$this->addComponent($this->label);
		
		$this->counter = new Label(15);
		$this->counter->setStyle(Label::TextRaceChrono);
		$this->counter->setScale(.6);
		$this->counter->setAlign('center', 'center');
		$this->counter->setPosition(5, -6.25);
		$this->addComponent($this->counter);
	}
	
	function setLabel($label)
	{
		$this->label->setText($label);
	}
	
	function setCounter($counter)
	{
		$this->counter->setText($counter);
	}
}

?>
