<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9058 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-05 18:05:59 +0100 (mer., 05 déc. 2012) $:
 */

namespace CompetitionManager\Views\Competition;

use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Layouts;
use CompetitionManager\Constants;
use CompetitionManager\Services\Stages;

class Brackets extends \ManiaLib\Application\View
{
	function display()
	{
		$this->renderSubView('_Menu');
		
		if(count($this->response->stage->matches[$this->response->bracket]) == 1)
			return $this->displaySingle();
		else
			$this->displayBracket();
		
		if($this->response->matchCard)
		{
			$ui = new Bgs1(240, 180);
			$ui->setSubStyle(Bgs1::BgDialogBlur);
			$ui->setAlign('center', 'center');
			$ui->setPosition(40, 0, 1);
			$ui->setScriptEvents();
			$ui->save();
			
			$this->displaySingle();
		}
	}
	
	private function displaySingle()
	{
		$ui = $this->response->matchCard;
		$ui->setAlign('center', 'center');
		$ui->setPosition(40, 0, 2);
		$ui->save();
	}
	
	private function displayBracket()
	{
		$nbCols = count($this->response->matches);
		$additionnalSpace = ((4 - $nbCols) * 55) / $nbCols;
		
		$step = -178 / count($this->response->matches[0]);
		$yIndexes = range($step / 2, -178, $step);
		
		$layout = new Layouts\Line();
		$layout->setMarginWidth(10+$additionnalSpace);
		$layout->setBorder(15+$additionnalSpace/2, 1);
		$bracketFrame = new Frame(240);
		$bracketFrame->setLayout($layout);
		$bracketFrame->setPosition(-80, 90, -5);
		
		foreach($this->response->matches as $round => $roundMatches)
		{
			$layout = new Layouts\Spacer(Constants\UI::MATCH_WIDTH);
			$roundFrame = new Frame(Constants\UI::MATCH_WIDTH, 170);
			$roundFrame->setLayout($layout);
			$bracketFrame->add($roundFrame);
			$cards = array();
			
			foreach($roundMatches as $offset => $match)
			{
				$match->fetchParticipants();
				$roundFrame->add($cards[] = $this->createMatchCard($match, $round, $offset, $yIndexes[$offset]));
			}
			
			$yIndexes = $this->prepareNextRound($round, $yIndexes);
		}
		
		if($this->response->multipageTree)
		{
			$card = reset($cards);
			$treeNavigator = $this->response->multipageTree->createNavigator();
			$treeNavigator->setSize($card->getSizeX(), $card->getSizeY());
			$treeNavigator->setPosition($card->getPosX(), $card->getPosY());
			$roundFrame->add($treeNavigator);
		}
		
		$bracketFrame->save();
	}
	
	private function createMatchCard($match, $round, $offset, $posY)
	{
		$card = new \CompetitionManager\Cards\Match();
		$card->setPosY($posY);
		$card->setValign('center');
		foreach($match->participants as $participant)
			$card->addParticipant($participant, false, $match->state >= Constants\State::STARTED && $participant->hasScore(), false);
		
		if($match->state > Constants\State::UNKNOWN)
			$emptyLabels = 'BYE';
		else
			$emptyLabels = $this->response->stage->getEmptyLabels($this->response->bracket, $this->response->baseRound+$round, $this->response->baseOffset+$offset);
		for($i=count($match->participants); $i<$this->response->stage->parameters['slotsPerMatch']; ++$i)
		{
			if($emptyLabels)
			{
				if(is_array($emptyLabels))
					$card->addEmpty(array_shift($emptyLabels));
				else
					$card->addEmpty($emptyLabels);
			}
			else
				$card->addEmpty('');
		}

		$card->setName($match->name);
		$this->request->set('m', $match->matchId);
		$card->setManialink($this->request->createLink());
		$this->request->restore('m');
		
		return $card;
	}
	
	private function prepareNextRound($round, $yIndexes)
	{
		if($this->response->bracket == Stages\Brackets::WINNERS_BRACKET || ($this->response->baseRound+$round) % 2)
		{
			$this->response->baseOffset >>= 1;

			$nextYIndexes = array();
			for($i = 0; $i < count($yIndexes)>>1; ++$i)
				$nextYIndexes[] = ($yIndexes[2*$i] + $yIndexes[2*$i+1]) / 2;
			$nextYIndexes[] = end($nextYIndexes)*1.6;
			return $nextYIndexes;
		}
		else
			return $yIndexes;
	}
}

?>
