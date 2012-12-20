<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class AsynchronousTMTimeAttack extends TMTimeAttack
{
	public $maxTries = 0;
	
	function getNeededEvents()
	{
		return Event::ON_PLAYER_FINISH | Event::ON_END_MATCH;
	}
}

?>
