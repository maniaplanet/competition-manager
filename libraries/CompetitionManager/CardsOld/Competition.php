<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9011 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-11-29 18:57:57 +0100 (jeu., 29 nov. 2012) $:
 */

namespace CompetitionManager\CardsOld;

use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Label;

class Competition extends Bgs1
{
	private $name;
	private $title;
	private $start;
	private $nbParticipants;
	private $participants;
	
	function __construct($sizeX=150, $sizeY=20)
	{
		parent::__construct($sizeX, $sizeY);
		$this->subStyle = Bgs1::BgCardBuddy;
		
		$this->name = new Label(90, 0);
		$this->name->setStyle(Label::TextRankingsBig);
		$this->name->setPosition(3, -2);

		$this->title = new Label(90, 0);
		$this->title->setStyle(Label::TextValueSmall);
		$this->title->setValign('bottom');
		$this->title->setPosX(3);

		$this->start = new Label(90, 0);
		$this->start->setStyle(Label::TextValueSmall);
		$this->start->setValign('bottom');
		$this->start->setPosX(3);

		$this->nbParticipants = new Label(20, 0);
		$this->nbParticipants->setStyle(Label::TextRaceChrono);
		$this->nbParticipants->setHalign('center');
		$this->nbParticipants->setPosY(-1);
		$this->nbParticipants->setManialink('');
		
		$this->participants = new Label(50, 0);
		$this->participants->setStyle(Label::TextRaceChrono);
		$this->participants->setScale(.5);
		$this->participants->setAlign('center', 'top');
		$this->participants->setManialink('');
		
		$this->addCardElement($this->name);
		$this->addCardElement($this->title);
		$this->addCardElement($this->start);
		$this->addCardElement($this->nbParticipants);
		$this->addCardElement($this->participants);
	}
	
	function setUpcoming()
	{
		$this->subStyle = Bgs1::BgCardChallenge;
	}
	
	function setFinished()
	{
		$this->subStyle = Bgs1::BgCardFolder;
	}
	
	function setWithCurrentPlayer()
	{
		$this->subStyle = Bgs1::BgCardZone;
	}
	
	function setName($name)
	{
		$this->name->setText('$000'.$name);
	}
	
	function setTitle($title)
	{
		$this->title->setText('$444'._('Title').': $111'.$title);
	}
	
	function setStart($start)
	{
		$this->start->setText('$444'.sprintf(_('Starts on %s'), '$111'.$start));
	}
	
	function setPickUp()
	{
		$this->start->setText('$111Pick-up');
	}
	
	function setNbParticipants($nbParticipants, $areTeams=false)
	{
		$this->nbParticipants->setText('$666$s'.$nbParticipants);
		if($areTeams)
			$this->participants->setText('$666$s'._('teams'));
		else
			$this->participants->setText('$666$s'._('players'));
	}
	
	function preFilter()
	{
		$this->name->setSizeX($this->sizeX - 30);
		$this->title->setPosY(6.5 - $this->sizeY);
		$this->start->setPosY(1.5 - $this->sizeY);
		$this->nbParticipants->setPosition($this->sizeX - 20, -9);
		$this->participants->setPosition($this->sizeX - 20, -3);
	}
}

?>
