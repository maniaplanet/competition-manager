<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Views\Competition;

use ManiaLib\Application\Dispatcher;
use ManiaLib\Gui\Cards\Navigation\Menu;
use ManiaLib\Gui\Elements\Icon;
use ManiaLib\Gui\Elements\Icons128x32_1;
use CompetitionManager\Constants\State;
use CompetitionManager\Services\Stages;

class _Menu extends \ManiaLib\Application\View
{
	function display()
	{
		$competition = $this->response->competition;
		$currentAction = Dispatcher::getInstance()->getAction('index');
		
		$menu = new Menu();
		$menu->title->setText('$<'.$competition->name.'$>');
		if($competition->state == State::CANCELLED)
			$menu->subTitle->setText('Cancelled...');
		else if(reset($competition->stages)->state < State::STARTED)
			$menu->subTitle->setText('Upcoming');
		else if($competition->state > State::STARTED)
			$menu->subTitle->setText('Over, results available!');
		else if(($current = $competition->getCurrentStage()))
			$menu->subTitle->setText('Currently: '.$current->getName());
		$menu->logo->setStyle(Icon::Icons128x32_1);
		$menu->logo->setSubStyle(Icons128x32_1::RT_Cup);
		
		$menu->addItem();
		$menu->lastItem->icon->setVisibility(false);
		$menu->lastItem->text->setText('Overview');
		$menu->lastItem->setManialink($this->request->createLinkArgList('..', 'c', 'external'));
		if($currentAction == 'index')
			$menu->lastItem->setSelected();
		
		foreach($competition->stages as $stage)
		{
			if($stage instanceof Stages\Registrations && $stage->state >= State::OVER)
				continue;
			else if($stage instanceof Stages\Lobby && $stage->state >= State::OVER)
			{
				if(!$this->response->userParticipation || $competition->state >= State::OVER)
					continue;
				
				$menu->addItem(Menu::BUTTONS_BOTTOM);
				$menu->lastItem->icon->setVisibility(false);
				$stage->fetchMatches();
				$stage->matches[0]->fetchServer();
				if($stage->matches[0]->server && $stage->matches[0]->server->isReady())
				{
					$menu->lastItem->text->setText(_('Join lobby'));
					$menu->lastItem->setManialink($stage->matches[0]->server->getLink('qjoin'));
				}
				else
					$menu->lastItem->text->setText('$888$i'._('Lobby down...'));
			}
			else
			{
				$menu->addItem();
				$menu->lastItem->icon->setVisibility(false);
				if($this->response->competition->state != State::CANCELLED)
				{
					$this->request->set('s', $stage->stageId);
					$menu->lastItem->text->setText($stage->getName());
					$menu->lastItem->setManialink($this->request->createLinkArgList('../'.$stage->getAction(), 'c', 's', 'external'));
					if($currentAction == $stage->getAction() && $this->request->get('s') == $stage->stageId)
					{
						$menu->lastItem->setSelected();
						if($stage instanceof Stages\EliminationTree && $stage->parameters['withLosersBracket'])
						{
							$menu->addGap(-12);
							$menu->addItem();
							$menu->lastItem->icon->setVisibility(false);
							$this->request->set('bracket', Stages\EliminationTree::WINNERS_BRACKET);
							$menu->lastItem->text->setText(($this->response->bracket == Stages\EliminationTree::WINNERS_BRACKET ? '$g' : '')._('Winners bracket'));
							$menu->lastItem->text->incPosX(6);
							$menu->lastItem->setManialink($this->request->createLinkArgList('../'.$stage->getAction(), 'c', 's', 'bracket', 'external'));
							
							$menu->addGap(-12);
							$menu->addItem();
							$menu->lastItem->icon->setVisibility(false);
							$this->request->set('bracket', Stages\EliminationTree::LOSERS_BRACKET);
							$menu->lastItem->text->setText(($this->response->bracket == Stages\EliminationTree::LOSERS_BRACKET ? '$g' : '')._('Losers bracket'));
							$menu->lastItem->text->incPosX(6);
							$menu->lastItem->setManialink($this->request->createLinkArgList('../'.$stage->getAction(), 'c', 's', 'bracket', 'external'));
							
							if(!$stage->parameters['withSmallFinal'])
							{
								$menu->addGap(-12);
								$menu->addItem();
								$menu->lastItem->icon->setVisibility(false);
								$this->request->set('bracket', Stages\EliminationTree::GRAND_FINAL);
								$menu->lastItem->text->setText(($this->response->bracket == Stages\EliminationTree::GRAND_FINAL ? '$g' : '')._('Grand final'));
								$menu->lastItem->text->incPosX(6);
								$menu->lastItem->setManialink($this->request->createLinkArgList('../'.$stage->getAction(), 'c', 's', 'bracket', 'external'));
							}
						}
						$this->request->restore('bracket');
					}
					$this->request->restore('s');
				}
				else
					$menu->lastItem->text->setText('$888'.$stage->getName());
			}
			
		}
		
		$menu->addItem();
		$menu->lastItem->icon->setVisibility(false);
		if($competition->state == State::ARCHIVED)
		{
			$menu->lastItem->text->setText('Results');
			$menu->lastItem->setManialink($this->request->createLinkArgList('../results', 'c', 'external'));
			if($currentAction == 'results')
				$menu->lastItem->setSelected();
		}
		else
			$menu->lastItem->text->setText('$888Results');
		
		$menu->addItem(Menu::BUTTONS_BOTTOM);
		$menu->lastItem->icon->setVisibility(false);
		$menu->lastItem->text->setText('Rules');
		$menu->lastItem->setManialink($this->request->createLinkArgList('../rules', 'c', 'external'));
		if($currentAction == 'rules')
			$menu->lastItem->setSelected();
		
		if($this->response->external)
			$menu->quitButton->setAction(0);
		else
			$menu->quitButton->setManialink($this->request->createLinkArgList('/'));
		
		$menu->save();
	}
}

?>
