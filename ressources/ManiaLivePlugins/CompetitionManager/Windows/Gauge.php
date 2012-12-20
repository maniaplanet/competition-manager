<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Utils\Color;
use ManiaLivePlugins\CompetitionManager\Constants;

class Gauge extends \ManiaLive\Gui\Window
{
	/** @var Quad */
	private $gauge;
	/** @var Label */
	private $currently;
	/** @var int */
	private $min;
	/** @var int */
	private $max;
	
	protected function onConstruct()
	{
		$this->setSize(120, 15);
		$this->setHalign('center');
		$this->setPosition(0, 65);
		
		$lobby = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		$this->min = $lobby->stage->minSlots;
		$this->max = $lobby->stage->maxSlots;
		
		$ui = new Quad(120, 15);
		$ui->setBgcolor('0008');
		$this->addComponent($ui);
		
		$ui = new Quad(116, 5);
		$ui->setBgcolor('0008');
		$ui->setPosition(2, -8);
		$this->addComponent($ui);
		
		$this->gauge = new Quad(0, 5-2*Constants\UI::PIXEL);
		$this->gauge->setBgcolor('f00a');
		$this->gauge->setPosition(2+Constants\UI::PIXEL, -(8+Constants\UI::PIXEL));
		$this->addComponent($this->gauge);
		
		$registrations = new Label(30, 0);
		$registrations->setPosition(4, -4);
		$registrations->setAlign('left', 'center2');
		$registrations->setTextSize(2);
		$registrations->setText('$sRegistrations');
		$this->addComponent($registrations);
		
		$this->currently = new Label(30, 0);
		$this->currently->setPosition(60, -4);
		$this->currently->setAlign('center', 'center2');
		$this->currently->setTextSize(2);
		$this->currently->setText(sprintf('$s$iCurrently: %d', 0));
		$this->addComponent($this->currently);
		
		$required = new Label(30, 0);
		$required->setPosition(116, -4);
		$required->setAlign('right', 'center2');
		$required->setTextSize(2);
		$required->setText(sprintf('$s$iRequired: %d', $this->min));
		$this->addComponent($required);
		
		if($this->max > $this->min)
		{
			$this->currently->setPosX(45);
			$required->setPosX(75);
			$required->setHalign('center');
			
			$ui = new Label(30, 0);
			$ui->setPosition(116, -4);
			$ui->setAlign('right', 'center2');
			$ui->setTextSize(2);
			$ui->setText(sprintf('$s$iMaximum: %d', $this->max));
			$this->addComponent($ui);
			
			$ui = new Quad(2*Constants\UI::PIXEL, 6);
			$ui->setBgcolor('d30');
			$ui->setPosition(2 + 116 * $this->min / $this->max, -7.5);
			$this->addComponent($ui);
		}
	}
	
	function setLevel($level)
	{
		if($level > $this->min)
			$hue = 2 + ($level - $this->min) / ($this->max - $this->min);
		else
			$hue = 2 * $level / $this->min;
		$bgColor = Color::Rgb12ToString(Color::Rgb24ToRgb12(Color::HsvToRgb24(array('hue' => $hue, 'saturation' => 1, 'value' => 1))));
		$this->gauge->setBgcolor($bgColor.'a');
		$this->gauge->setSizeX((116-2*Constants\UI::PIXEL) * $level / $this->max);
		$this->currently->setText(sprintf('$s$iCurrently: %d', $level));
	}
}

?>
