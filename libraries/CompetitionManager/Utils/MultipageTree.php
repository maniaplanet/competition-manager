<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9051 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-04 18:12:08 +0100 (mar., 04 déc. 2012) $:
 */

namespace CompetitionManager\Utils;

class MultipageTree
{
	private $tree;
	private $columns;
	private $round;
	private $offset;
	private $maxOffset;
	private $maxRound;
	
	function __construct($tree, $columns)
	{
		$this->tree = $tree;
		$this->columns = $columns-1;
		
		$request = \ManiaLib\Application\Request::getInstance();
		$this->maxRound = count($this->tree) - $this->columns - 1;
		$this->round = $request->get('round', 0);
		if($this->round < 0)
			$this->round = 0;
		else if($this->round > $this->maxRound)
			$this->round = $this->maxRound;
		
		$this->maxOffset = (count($this->tree[$this->round]) >> $this->columns) - 1;
		$this->offset = $request->get('offset', 0);
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
		$treeNavigator = new \CompetitionManager\Cards\TreeNavigator();
		
		$request = \ManiaLib\Application\Request::getInstance();
		if($this->round < $this->maxRound)
		{
			$request->set('round', $this->round + 1);
			$request->set('offset', $this->offset >> 1);
			$treeNavigator->arrowNext->setManialink($request->createLink());
			$treeNavigator->arrowNextTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns+1][$this->offset>>1]->name;
		}
		if($this->round > 0)
		{
			$request->set('round', $this->round - 1);
			$request->set('offset', $this->offset * 2);
			$treeNavigator->arrowPreviousUp->setManialink($request->createLink());
			$treeNavigator->arrowPreviousUpTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns-1][$this->offset<<1]->name.'$000'._(' bracket');
			$request->set('offset', $this->offset * 2 + 1);
			$treeNavigator->arrowPreviousDown->setManialink($request->createLink());
			$treeNavigator->arrowPreviousDownTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns-1][$this->offset<<1^1]->name.'$000'._(' bracket');
		}
		if($this->offset < $this->maxOffset)
		{
			$request->set('round', $this->round);
			$request->set('offset', $this->offset + 1);
			$treeNavigator->arrowDown->setManialink($request->createLink());
			$treeNavigator->arrowDownTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns][$this->offset+1]->name;
		}
		if($this->offset > 0)
		{
			$request->set('round', $this->round);
			$request->set('offset', $this->offset - 1);
			$treeNavigator->arrowUp->setManialink($request->createLink());
			$treeNavigator->arrowUpTooltip = '$<$000'._('To ').'$>'.$this->tree[$this->round+$this->columns][$this->offset-1]->name;
		}
		$request->set('round', $this->round);
		$request->set('offset', $this->offset);
		
		return $treeNavigator;
	}
}

?>
