<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLib\Gui\Elements\Label;

class BigMessage extends \ManiaLive\Gui\Window
{
	/** @var Label */
	private $message;
	
	protected function onConstruct()
	{
		$this->message = new Label(280 / .75, 0);
		$this->message->setStyle(Label::TextRaceMessageBig);
		$this->message->setAlign('center', 'bottom');
		$this->message->setScale(.75);
		$this->message->setPosY(10);
		$this->addComponent($this->message);
	}
	
	function set($message)
	{
		$this->message->setText($message);
		$this->redraw();
	}
}

?>
