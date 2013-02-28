<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Cards;

use CompetitionManager\Constants;

class Match extends Ranking
{
	function __construct()
	{
		parent::__construct(Constants\UI::MATCH_WIDTH, -Constants\UI::PIXEL);
	}
	
	function setName($name)
	{
		$this->background->setId($this->getId().':tooltip');
		$this->background->setScriptEvents();
		\ManiaLib\ManiaScript\UI::tooltip($this->background->getId(), $name);
	}
}

?>
