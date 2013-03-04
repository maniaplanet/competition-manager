<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Views\Competition;

use ManiaLib\Gui\Elements\Button;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Spacer;
use CompetitionManager\CardsOld\Map;

class OpenStage extends \ManiaLib\Application\View
{
	function display()
	{
		$this->renderSubView('_Menu');
		
		$nbMaps = count($this->response->stage->matches);
		if($nbMaps > 1)
		{
			$layout = new \ManiaLib\Gui\Layouts\Column();
			$layout->setMarginHeight(1);
			\ManiaLib\Gui\Manialink::beginFrame(-42.5, 75, -5, null, $layout);
			{
				// Global icon
				$ui = new Button();
				$ui->setHalign('center');
				$ui->setText('Global');
				$this->request->delete('map');
				$ui->setManialink($this->request->createLinkArgList('', 'offset', 'map', 'external'));
				$this->request->restore('map');
				$ui->save();

				// Arrow up
				if($nbMaps > 5)
				{
					if($this->response->matchOffset > 0)
					{
						$ui = new Icons64x64_1(10);
						$ui->setHalign('center');
						$ui->setSubStyle(Icons64x64_1::ShowUp2);
						$this->request->set('offset', $this->response->matchOffset - 1);
						$ui->setManialink($this->request->createLinkArgList('', 'offset', 'map', 'page', 'external'));
						$this->request->restore('offset');
						$ui->save();
					}
					else
					{
						$ui = new Spacer(10, 10);
						$ui->save();
					}
					$matches = array_slice($this->response->stage->matches, $this->response->matchOffset, 5);
				}
				else
				{
					$ui = new Spacer(10, 10);
					$ui->save();
					$matches = $this->response->stage->matches;
				}
				$ui = new Spacer(10, -4);
				$ui->save();

				// Maps
				$mapNumber = $this->response->matchOffset + 1;
				foreach($matches as $match)
				{
					$match->fetchMaps();
					$map = reset($match->maps);
					$ui = new Map();
					$ui->setHalign('center');
					$ui->setName(\ManiaLib\Utils\Formatting::stripStyles($map->name));
					$ui->setScreenshot('maps/'.$map->mapUid.'.jpg');
					$this->request->set('map', $mapNumber++);
					$ui->setManialink($this->request->createLinkArgList('', 'offset', 'map', 'external'));
					$ui->save();
				}
				$this->request->restore('map');

				// Arrow down
				if($nbMaps > 5 && $this->response->matchOffset + 5 < $nbMaps)
				{
					$ui = new Spacer(10, -4);
					$ui->save();
					$ui = new Icons64x64_1(10);
					$ui->setHalign('center');
					$ui->setSubStyle(Icons64x64_1::ShowDown2);
					$this->request->set('offset', $this->response->matchOffset + 1);
					$ui->setManialink($this->request->createLinkArgList('', 'offset', 'map', 'page', 'external'));
					$this->request->restore('offset');
					$ui->save();
				}
			}
			\ManiaLib\Gui\Manialink::endFrame();
			
			$ui = $this->response->rankingCard;
			$ui->computeSize();
			$ui->setPosition(-15, $ui->getSizeY() / 2, -5);
			$ui->save();
		}
		else
		{
			$ui = $this->response->rankingCard;
			$ui->setAlign('center', 'center');
			$ui->setPosition(40);
			$ui->save();
		}
	}
}

?>
