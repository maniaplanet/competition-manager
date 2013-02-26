<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Views\Competition;

use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Layouts;
use CompetitionManager\Constants;
use CompetitionManager\Services\Stages;

class Groups extends \ManiaLib\Application\View
{
	function display()
	{
		$this->renderSubView('_Menu');
		
		if(count($this->response->groups) <= 4)
			$groups = array_chunk($this->response->groups, 2, true);
		else
			$groups = array_chunk($this->response->groups, 3, true);
		
		$stage = $this->response->stage;
		
		$frame = new Frame(240, 0);
		$layout = new Layouts\Column($frame->getSizeX(), $frame->getSizeY());
		$frame->setLayout($layout);
		$frame->setPosition(-80, $frame->getSizeY() / 2, -5);
		$frame->setPosition(40, 0, -5);
		$frame->setAlign('center', 'center');
		$lineHeight = 0;
		$lineFrames = array();
		
		foreach($groups as $groupLine)
		{
			$lineFrames[] = $lineFrame = new Frame(self::getLineWidth(count($groupLine)), 0);
			$lineLayout = new Layouts\Line($lineFrame->getSizeX(), $lineFrame->getSizeY());
			$lineLayout->setMarginWidth(self::getLineMargin(count($groupLine)));
			$lineFrame->setLayout($lineLayout);
			$lineFrame->setHalign('center');
			$lineFrame->setRelativeHalign('center');
			$frame->add($lineFrame);
			
			foreach($groupLine as $group => $participants)
			{
				$card = new \CompetitionManager\Cards\Group();
				$card->setName(sprintf(_('Group %s'), Stages\Groups::getGroupLetter($group)));
				foreach($participants as $participant)
					$card->addParticipant($participant, true, true, false);
				if($stage->state == Constants\State::UNKNOWN)
				{
					$emptyLabels = $stage->getEmptyLabels($group);
					foreach($emptyLabels as $emptyLabel)
						$card->addEmpty($emptyLabel);
				}
				$lineFrame->add($card);
				$lineHeight = max($card->getRealSizeY(), $lineHeight);
			}
		}
		$frame->setSizeY(self::getColumnHeight($lineHeight, count($groups)));
		$layout->setMarginHeight(self::getColumnMargin($lineHeight, count($groups)));
		foreach($lineFrames as $lineFrame)
			$lineFrame->setSizeY($lineHeight);
		
		$frame->save();
	}
	
	private static function getColumnHeight($lineHeight, $nbLines)
	{
		return ($lineHeight+self::getColumnMargin($lineHeight, $nbLines))*$nbLines-self::getColumnMargin($lineHeight, $nbLines);
	}
	
	private static function getColumnMargin($lineHeight, $nbLines)
	{
		return (180-$lineHeight*$nbLines)/($nbLines+4);
	}
	
	private static function getLineWidth($nbElements)
	{
		return (Constants\UI::GROUP_WIDTH+self::getLineMargin($nbElements))*$nbElements-self::getLineMargin($nbElements);
	}
	
	private static function getLineMargin($nbElements)
	{
		static $marginWidth = array(1 => 0, 40, 15);
		return (240-$nbElements*Constants\UI::GROUP_WIDTH)/($nbElements+1);
	}
}

?>
