<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 7620 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-06-29 16:35:07 +0200 (ven., 29 juin 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLib\Gui\Elements\Quad;
use ManiaLive\Event\Dispatcher;
use ManiaLive\Features\Tick\Listener as TickListener;
use ManiaLive\Features\Tick\Event as TickEvent;
use ManiaLivePlugins\CompetitionManager\Constants;
use ManiaLivePlugins\CompetitionManager\Controls\Counter;

class CountDown extends \ManiaLive\Gui\Window implements TickListener
{
	/** @var Counter */
	private $leftCountdown;
	/** @var Counter */
	private $rightCountdown;
	/** @var \DateTime */
	private $endTime;
	/** @var integer */
	private $lastUpdate;
	/** @var integer */
	private $updateInterval;
	
	protected function onConstruct()
	{
		$this->setSize(20, 10);
		$this->setHalign('right');
		$this->setPosition(125-Constants\UI::PIXEL, -(55+Constants\UI::PIXEL));
		
		$ui = new Quad(20, 10);
		$ui->setBgcolor('0008');
		$this->addComponent($ui);
		
		$this->leftCountdown = new Counter();
		$this->leftCountdown->setHalign('center');
		$this->leftCountdown->setPosition(5.5);
		$this->addComponent($this->leftCountdown);
		
		$this->rightCountdown = new Counter();
		$this->rightCountdown->setHalign('center');
		$this->rightCountdown->setPosition(14.5);
		$this->addComponent($this->rightCountdown);
	}
	
	function start($endTime)
	{
		$this->endTime = $endTime;
		$this->lastUpdate = $this->updateInterval = 0;
		$this->onTick();
		Dispatcher::register(TickEvent::getClass(), $this);
	}
	
	function onTick()
	{
		$time = new \DateTime();
		$timestamp = $time->getTimestamp();
		if($timestamp - $this->lastUpdate >= $this->updateInterval)
		{
			$this->lastUpdate = $timestamp;
			$interval = $time->diff($this->endTime);
			$absoluteInterval = $this->endTime->getTimestamp() - $timestamp;
			if($absoluteInterval <= 0)
			{
				Dispatcher::unregister(TickEvent::getClass(), $this);
				return;
			}
			if($absoluteInterval >= 86400)
			{
				$this->updateInterval = $absoluteInterval == 86400 ? ($interval->s ?: 60) : ($interval->i ?: 60) * 60 + $interval->s;
				$this->leftCountdown->setLabel('$08fdays');
				$this->rightCountdown->setLabel('$08fhours');
				$this->leftCountdown->setCounter(sprintf('$0f0%02d', intval($absoluteInterval / 86400)));
				$this->rightCountdown->setCounter(sprintf('$0f0%02d', $interval->h));
			}
			else if($absoluteInterval >= 3600)
			{
				$this->updateInterval = $absoluteInterval == 3600 ? 1 : ($interval->s ?: 60);
				$this->leftCountdown->setLabel('$08fhours');
				$this->rightCountdown->setLabel('$08fmins');
				$this->leftCountdown->setCounter(sprintf('$af0%02d', $interval->h));
				$this->rightCountdown->setCounter(sprintf('$af0%02d', $interval->i));
			}
			else
			{
				$this->updateInterval = 1;
				$this->leftCountdown->setLabel('$08fmins');
				$this->rightCountdown->setLabel('$08fsecs');
				if($interval->i >= 10)
				{
					$this->leftCountdown->setCounter(sprintf('$ff0%02d', $interval->i));
					$this->rightCountdown->setCounter(sprintf('$ff0%02d', $interval->s));
				}
				else if($interval->i >= 3)
				{
					$this->leftCountdown->setCounter(sprintf('$fa0%02d', $interval->i));
					$this->rightCountdown->setCounter(sprintf('$fa0%02d', $interval->s));
				}
				else if($interval->i >= 1)
				{
					$this->leftCountdown->setCounter(sprintf('$f50%02d', $interval->i));
					$this->rightCountdown->setCounter(sprintf('$f50%02d', $interval->s));
				}
				else
				{
					$this->leftCountdown->setCounter(sprintf('$f00%02d', $interval->i));
					$this->rightCountdown->setCounter(sprintf('$f00%02d', $interval->s));
				}
			}
		}
		$this->redraw();
	}
	
	function destroy()
	{
		parent::destroy();
		Dispatcher::unregister(TickEvent::getClass(), $this);
	}
}

?>
