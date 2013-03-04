<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9012 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-11-30 10:41:17 +0100 (ven., 30 nov. 2012) $:
 */

namespace CompetitionManager\Views\Competition;

class Results extends \ManiaLib\Application\View
{
	function display()
	{
		$this->renderSubView('_Menu');
		
		$ui = $this->response->rankingCard;
		$ui->setAlign('center', 'center');
		$ui->setPosition(40);
		$ui->save();
	}
}

?>
