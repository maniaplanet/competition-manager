<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Cards;

use CompetitionManager\Constants;

class Group extends Match
{
	/** @var HighlightedLabel */
	protected $title;
	
	function __construct()
	{
		parent::__construct();
		$this->setSize(Constants\UI::GROUP_WIDTH, Constants\UI::TITLE_HEIGHT/2);
		
		$this->title = new HighlightedLabel($this->sizeX, Constants\UI::TITLE_HEIGHT/2);
		$this->title->highlight->setBgcolor('ff05');
		$this->title->highlight->setBgcolorFocus('ff09');
		$this->title->setLabelMargin(2);
		$this->title->label->setPosX(2);
		$this->content->add($this->title);
		
		$this->background->setId('group:'.uniqid());
		$this->background->setScriptEvents();
		\ManiaLib\ManiaScript\UI::tooltip($this->background->getId(), _('See details'));
	}
	
	function setName($name)
	{
		$this->title->label->setText($name);
	}
}

?>
