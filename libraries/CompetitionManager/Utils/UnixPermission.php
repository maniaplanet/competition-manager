<?php
/**
 * @version     $Revision$:
 * @author      $Author$:
 * @date        $Date$:
 */
namespace CompetitionManager\Utils;

class UnixPermission
{
	public static function fix($filename)
	{
		$config = \CompetitionManager\Config::getInstance();
		
		if ($config->filesGroup)
		{
			@chgrp($filename, $config->filesGroup);
		}
		@chmod($filename, 0775);
	}
	
	public static function check($filename)
	{
		
	}
}
?>