<?php
/**
 * @version     $Revision$:
 * @author      $Author$:
 * @date        $Date$:
 */
namespace CompetitionManager\Utils;

class Date
{
	public static function getDate($date)
	{
		return static::getFormat($date, 'Y-m-d');
	}
	
	public static function getTime($date)
	{
		return static::getFormat($date, 'H:i:s');
	}
	
	public static function getFullDate($date)
	{
		return static::getDate($date).' '.static::getTime($date);
	}
	
	/**
	 * @param DateTime $date
	 * @param string $format
	 * @return string
	 */
	protected static function getFormat($date, $format)
	{
		if ($date instanceof \DateTime)
		{
			return $date->format($format);
		}
		return "";
	}
}
?>