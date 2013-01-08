<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLive\Event\Dispatcher;
use ManiaLive\Features\Tick;
use ManiaLib\Gui\Elements\Audio;
use ManiaLib\Gui\Elements\Label;

class AudioCountDown extends \ManiaLive\Gui\Window implements Tick\Listener
{
	/** @var int */
	private $counter;
	/** @var Label */
	private $label;
	
	protected function onConstruct()
	{
		$this->setScale(2);
		
		$this->label = new Label(10, 0);
		$this->label->setStyle(Label::TextRaceChronoOfficial);
		$this->label->setAlign('center', 'center');
		$this->label->setPosY(.75);
		$this->addComponent($this->label);
		
		$sound = new Audio();
		$sound->setData('http://static.maniaplanet.com/manialinks/lobbyTimer.wav', true);
		$sound->setPosition(200);
		$sound->autoPlay();
		$this->addComponent($sound);
	}
	
	function start($count)
	{
		$this->counter = $count;
		$this->onTick();
		Dispatcher::register(Tick\Event::getClass(), $this);
	}
	
	function onTick()
	{
		if($this->counter == -1)
			return $this->destroy();
		
		$this->label->setText($this->counter--);
		$this->redraw();
	}
	
	function destroy()
	{
		parent::destroy();
		Dispatcher::unregister(Tick\Event::getClass(), $this);
	}
}

?>
