<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Views;

class Footer extends \ManiaLib\Application\View
{
	function display()
	{
		\ManiaLib\ManiaScript\Main::loop();
		\ManiaLib\ManiaScript\Main::end();
		\ManiaLib\Gui\Manialink::render();
	}
}
?>
