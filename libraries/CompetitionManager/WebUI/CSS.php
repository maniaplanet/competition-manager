<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8510 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:40:34 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\WebUI;

abstract class CSS
{
	static function jQueryMobile()
	{
		$mediaUrl = \ManiaLib\Application\Config::getInstance()->getMediaURL();
		self::stylesheet($mediaUrl.'jquery/jquery.mobile-1.2.1.min.css');
		self::stylesheet($mediaUrl.'css/jqm-utils.css');
	}
	
	static function import($stylesheet)
	{
		$mediaUrl = \ManiaLib\Application\Config::getInstance()->getMediaURL();
		self::stylesheet($mediaUrl.'css/'.$stylesheet.'.css');
	}

	static function stylesheet($url)
	{
		printf('<link rel="stylesheet" href="%s" type="text/css" media="all" />', htmlentities($url));
	}
}

?>