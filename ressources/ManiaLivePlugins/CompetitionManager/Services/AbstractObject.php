<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

/**
 * Abstract object for (very) simple object relational mapping
 * Provides convenient methods for dealing with db result sets
 */
abstract class AbstractObject
{
	/** @var \ManiaLive\Database\Connection */
	static private $db;

	/**
	 * @return \ManiaLive\Database\Connection
	 */
	static protected function db()
	{
		if(!self::$db)
		{
			$config = \ManiaLive\Database\Config::getInstance();
			self::$db = \ManiaLive\Database\Connection::getConnection(
					$config->host,
					$config->username,
					$config->password,
					$config->database,
					$config->type,
					$config->port
			);
		}
		return self::$db;
	}
	
	/**
	 * Fetches a single object from the record set
	 */
	static function fromRecordSet(\ManiaLive\Database\RecordSet $result, $strict=true, $default=null, $message='Object not found')
	{
		if(!($object = $result->fetchObject(get_called_class())))
		{
			if($strict)
			{
				throw new NotFoundException(sprintf($message, get_called_class()));
			}
			else
			{
				return $default;
			}
		}
		$object->onFetchObject();
		return $object;
	}

	/**
	 * Fetches an array of object from the record set
	 */
	static function arrayFromRecordSet(\ManiaLive\Database\RecordSet $result)
	{
		$array = array();
		while($object = static::fromRecordSet($result, false))
		{
			$array[] = $object;
		}
		return $array;
	}
	
	/**
	 * Fetches an associative array of object from the record set
	 */
	static function assocFromRecordSet(\ManiaLive\Database\RecordSet $result, $key)
	{
		$array = array();
		while($object = static::fromRecordSet($result, false))
		{
			$array[$object->$key] = $object;
		}
		return $array;
	}

	/**
	 * Override this to do things when the object is fetched from a record set.
	 * Eg.: convering MySQL's TIMESTAMP fields into timestamp integers.
	 *
	 * You can also use the constructor since myqsl_fetch_object fills props before calling it
	 */
	protected function onFetchObject()
	{

	}
}

class NotFoundException extends \Exception {}

?>