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
	
	/** @var HighlightedLabel  */
	private $help;
	
	protected function onConstruct()
	{
		$this->setSize(40, 10);
		$this->setHalign('right');
		$this->setPosition(160, -(55+Constants\UI::PIXEL));
		
		$this->status = new HighlightedLabel(count(CountDown::GetAll()) != 0 ? 40 : 60, 10);
		$this->status->label->setHalign('center');
		$this->status->label->setPosX($this->status->getSizeX()/2);
		$this->status->label->setTextSize(2);
		$this->status->setLabelMargin(2);
		$this->addComponent($this->status);
		
		$this->help = new HighlightedLabel(60, 5);
		$this->help->setHalign('right');
		$this->help->label->setHalign('right');
		$this->help->label->setPosX(59);
		$this->help->label->setTextSize(3);
		$this->help->setLabelMargin(1);
		$this->addComponent($this->help);
	}
	
	function set($status, $color, $help = null)
	{
		$this->status->highlight->setBgcolor($color);
		$this->status->label->setText($status);
		if ($help)
		{
			$this->help->label->setText($help);
		}
	}
}

?>
