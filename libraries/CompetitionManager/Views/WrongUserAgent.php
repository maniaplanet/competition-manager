<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Views;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;

class WrongUserAgent extends \ManiaLib\Application\View
{
	function display()
	{
		\ManiaLib\Gui\Manialink::load();
		
		$config = \ManiaLib\Application\Config::getInstance();
		
		$ui = new Quad(320, 180);
		$ui->setAlign('center', 'center');
		$ui->setBgcolor('111');
		$ui->save();
		
		$ui = new Label(125);
		$ui->setHalign('center');
		$ui->setPosition(0, 65, 1);
		$ui->setTextSize(4);
		$ui->setTextColor('6cf');
		$ui->setText('$o'.$config->manialink);
		$ui->save();
		
		$ui = new Label(100);
		$ui->setPosition(-62.5, 50, 1);
		$ui->setTextSize(1);
		$ui->setTextColor('fff');
		$ui->setScale(1.25);
		$ui->enableAutonewline();
		$URL = $this->request->createLink();
		$ui->setText('The page your are trying to access is not a Manialink. You cannot view it using the in-game browser.'.PHP_EOL.PHP_EOL.
				'To access it, $l['.$URL.']$6cfclick here$g$l or launch your usual web browser and go to the address $o'.$URL.'$o.');
		$ui->save();
		
		\ManiaLib\Gui\Manialink::render();
	}
}

?>
