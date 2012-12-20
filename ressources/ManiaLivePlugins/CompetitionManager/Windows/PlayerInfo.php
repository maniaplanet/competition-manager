<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 7620 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-06-29 16:35:07 +0200 (ven., 29 juin 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Label;

// FIXME
class PlayerInfo extends \ManiaLive\Gui\Window
{
	private $rank;
	private $score;
	
	protected function onConstruct()
	{
		$this->setSize(30, 10);
		
		$ui = new Bgs1InRace(30, 10);
		$ui->setSubStyle(Bgs1InRace::BgList);
		$this->addComponent($ui);
		
		$ui = new Bgs1InRace(12, 9);
		$ui->setSubStyle(Bgs1InRace::BgList);
		$ui->setPosition(.5, -.5);
		$this->addComponent($ui);
		
		$this->rank = new Label(11, 9);
		$this->rank->setStyle(Label::TextRankingsBig);
		$this->rank->setAlign('center', 'center2');
		$this->rank->setPosition(6.5, -5);
		$this->addComponent($this->rank);
		
		$this->score = new Label(17, 9);
		$this->score->setStyle(Label::TextStaticSmall);
		$this->score->setAlign('center', 'center2');
		$this->score->setPosition(21, -5);
		$this->addComponent($this->score);
	}
	
	function setRank($rank)
	{
		$this->rank->setText($rank);
	}
	
	function setScore($score)
	{
		$this->score->setText($score);
	}
}

?>
