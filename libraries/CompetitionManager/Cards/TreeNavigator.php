<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9051 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-04 18:12:08 +0100 (mar., 04 déc. 2012) $:
 */

namespace CompetitionManager\Cards;

use ManiaLib\Gui\Elements\UIConstructionSimple_Buttons;

class TreeNavigator extends \ManiaLib\Gui\Elements\Frame
{
	public $arrowNext;
	public $arrowPreviousUp;
	public $arrowPreviousDown;
	public $arrowUp;
	public $arrowDown;
	
	public $arrowNextTooltip = 'To next round';
	public $arrowPreviousUpTooltip = 'To upper half details';
	public $arrowPreviousDownTooltip = 'To lower half details';
	public $arrowUpTooltip = 'To upper match';
	public $arrowDownTooltip = 'To lower match';
	
	function __construct()
	{
		parent::__construct();
		$this->setValign('center');

		$this->arrowNext = new UIConstructionSimple_Buttons(8);
		$this->arrowNext->setSubStyle(UIConstructionSimple_Buttons::Right);
		$this->arrowNext->setRelativeAlign('right', 'center');
		$this->arrowNext->setAlign('left', 'center');
		
		$this->arrowPreviousUp = new UIConstructionSimple_Buttons(8);
		$this->arrowPreviousUp->setSubStyle(UIConstructionSimple_Buttons::Left);
		$this->arrowPreviousUp->setRelativeAlign('left', 'top');
		$this->arrowPreviousUp->setAlign('right', 'center');
		
		$this->arrowPreviousDown = new UIConstructionSimple_Buttons(8);
		$this->arrowPreviousDown->setSubStyle(UIConstructionSimple_Buttons::Left);
		$this->arrowPreviousDown->setRelativeAlign('left', 'bottom');
		$this->arrowPreviousDown->setAlign('right', 'center');
		
		$this->arrowUp = new UIConstructionSimple_Buttons(8);
		$this->arrowUp->setSubStyle(UIConstructionSimple_Buttons::Up);
		$this->arrowUp->setRelativeAlign('center', 'top');
		$this->arrowUp->setAlign('center', 'bottom');
		
		$this->arrowDown = new UIConstructionSimple_Buttons(8);
		$this->arrowDown->setSubStyle(UIConstructionSimple_Buttons::Down);
		$this->arrowDown->setRelativeAlign('center', 'bottom');
		$this->arrowDown->setAlign('center', 'top');
		
		$this->add($this->arrowNext);
		$this->add($this->arrowPreviousUp);
		$this->add($this->arrowPreviousDown);
		$this->add($this->arrowUp);
		$this->add($this->arrowDown);
	}
	
	function preFilter()
	{
		if(!$this->arrowNext->getManialink())
			$this->arrowNext->setVisibility(false);
		else
		{
			$this->arrowNext->setId('tree-navigator:next');
			$this->arrowNext->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowNext->getId(), $this->arrowNextTooltip);
		}
		
		if(!$this->arrowPreviousUp->getManialink())
			$this->arrowPreviousUp->setVisibility(false);
		else
		{
			$this->arrowPreviousUp->setId('tree-navigator:previous-up');
			$this->arrowPreviousUp->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowPreviousUp->getId(), $this->arrowPreviousUpTooltip);
		}
		
		if(!$this->arrowPreviousDown->getManialink())
			$this->arrowPreviousDown->setVisibility(false);
		else
		{
			$this->arrowPreviousDown->setId('tree-navigator:previous-down');
			$this->arrowPreviousDown->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowPreviousDown->getId(), $this->arrowPreviousDownTooltip);
		}
		
		if(!$this->arrowUp->getManialink())
			$this->arrowUp->setVisibility(false);
		else
		{
			$this->arrowUp->setId('tree-navigator:up');
			$this->arrowUp->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowUp->getId(), $this->arrowUpTooltip);
		}
		
		if(!$this->arrowDown->getManialink())
			$this->arrowDown->setVisibility(false);
		else
		{
			$this->arrowDown->setId('tree-navigator:down');
			$this->arrowDown->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowDown->getId(), $this->arrowDownTooltip);
		}
	}
}

?>
