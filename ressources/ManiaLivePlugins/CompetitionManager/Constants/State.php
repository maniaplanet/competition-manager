<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8504 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 14:38:50 +0200 (lun., 15 oct. 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Constants;

abstract class State
{
	const UNKNOWN   = 0;
	const READY     = 1;
	const STARTED   = 2;
	const OVER      = 3;
	const ARCHIVED  = 4;
	const CANCELLED = 5;
}

?>
