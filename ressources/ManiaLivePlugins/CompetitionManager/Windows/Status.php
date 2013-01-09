<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 7620 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-06-29 16:35:07 +0200 (ven., 29 juin 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLivePlugins\CompetitionManager\Controls\HighlightedLabel;
use ManiaLivePlugins\CompetitionManager\Constants;

class Status extends \ManiaLive\Gui\Window
{
	/** @var HighlightedLabel */
	private $status;
	
	protected function onConstruct()
	{
		$this->setSize(35, 10);
		$this->setHalign('right');
		$this->setPosition(160, -(55+Constants\UI::PIXEL));
		
		$this->status = new HighlightedLabel(35, 10);
		$this->status->label->setHalign('center');
		$this->status->label->setPosX(17.5);
		$this->status->label->setTextSize(2);
		$this->status->setLabelMargin(2);
		$this->addComponent($this->status);
	}
	
	function set($status, $color)
	{
		$this->status->highlight->setBgcolor($color);
		$this->status->label->setText($status);
	}
}

?>
