<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\WebUI;

abstract class Meta
{
	static function contentType()
	{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	}

	static function robots()
	{
		echo '<meta name="robots" content="index, follow, all" />';
		echo '<meta name="revisit-after" content="5 Days" />';
	}

	static function description($description)
	{
		printf('<meta name="description" content="%s" />', HTML::encode($description));
	}
}

?>