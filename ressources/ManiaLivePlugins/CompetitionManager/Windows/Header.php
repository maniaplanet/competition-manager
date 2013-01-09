<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLivePlugins\CompetitionManager\Constants;
use ManiaLivePlugins\CompetitionManager\Controls\HighlightedLabel;

class Header extends \ManiaLive\Gui\Window
{
	/** @var HighlightedLabel */
	private $progress;
	
	protected function onConstruct()
	{
		$this->setSize(55, 5);
		$this->setPosition(160, -50);
		
		$matchObj = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		
		$this->progress = new HighlightedLabel(55, 5);
		$this->progress->setHalign('right');
		$this->progress->highlight->setBgcolorFocus('000a');
		// FIXME for lobby: update label and link to stage
		$this->progress->highlight->setManialink($matchObj->getManialink());
		$this->progress->label->setHalign('right');
		$this->progress->label->setPosX(54);
		$this->progress->label->setTextSize(3);
		$this->progress->label->setText($matchObj->stage->competition->name.' » '.$matchObj->name);
		$this->progress->setLabelMargin(1);
		$this->addComponent($this->progress);
	}
}

?>
