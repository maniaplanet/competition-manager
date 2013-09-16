<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8680 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-31 18:11:05 +0100 (mer., 31 oct. 2012) $:
 */

namespace CompetitionManager\WebUI;

abstract class JS
{
	static function jQueryMobile()
	{
		$mediaUrl = \ManiaLib\Application\Config::getInstance()->getMediaURL();
		self::script($mediaUrl.'jquery/jquery-1.8.2.min.js');
		self::script($mediaUrl.'jquery/jquery.mobile-1.2.1.min.js');
		self::script($mediaUrl.'jquery/jquery.mousewheel.min.js');
		self::script($mediaUrl.'js/jqm-utils.js');
	}

	static function import($script)
	{
		$mediaUrl = \ManiaLib\Application\Config::getInstance()->getMediaURL();
		self::script($mediaUrl.'js/'.$script.'.js');
	}

	static function script($url)
	{
		printf('<script type="text/javascript" src="%s"></script>', htmlentities($url));
	}

}

?>