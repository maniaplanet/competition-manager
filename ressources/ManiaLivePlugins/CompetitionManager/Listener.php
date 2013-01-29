<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager;

interface Listener extends \ManiaLive\Event\Listener
{
	function onRulesEndRound();
	function onRulesEndMap();
	function onRulesEndMatch();
}

?>
