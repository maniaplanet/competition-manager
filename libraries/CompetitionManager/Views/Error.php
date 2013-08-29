<?php
/**
 * @version     $Revision$:
 * @author      $Author$:
 * @date        $Date$:
 */
namespace CompetitionManager\Views;

use ManiaLib\Gui\Manialink;
use ManiaLib\ManiaScript\Main;
use ManiaLib\ManiaScript\Tools;

class Error extends \ManiaLib\Application\View
{

	function display()
	{
		Manialink::load();
		Main::begin();
		Manialink::appendScript('log("'.Tools::escapeString($this->response->message).'");');
		Main::end();
		Manialink::render();
	}
}
?>