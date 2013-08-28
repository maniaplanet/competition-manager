<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8797 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-11-07 18:05:59 +0100 (mer., 07 nov. 2012) $:
 */

namespace CompetitionManager\Utils;

abstract class Formatting
{
	static function timeFrame($timestamp)
	{
		if($timestamp > time())
		{
			return self::timeIn($timestamp);
		}
		else
		{
			return self::timeAgo($timestamp);
		}
	}

	static function timeAgo($timestamp)
	{
		$timestamp = time() - $timestamp;
		if($timestamp >= 31536000)
		{
			$value = floor($timestamp / 31536000);
			return sprintf(ngettext('%d year ago', '%d years ago', $value), $value);
		}
		elseif($timestamp >= 2592000)
		{
			$value = floor($timestamp / 2592000);
			return sprintf(ngettext('%d month ago', '%d months ago', $value), $value);
		}
		elseif($timestamp >= 604800)
		{
			$value = floor($timestamp / 604800);
			return sprintf(ngettext('%d week ago', '%d weeks ago', $value), $value);
		}
		elseif($timestamp >= 86400)
		{
			$value = floor($timestamp / 86400);
			return sprintf(ngettext('%d day ago', '%d days ago', $value), $value);
		}
		elseif($timestamp >= 3600)
		{
			$value = floor($timestamp / 3600);
			return sprintf(ngettext('%d hour ago', '%d hours ago', $value), $value);
		}
		elseif($timestamp >= 60)
		{
			$value = floor($timestamp / 60);
			return sprintf(ngettext('%d minute ago', '%d minutes ago', $value), $value);
		}
		else
		{
			$value = $timestamp;
			return sprintf(ngettext('%d second ago', '%d seconds ago', $value), $value);
		}
	}

	static function timeIn($timestamp)
	{
		$timestamp = $timestamp - time();
		if($timestamp >= 31536000)
		{
			$value = floor($timestamp / 31536000);
			return sprintf(ngettext('in %d year', 'in %d years', $value), $value);
		}
		elseif($timestamp >= 2592000)
		{
			$value = floor($timestamp / 2592000);
			return sprintf(ngettext('in %d month', 'in %d months', $value), $value);
		}
		elseif($timestamp >= 604800)
		{
			$value = floor($timestamp / 604800);
			return sprintf(ngettext('in %d week', 'in %d weeks', $value), $value);
		}
		elseif($timestamp >= 86400)
		{
			$value = floor($timestamp / 86400);
			return sprintf(ngettext('in %d day', 'in %d days', $value), $value);
		}
		elseif($timestamp >= 3600)
		{
			$value = floor($timestamp / 3600);
			return sprintf(ngettext('in %d hour', 'in %d hours', $value), $value);
		}
		elseif($timestamp >= 60)
		{
			$value = floor($timestamp / 60);
			return sprintf(ngettext('in %d minute', 'in %d minutes', $value), $value);
		}
		else
		{
			$value = $timestamp;
			return sprintf(ngettext('in %d second', 'in %d seconds', $value), $value);
		}
	}
	
	static function milliseconds($timestamp, $signed = false)
	{
		$time = (int)$timestamp;
		
		$negative = ($time < 0);
		if ($negative)
		{
			$time = abs($time);
		}
		
		$cent = str_pad(($time % 1000), 3, '0', STR_PAD_LEFT);
		$time = floor($time / 1000);
		$sec = str_pad($time % 60, 2, '0', STR_PAD_LEFT);
		$min = str_pad(floor($time / 60), 1, '0');
		$time = $min.':'.$sec.'.'.$cent;
		
		if ($signed)
		{
			return ($negative ? '-'.$time : '+'.$time);
		}
		else
		{
			return $time;
		}
	}
	
	static function ordinal($n)
	{
		static $ends = array('th','st','nd','rd','th','th','th','th','th','th');
		if(($n % 100) >= 11 && ($n % 100) <= 13)
			return $n.'th';
		else
			return $n.$ends[$n % 10];
	}
	
	static function dateTimeToString($datetime)
	{
		if(is_object($datetime))
			return $datetime->format('Y-m-d H:i:s');
		return $datetime;
	}
}

?>
