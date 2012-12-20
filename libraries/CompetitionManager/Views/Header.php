<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9104 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-13 15:01:15 +0100 (jeu., 13 déc. 2012) $:
 */

namespace CompetitionManager\Views;

use ManiaLib\Gui\Elements\Bgs1;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Manialink;

class Header extends \ManiaLib\Application\View
{
	function display()
	{
		Manialink::load(true, 0, 1, !$this->response->external);

		$ui = new Icons64x64_1(10);
		$ui->setAlign('right', 'bottom');
		$ui->setSubStyle('Refresh');
		$ui->setPosition(160, -90, 15);
		$ui->setManiazone($this->request->createLink());
		$ui->save();
		
		$appConfig = \ManiaLib\Application\Config::getInstance();
		$config = \CompetitionManager\Config::getInstance();
		if($appConfig->manialink)
		{
			\ManiaLib\Gui\Manialink::beginFrame(110, -81);
			$ui = new \ManiaLib\Gui\Elements\IncludeManialink();
			$this->request->set('url', $appConfig->manialink);
			$this->request->set('name', $config->manialinkName);
			$ui->setUrl($this->request->createAbsoluteLinkArgList('http://maniahome.maniaplanet.com/add/', 'url', 'name'));
			$this->request->restore('url');
			$this->request->restore('name');
			$ui->save();
			\ManiaLib\Gui\Manialink::endFrame();
		}
		
		if($this->response->external)
		{
			$ui = new Bgs1(320, 180);
			$ui->setSubStyle(Bgs1::BgDialogBlur);
			$ui->setPosition(-160, 90, -10);
			$ui->save();
		}
		else
		{
			if(!$this->response->background)
				$this->response->background = \ManiaLib\Application\Config::getInstance()->getImagesURL().'background.jpg';
			$ui = new Quad(320, 180);
			$ui->setPosition(-160, 90, -10);
			$ui->setImage($this->response->background, true);
			$ui->save();
		}
		
		$ui = new \ManiaLib\Gui\Elements\IncludeManialink();
		$ui->setUrl('http://files.maniaplanet.com/manialinks/maniascript/manialib2.xml');
		$ui->save();

		\ManiaLib\ManiaScript\Main::begin();
	}
}

?>
