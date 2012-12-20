<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Services;

abstract class AbstractObject extends \DedicatedManager\Services\AbstractObject
{
	/**
	 * Fetches an associative array of object from the record set
	 */
	static function assocFromRecordSet(\ManiaLib\Database\RecordSet $result, $key)
	{
		$array = array();
		while($object = static::fromRecordSet($result, false))
		{
			$array[$object->$key] = $object;
		}
		return $array;
	}
}

?>
