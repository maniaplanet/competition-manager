<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Constants;

abstract class Transaction
{
	const REGISTRATION        = 0x01;
	const SPONSOR             = 0x02;
	const REWARD              = 0x80;
	const REFUND              = 0x80;
}

?>
