<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager;

class Event extends \ManiaLive\Event\Event
{
	const ON_RULES_END_ROUND = 0x1;
	const ON_RULES_END_MAP   = 0x2;
	const ON_RULES_END_MATCH = 0x4;
	
	public function fireDo($listener)
	{
		switch($this->onWhat)
		{
			case self::ON_RULES_END_ROUND: $listener->onRulesEndRound();
			case self::ON_RULES_END_MAP: $listener->onRulesEndMap();
			case self::ON_RULES_END_MATCH: $listener->onRulesEndMatch();
		}
	}
}

?>
