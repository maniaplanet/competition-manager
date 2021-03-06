<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Cards;

use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\ManiaScript;
use CompetitionManager\Constants;

class Dialog extends Window
{
	const ERROR = 0;
	const WARNING = 1;
	const SUCCESS = 2;
	
	/** @var Bgs1 */
	private $blur;
	/** @var Label */
	private $message;
	/** @var HighlightedLabel */
	private $button;
	/** @var mixed[][] */
	private $actions = array();
	
	function __construct($sizeX=Constants\UI::DIALOG_WIDTH, $sizeY=Constants\UI::DIALOG_HEIGHT)
	{
		parent::__construct($sizeX, $sizeY);
		$this->setRelativeAlign('center', 'center');
		$this->setAlign('center', 'center');
		
		$this->background->setBgcolor('eee');
		
		$this->blur = new Bgs1(320, 180);
		$this->blur->setSubStyle(Bgs1::BgDialogBlur);
		$this->blur->setRelativeAlign('center', 'center');
		$this->blur->setAlign('center', 'center');
		$this->blur->setId($this->getId().':blur');
		$this->blur->setScriptEvents();
		
		$this->message = new Label(($sizeX - 20) / 1.3, 0);
		$this->message->setStyle(Label::TextTips);
		$this->message->setRelativeAlign('center', 'center');
		$this->message->setAlign('center', 'center2');
		$this->message->setPosition(0, 3, .2);
		$this->message->setScale(1.3);
		$this->message->enableAutonewline();
		
		$this->button = new HighlightedLabel(35, 7);
		$this->button->setRelativeAlign('center', 'bottom');
		$this->button->setAlign('center', 'bottom');
		$this->button->setPosition(0, 3, .2);
		$this->button->label->setRelativeHalign('center');
		$this->button->label->setHalign('center');
		$this->button->label->setText('$o'._('OK'));
		$this->button->highlight->setId($this->getId().':button');
		$this->button->highlight->setScriptEvents();
		
		$this->add($this->blur);
		$this->content->add($this->message);
		$this->content->add($this->button);
	}
	
	function setType($type)
	{
		static $bgColors = array(self::ERROR => 'c20', self::WARNING => 'd90', self::SUCCESS => '290');
		
		$this->setTitleBackground($bgColors[$type]);
		$this->button->highlight->setBgcolor($bgColors[$type].'a');
		$this->button->highlight->setBgcolorFocus($bgColors[$type]);
	}
	
	function setContent($content)
	{
		$this->message->setText($content);
	}
	
	function addCustomAction(array $action)
	{
		$this->actions[] = $action;
	}
	
	function setAsExternal()
	{
		$this->blur->setVisibility(false);
		$this->button->highlight->setAction(0);
		$this->button->highlight->setId(null);
		$this->button->highlight->setScriptEvents(0);
	}
	
	function preFilter()
	{
		if($this->button->highlight->getId())
		{
			ManiaScript\Manipulation::disableLinks();
			ManiaScript\Event::addListener($this->button->highlight->getId(), ManiaScript\Event::mouseClick, array(ManiaScript\Action::hide, $this->getId()));
			ManiaScript\Event::addListener($this->button->highlight->getId(), ManiaScript\Event::mouseClick, array(ManiaScript\Action::enable_links));
			foreach($this->actions as $action)
				ManiaScript\Event::addListener($this->button->highlight->getId(), ManiaScript\Event::mouseClick, $action);
		}
	}
}

?>
