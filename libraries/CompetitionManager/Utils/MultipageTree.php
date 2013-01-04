<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9051 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-04 18:12:08 +0100 (mar., 04 déc. 2012) $:
 */

namespace CompetitionManager\Utils;

use ManiaLib\Gui\Elements\UIConstructionSimple_Buttons;

class MultipageTree
{
	private $tree;
	private $columns;
	private $round;
	private $offset;
	private $maxOffset;
	private $maxRound;
	
	private $prefix;
	
	function __construct($tree, $columns, $prefix='')
	{
		$this->tree = $tree;
		$this->columns = $columns-1;
		$this->prefix = $prefix ? $prefix.'-' : '';
		
		$request = \ManiaLib\Application\Request::getInstance();
		$this->maxRound = count($this->tree) - $this->columns - 1;
		$this->round = (int) $request->get($this->prefix.'round', 0);
		if($this->round < 0)
			$this->round = 0;
		else if($this->round > $this->maxRound)
			$this->round = $this->maxRound;
		
		$this->maxOffset = (count($this->tree[$this->round]) >> $this->columns) - 1;
		$this->offset = (int) $request->get($this->prefix.'offset', 0);
		if($this->offset < 0)
			$this->offset = 0;
		else if($this->offset > $this->maxOffset)
			$this->offset = $this->maxOffset;
	}
	
	function getSubTree()
	{
		for($i=0, $slice=1<<$this->columns; $slice>0; ++$i, $slice>>=1)
			$subTree[] = array_slice($this->tree[$this->round + $i], $this->offset * $slice, $slice);
		
		return $subTree;
	}
	
	function getTreeBase()
	{
		return array($this->round, $this->offset*(1<<$this->columns));
	}
	
	function createNavigator()
	{
		$treeNavigator = new TreeNavigator();
		
		$request = \ManiaLib\Application\Request::getInstance();
		if($this->round < $this->maxRound)
		{
			$request->set($this->prefix.'round', $this->round + 1);
			$request->set($this->prefix.'offset', $this->offset >> 1);
			$treeNavigator->arrowNext->setManialink($request->createLink());
			$treeNavigator->arrowNextTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns+1][$this->offset>>1]->name;
		}
		if($this->round > 0)
		{
			$request->set($this->prefix.'round', $this->round - 1);
			$request->set($this->prefix.'offset', $this->offset * 2);
			$treeNavigator->arrowPreviousUp->setManialink($request->createLink());
			$treeNavigator->arrowPreviousUpTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns-1][$this->offset<<1]->name.'$000'._(' bracket');
			$request->set($this->prefix.'offset', $this->offset * 2 + 1);
			$treeNavigator->arrowPreviousDown->setManialink($request->createLink());
			$treeNavigator->arrowPreviousDownTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns-1][$this->offset<<1^1]->name.'$000'._(' bracket');
		}
		if($this->offset < $this->maxOffset)
		{
			$request->set($this->prefix.'round', $this->round);
			$request->set($this->prefix.'offset', $this->offset + 1);
			$treeNavigator->arrowDown->setManialink($request->createLink());
			$treeNavigator->arrowDownTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns][$this->offset+1]->name;
		}
		if($this->offset > 0)
		{
			$request->set($this->prefix.'round', $this->round);
			$request->set($this->prefix.'offset', $this->offset - 1);
			$treeNavigator->arrowUp->setManialink($request->createLink());
			$treeNavigator->arrowUpTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns][$this->offset-1]->name;
		}
		$request->restore($this->prefix.'round');
		$request->restore($this->prefix.'offset');
		
		return $treeNavigator;
	}
}

class TreeNavigator extends \ManiaLib\Gui\Elements\Frame
{
	/** @var UIConstructionSimple_Buttons */
	public $arrowNext;
	/** @var UIConstructionSimple_Buttons */
	public $arrowPreviousUp;
	/** @var UIConstructionSimple_Buttons */
	public $arrowPreviousDown;
	/** @var UIConstructionSimple_Buttons */
	public $arrowUp;
	/** @var UIConstructionSimple_Buttons */
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
		if($this->arrowNext->hasLink())
		{
			$this->arrowNext->setId('tree-navigator:next');
			$this->arrowNext->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowNext->getId(), $this->arrowNextTooltip);
		}
		else
			$this->arrowNext->setVisibility(false);
		
		if($this->arrowPreviousUp->hasLink())
		{
			$this->arrowPreviousUp->setId('tree-navigator:previous-up');
			$this->arrowPreviousUp->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowPreviousUp->getId(), $this->arrowPreviousUpTooltip);
		}
		else
			$this->arrowPreviousUp->setVisibility(false);
		
		if($this->arrowPreviousDown->hasLink())
		{
			$this->arrowPreviousDown->setId('tree-navigator:previous-down');
			$this->arrowPreviousDown->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowPreviousDown->getId(), $this->arrowPreviousDownTooltip);
		}
		else
			$this->arrowPreviousDown->setVisibility(false);
		
		if($this->arrowUp->hasLink())
		{
			$this->arrowUp->setId('tree-navigator:up');
			$this->arrowUp->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowUp->getId(), $this->arrowUpTooltip);
		}
		else
			$this->arrowUp->setVisibility(false);
		
		if($this->arrowDown->hasLink())
		{
			$this->arrowDown->setId('tree-navigator:down');
			$this->arrowDown->setScriptEvents();
			\ManiaLib\ManiaScript\UI::tooltip($this->arrowDown->getId(), $this->arrowDownTooltip);
		}
		else
			$this->arrowDown->setVisibility(false);
	}
}

?>
