<?php
/**
 * ManiaLib - Lightweight PHP framework for Manialinks
 * 
 * @see         http://code.google.com/p/manialib/
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 7407 $:
 * @author      $Author: maxime $:
 * @date        $Date: 2012-06-07 19:05:16 +0200 (jeu., 07 juin 2012) $:
 */
if(!defined('MANIALIB_APP_PATH'))
{
	define('MANIALIB_APP_PATH', __DIR__.'/../');
}

spl_autoload_register(function ($className)
	{
		$className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
		$path = __DIR__.DIRECTORY_SEPARATOR.$className.'.php';
		if(file_exists($path))
		{
			require_once $path;
		}
	});
?>