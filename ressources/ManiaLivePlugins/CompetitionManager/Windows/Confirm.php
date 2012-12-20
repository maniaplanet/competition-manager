<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 7620 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-06-29 16:35:07 +0200 (ven., 29 juin 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLib\Gui\Elements\Icons128x128_Blink;
use ManiaLivePlugins\CompetitionManager\Constants;
use ManiaLivePlugins\CompetitionManager\Controls\HighlightedLabel;

class Confirm extends \ManiaLive\Gui\Window
{
	/** @var Icons128x128_Blink */
	private $blink;
	/** @var HighlightedLabel */
	private $message;
	
	protected function onConstruct()
	{
		$this->setSize(30, 10);
		$this->setHalign('right');
		$this->setPosition(125-Constants\UI::PIXEL, 81);
		
		$this->blink = new Icons128x128_Blink();
		$this->blink->setSubStyle(Icons128x128_Blink::ShareBlink);
		$this->blink->setSize(60, 20);
		$this->blink->setScale(.5);
		$this->blink->setVisibility(false);
		$this->addComponent($this->blink);
		
		$this->message = new HighlightedLabel(30, 10);
		$this->message->highlight->setBgcolorFocus('000a');
		$this->message->label->setHalign('center');
		$this->message->label->setPosX(15);
		$this->message->label->enableAutonewline();
		$this->message->label->setMaxline(2);
		$this->message->setLabelMargin(1);
		$this->addComponent($this->message);
	}
	
	function set($message, $action=null, $manialink=null)
	{
		$this->message->highlight->setAction($action);
		$this->message->highlight->setManialink($manialink);
		$this->message->label->setText($message);
	}
	
	function blink($on=true)
	{
		$this->blink->setVisibility($on);
		if($on)
		{
			$this->message->highlight->setBgcolor('0004');
			$this->message->highlight->setBgcolor('0006');
		}
		else
		{
			$this->message->highlight->setBgcolor('0008');
			$this->message->highlight->setBgcolor('000a');
		}
	}
}

?>
