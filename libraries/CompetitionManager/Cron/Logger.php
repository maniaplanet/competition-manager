<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Cron;

class Logger
{
	private static $loaded = false;
	private static $path;

	static function log($message, $cron)
	{
		if(self::load())
		{
			$message = date('c').'  '.print_r($message, true).PHP_EOL;
			$filename = self::$path.'cron-'.$cron.'.log';
			file_put_contents($filename, $message, FILE_APPEND);
		}
	}
	
	static protected function load()
	{
		if(!self::$loaded)
		{
			$config = \ManiaLib\Utils\LoggerConfig::getInstance();
			if(file_exists($path = $config->path))
			{
				self::$path = $path;
				self::$loaded = true;
			}
		}
		return !empty(self::$path);
	}
}

?>
