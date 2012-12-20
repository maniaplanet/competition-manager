<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Views\Competition;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Quad;
use CompetitionManager\Cards;
use CompetitionManager\Constants;
use CompetitionManager\Services\Stages;

class Rules extends \ManiaLib\Application\View
{
	function display()
	{
		$this->renderSubView('_Menu');
		
		$last = null;
		
		$ui = new Cards\Accordion(Constants\UI::STANDARD_WIDTH, Constants\UI::ACCORDION_HEIGHT);
		$ui->setAlign('center', 'center');
		$ui->setPosX(40);
		
		$this->request->delete('details');
		$ui->addTab(_('General information'), $this->request->createLink());
		if(!$this->response->details)
		{
			$bulletList = $ui->selectTab();
			$infoList = $this->response->competition->getInfo();
		}
		
		foreach($this->response->competition->stages as $stage)
		{
			if($stage instanceof Stages\Registrations || $stage instanceof Stages\Lobby)
				continue;
			if($this->response->details == $last)
			{
				if($stage === $this->response->competition->getFirstPlayStage())
					$infoList[] = $stage->maxSlots ? sprintf(_('Maximum %d participants'), $stage->maxSlots) : _('Unlimited number of participants');
				else
					$infoList[] = sprintf(_('First %d qualify for next stage'), $stage->maxSlots);
			}
			
			$this->request->set('details', $stage->stageId);
			$ui->addTab($stage->getName(), $this->request->createLink());
			if($this->response->details == $stage->stageId)
			{
				$bulletList = $ui->selectTab();
				$infoList = $stage->getInfo();
			}
			
			$last = $stage->stageId;
		}
		$this->request->restore('details');
		
		$this->addBullets($bulletList, $infoList);
		$ui->save();
	}
	
	private function addBullets($bulletList, $infoList)
	{
		foreach($infoList as $info)
		{
			if(is_array($info))
			{
				$bulletList->beginLevel();
				$this->addBullets($bulletList, $info);
				$bulletList->endLevel();
			}
			else
				$bulletList->addBullet($info);
		}
	}
}

?>
