<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Utils;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Label;

class MultipageList
{
	private $size;
	private $perPage;
	private $page;
	private $maxPage;
	
	private $prefix;

	function __construct($size, $perPage, $prefix='')
	{
		$this->size = $size;
		$this->perPage = $perPage;
		$this->prefix = $prefix ? $prefix.'-' : '';
		
		$request = \ManiaLib\Application\Request::getInstance();
		$this->maxPage = ceil($this->size / $this->perPage);
		$this->page = (int) $request->get($this->prefix.'page', 1);
		if($this->page < 1)
			$this->page = 1;
		else if($this->page > $this->maxPage)
			$this->page = $this->maxPage;
	}

	/**
	 * @return int[] offset, length
	 */
	function getLimit()
	{
		$offset = ($this->page - 1) * $this->perPage;
		$length = $this->perPage;
		return array($offset, $length);
	}

	function createNavigator()
	{
		$listNavigator = new ListNavigator();
		$listNavigator->setCurrentPage($this->page);
		$listNavigator->setPageNumber($this->maxPage);
		
		$request = \ManiaLib\Application\Request::getInstance();
		if($listNavigator->isLastShown())
		{
			$request->set($this->prefix.'page', 1);
			$listNavigator->arrowFirst->setManialink($request->createLink());

			$request->set($this->prefix.'page', $this->maxPage);
			$listNavigator->arrowLast->setManialink($request->createLink());
		}

		if($listNavigator->isFastNextShown())
		{
			if($this->page < $this->maxPage)
			{
				$request->set($this->prefix.'page', min($this->page + 5, $this->maxPage));
				$listNavigator->arrowFastNext->setManialink($request->createLink());
			}

			if($this->page > 1)
			{
				$request->set($this->prefix.'page', max($this->page - 5, 1));
				$listNavigator->arrowFastPrev->setManialink($request->createLink());
			}
		}

		if($this->page < $this->maxPage)
		{
			$request->set($this->prefix.'page', $this->page + 1);
			$listNavigator->arrowNext->setManialink($request->createLink());
		}

		if($this->page > 1)
		{
			$request->set($this->prefix.'page', $this->page - 1);
			$listNavigator->arrowPrev->setManialink($request->createLink());
		}
		$request->restore($this->prefix.'page');

		return $listNavigator;
	}
}

class ListNavigator extends \ManiaLib\Gui\Elements\Frame
{
	/** @var Icons64x64_1 */
	public $arrowNext;
	/** @var Icons64x64_1 */
	public $arrowPrev;
	/** @var Icons64x64_1 */
	public $arrowFastNext;
	/** @var Icons64x64_1 */
	public $arrowFastPrev;
	/** @var Icons64x64_1 */
	public $arrowLast;
	/** @var Icons64x64_1 */
	public $arrowFirst;

	/** @var Label */
	private $text;
	/** @var Bgs1InRace */
	private $textBg;

	protected $showLast;
	protected $showFastNext;
	protected $showText;
	protected $pageNumber;
	protected $currentPage;

	function __construct($iconSize = 8)
	{
		parent::__construct();
		$this->setLayout(new \ManiaLib\Gui\Layouts\Spacer());
		$this->setSize(8*$iconSize+2, $iconSize);
		$this->setHalign('center');
		
		$this->arrowNext = new Icons64x64_1($iconSize);
		$this->arrowNext->setSubStyle('ArrowDisabled');
		$this->arrowNext->setRelativeAlign('center', 'center');
		$this->arrowNext->setAlign('left', 'center');
		
		$this->arrowPrev = new Icons64x64_1($iconSize);
		$this->arrowPrev->setSubStyle('ArrowDisabled');
		$this->arrowPrev->setRelativeAlign('center', 'center');
		$this->arrowPrev->setAlign('right', 'center');
		
		$this->arrowFastNext = new Icons64x64_1($iconSize);
		$this->arrowFastNext->setSubStyle('ArrowDisabled');
		$this->arrowFastNext->setRelativeAlign('center', 'center');
		$this->arrowFastNext->setAlign('left', 'center');
		
		$this->arrowFastPrev = new Icons64x64_1($iconSize);
		$this->arrowFastPrev->setSubStyle('ArrowDisabled');
		$this->arrowFastPrev->setRelativeAlign('center', 'center');
		$this->arrowFastPrev->setAlign('right', 'center');
		
		$this->arrowLast = new Icons64x64_1($iconSize);
		$this->arrowLast->setSubStyle('ArrowDisabled');
		$this->arrowLast->setRelativeAlign('center', 'center');
		$this->arrowLast->setAlign('left', 'center');
		
		$this->arrowFirst = new Icons64x64_1($iconSize);
		$this->arrowFirst->setSubStyle('ArrowDisabled');
		$this->arrowFirst->setRelativeAlign('center', 'center');
		$this->arrowFirst->setAlign('right', 'center');
		
		$this->text = new Label(2*$iconSize-2, 0);
		$this->text->setRelativeAlign('center', 'center');
		$this->text->setAlign('center', 'center2');
		$this->text->setPosZ(.1);
		
		$this->textBg = new Bgs1InRace(2*$iconSize, $iconSize-2);
		$this->textBg->setSubStyle(Bgs1InRace::BgPager);
		$this->textBg->setRelativeAlign('center', 'center');
		$this->textBg->setAlign('center', 'center');

		$this->showLast = false;
		$this->showFastNext = false;
		$this->showText = true;

		$this->add($this->textBg);
		$this->add($this->text);
		$this->add($this->arrowNext);
		$this->add($this->arrowPrev);
		$this->add($this->arrowFastNext);
		$this->add($this->arrowFastPrev);
		$this->add($this->arrowLast);
		$this->add($this->arrowFirst);
	}

	function setPageNumber($pageNumber)
	{
		$this->pageNumber = $pageNumber;
	}

	function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage;
	}

	function showLast($show = true)
	{
		$this->showLast = $show;
	}

	function isLastShown()
	{
		return $this->showLast;
	}

	function showFastNext($show = true)
	{
		$this->showFastNext = $show;
	}

	function isFastNextShown()
	{
		return $this->showFastNext;
	}

	function showText($show = true)
	{
		$this->showText = $show;
	}

	function isTextShown()
	{
		return $this->showText;
	}
	
	function preFilter()
	{
		if(!$this->currentPage || !$this->pageNumber)
			$this->showText(false);
		
		$this->textBg->setVisibility($this->showText);
		$this->text->setVisibility($this->showText);
		$this->arrowFastNext->setVisibility($this->showFastNext);
		$this->arrowFastPrev->setVisibility($this->showFastNext);
		$this->arrowLast->setVisibility($this->showLast);
		$this->arrowFirst->setVisibility($this->showLast);
		
		$this->arrowNext->setPosX(($this->text->getSizeX() / 2) + 1);
		$this->arrowPrev->setPosX(-$this->arrowNext->getPosX());
		$this->arrowFastNext->setPosX($this->arrowNext->getPosX() + $this->arrowNext->getSizeX());
		$this->arrowFastPrev->setPosX(-$this->arrowFastNext->getPosX());
		$this->arrowLast->setPosX($this->arrowNext->getPosX() + $this->showFastNext * $this->arrowFastNext->getSizeX() + $this->arrowNext->getSizeX());
		$this->arrowFirst->setPosX(-$this->arrowLast->getPosX());
		
		$this->text->setText('$fff'.$this->currentPage.' / '.$this->pageNumber);
		
		if($this->arrowNext->hasLink())
			$this->arrowNext->setSubStyle(Icons64x64_1::ArrowNext);
		if($this->arrowPrev->hasLink())
			$this->arrowPrev->setSubStyle(Icons64x64_1::ArrowPrev);
		if($this->arrowFastNext->hasLink())
			$this->arrowFastNext->setSubStyle(Icons64x64_1::ArrowFastNext);
		if($this->arrowFastPrev->hasLink())
			$this->arrowFastPrev->setSubStyle(Icons64x64_1::ArrowFastPrev);
		if($this->arrowLast->hasLink())
			$this->arrowLast->setSubStyle(Icons64x64_1::ArrowLast);
		if($this->arrowFirst->hasLink())
			$this->arrowFirst->setSubStyle(Icons64x64_1::ArrowFirst);
	}
}

?>
