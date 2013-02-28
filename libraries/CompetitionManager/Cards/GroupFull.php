<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Cards;

use CompetitionManager\Constants;

class GroupFull extends RankingFull
{
	function __construct()
	{
		parent::__construct(Constants\UI::MEDIUM_WIDTH, Constants\UI::TITLE_HEIGHT-Constants\UI::PIXEL);
	}
}

?>
