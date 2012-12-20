<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Views\Home;

use CompetitionManager\CardsOld\Competition;
use CompetitionManager\Constants\State;
use CompetitionManager\Services\WebServicesProxy;

class Index extends \ManiaLib\Application\View
{
	function display()
	{
		$ui = new \ManiaLib\Gui\Elements\Quad(80, 80);
		$ui->setImage('logo.png');
		$ui->setPosition(0, 55);
		$ui->setAlign('center', 'center');
		$ui->save();
		
		\ManiaLib\Gui\Manialink::beginFrame(-75, 30, 0, null, new \ManiaLib\Gui\Layouts\Column());
		foreach($this->response->competitions as $competition)
		{
			$ui = new Competition();
			$ui->setName($competition->name);
			$ui->setTitle(WebServicesProxy::getTitleName($competition->title));
			if(reset($competition->stages)->maxSlots)
				$ui->setNbParticipants($competition->nbParticipants.'/'.reset($competition->stages)->maxSlots, $competition->isTeam);
			else
				$ui->setNbParticipants($competition->nbParticipants, $competition->isTeam);
			if($competition->isScheduled())
			{
				$stage = reset($competition->stages);
				if($stage instanceof \CompetitionManager\Services\Stages\Registrations)
					$stage = $competition->stages[$stage->nextId];
				$ui->setStart($stage->startTime->format('j F Y \a\t G:i T'));
			}
			else
				$ui->setPickUp();
			if($competition->state <= State::READY)
			{
				$ui->setUpcoming();
			}
			else if($competition->state >= State::OVER)
				$ui->setFinished();
			$this->request->set('c', $competition->competitionId);
			$ui->setManialink($this->request->createLinkArgList('/competition', 'c', 'external'));
			$ui->save();
		}
		$this->request->restore('c');
		\ManiaLib\Gui\Manialink::endFrame();
		
		$this->response->multipage->pageNavigator->setSize(9);
		$this->response->multipage->pageNavigator->setPosition(0, -80);
		$this->response->multipage->pageNavigator->showLast(true);
		$this->response->multipage->savePageNavigator();
	}
}

?>
