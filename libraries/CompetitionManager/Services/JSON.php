<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

abstract class JSON
{
	/**
	 * @param mixed $var
	 * @return string
	 */
	static function serialize($var)
	{
		if(!is_object($var))
			return '!'.json_encode($var);
		
		if(method_exists($var, '_json_sleep'))
			$var->_json_sleep();
		
		$str = preg_replace('/^'.preg_quote(__NAMESPACE__, '/').'/', '', get_class($var)).'!'.json_encode($var);
		
		if(method_exists($var, '_json_wakeup'))
			$var->_json_wakeup();
		
		return $str;
	}
	
	/**
	 * @param string $json
	 * @return mixed
	 */
	static function unserialize($json)
	{
		list($class, $json) = explode('!', $json, 2);
		$var = json_decode($json, true);
		
		if(!$class)
			return $var;
		
		$class = __NAMESPACE__.$class;
		$object = new $class;
		foreach($var as $key => $value)
			$object->$key = $value;
		
		if(method_exists($object, '_json_wakeup'))
			$object->_json_wakeup();
		
		return $object;
	}
}

?>