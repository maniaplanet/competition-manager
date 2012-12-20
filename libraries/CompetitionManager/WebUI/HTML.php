<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\WebUI;

abstract class HTML
{
	static function encode($html)
	{
		return htmlentities($html, ENT_QUOTES, 'UTF-8');
	}

	static function doctype()
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '.
				'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	}

	static function begin($lang = 'en')
	{
		printf('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="%s">', self::encode($lang));
	}

	static function end()
	{
		echo '</html>';
	}

	static function beginHead()
	{
		echo '<head>';
	}

	static function endHead()
	{
		echo '</head>';
	}

	static function title($title)
	{
		printf('<title>%s</title>', HTML::encode($title));
	}

	static function beginBody()
	{
		echo '<body>';
	}

	static function endBody()
	{
		echo '</body>';
	}
}

?>