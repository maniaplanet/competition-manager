<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

abstract class Template
{
	/** @var string */
	public $name;
	/** @var string */
	public $description;
	
	/**
	 * @param string $filename
	 * @throws \LogicException
	 */
	static function read($filename)
	{
		throw new \LogicException('This method has to be defined in subclasses');
	}
	
	/**
	 * @param string $filename
	 * @return string
	 */
	final static function readName($filename)
	{
		$class = explode('\\', get_called_class());
		return basename($filename, '.'.end($class).'.txt');
	}
	
	/**
	 * @param string $filename
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	final static function validate($filename)
	{
		if(!file_exists($filename))
			throw new \InvalidArgumentException('File does not exist');
		
		$className = explode('\\', get_called_class());
		$className = end($className);
		$dom = new \DOMDocument();
		$dom->load($filename);
		return $dom->relaxNGValidate(__DIR__.'/../../../ressources/CompetitionManager/Validators/'.strtolower($className).'.rng');
	}
}

?>
