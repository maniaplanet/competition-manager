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
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Layouts;
use CompetitionManager\Constants;
use CompetitionManager\Cards;

class Championship extends \ManiaLib\Application\View
{
	function display()
	{
		$this->renderSubView('_Menu');
		$stage = $this->response->stage;
		
		$ui = $this->response->rankingCard;
		$ui->setPosition(-50, 70, -5);
		$ui->save();
		
		$layout = new Layouts\Column(Constants\UI::MATCH_WIDTH);
		$layout->setMarginHeight(2.5);
		$frame = new Frame(Constants\UI::GROUP_WIDTH);
		$frame->setLayout($layout);
		$frame->setHalign('center');
		$frame->setPosition(130, 70, -5);
		
		if($this->response->round !== null)
		{
			$ui = new Championship_RoundPager();
			$ui->setHalign('center');
			$ui->setLabel(sprintf(_('Round #%d'), $this->response->round+1));
			if($this->response->round > 0)
			{
				$this->request->set('round', $this->response->round-1);
				$ui->setPreviousLink($this->request->createLink());
			}
			if($this->response->round < $this->response->maxRound)
			{
				$this->request->set('round', $this->response->round+1);
				$ui->setNextLink($this->request->createLink());
			}
			$this->request->restore('round');
			$frame->add($ui);
			$frame->add(new \ManiaLib\Gui\Elements\Spacer(0,0));
		}
		
		foreach($this->response->matches as $offset => $match)
		{
			$card = new Cards\Match();
			$card->setHalign('center');
			if($stage->parameters['isFreeForAll'])
				$card->setName($match->name);
			else
				$card->setName(sprintf(_('Match #%d'), $offset+1));
			if($stage->state == Constants\State::UNKNOWN)
			{
				$emptyLabels = call_user_func_array(array($stage, 'getEmptyLabels'), $stage->findMatch($match->matchId));
				foreach($emptyLabels as $emptyLabel)
					$card->addEmpty($emptyLabel);
			}
			else
			{
				$match->fetchParticipants();
				foreach($match->participants as $participant)
					$card->addParticipant($participant, false, $match->state >= Constants\State::STARTED && $participant->score->isVisible(), false);
			}

			$this->request->set('m', $match->matchId);
			$card->setManialink($this->request->createLink());
			$frame->add($card);
		}
		$this->request->restore('m');
		
		$frame->save();
	}
}

class Championship_RoundPager extends Cards\Shadowed
{
	/** @var Cards\HighlightedLabel */
	private $label;
	/** @var Icons64x64_1 */
	private $arrowPrevious;
	/** @var Icons64x64_1 */
	private $arrowNext;
	
	function __construct()
	{
		parent::__construct(Constants\UI::GROUP_WIDTH, Constants\UI::TITLE_HEIGHT*.75);
		
		$this->label = new Cards\HighlightedLabel($this->sizeX, $this->sizeY);
		$this->label->highlight->setBgcolor('ff09');
		$this->label->label->setRelativeHalign('center');
		$this->label->label->setHalign('center');
		$this->add($this->label);
		
		$this->arrowPrevious = new Icons64x64_1(Constants\UI::TITLE_HEIGHT*.75);
		$this->arrowPrevious->setSubStyle(Icons64x64_1::ArrowDisabled);
		$this->arrowPrevious->setRelativeAlign('left', 'center');
		$this->arrowPrevious->setAlign('right', 'center');
		$this->add($this->arrowPrevious);
		
		$this->arrowNext = new Icons64x64_1(Constants\UI::TITLE_HEIGHT*.75);
		$this->arrowNext->setSubStyle(Icons64x64_1::ArrowDisabled);
		$this->arrowNext->setRelativeAlign('right', 'center');
		$this->arrowNext->setAlign('left', 'center');
		$this->add($this->arrowNext);
	}
	
	function setLabel($text)
	{
		$this->label->label->setText($text);
	}
	
	function setPreviousLink($manialink)
	{
		$this->arrowPrevious->setManialink($manialink);
		if($manialink)
			$this->arrowPrevious->setSubStyle(Icons64x64_1::ArrowPrev);
	}
	
	function setNextLink($manialink)
	{
		$this->arrowNext->setManialink($manialink);
		if($manialink)
			$this->arrowNext->setSubStyle(Icons64x64_1::ArrowNext);
	}
}

?>
