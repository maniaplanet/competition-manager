<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\CardsOld;

use ManiaLib\Gui\Elements\Frame;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Label;

class Podium extends Frame
{
	/** @var Icons64x64_1 */
	private $rank;
	/** @var Frame */
	private $participants;
	
	function __construct()
	{
		parent::__construct(120);
		$this->setLayout(new \ManiaLib\Gui\Layouts\Spacer());
		
		$this->rank = new Icons64x64_1(9);
		$this->add($this->rank);
		
		$this->participants = new Frame();
		$layout = new \ManiaLib\Gui\Layouts\Column();
		$layout->setBorderHeight(2);
		$this->participants->setLayout($layout);
		$this->participants->setPosX(11);
		$this->add($this->participants);
	}
	
	function setRank($rank)
	{
		if($rank == 1)
			$this->rank->setSubStyle(Icons64x64_1::First);
		else if($rank == 2)
			$this->rank->setSubStyle(Icons64x64_1::Second);
		else if($rank == 3)
			$this->rank->setSubStyle(Icons64x64_1::Third);
	}
	
	function addParticipant($name)
	{
		$label = new Label(100, 7.5);
		$label->setStyle(Label::TextValueMedium);
		$label->setText($name);
		$this->participants->add($label);
		$this->participants->setSizeY($this->participants->getSizeY() + 7.5);
		$this->setSizeY($this->sizeY + 7.5);
	}
}

?>
