<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

class TemplateService extends \DedicatedManager\Services\AbstractService
{
	const SCORING = 'Scoring';
	const REWARDS = 'Rewards';
	const FORMAT = 'Format';
	
	/** @var string */
	private $directory;
	
	function __construct()
	{
		$this->directory = __DIR__.'/../../../templates/';
		if(!file_exists($this->directory))
			mkdir($this->directory, 0775);
	}
	
	/**
	 * @param string $name
	 * @param string $type
	 * @return Template
	 * @throws \InvalidArgumentException
	 */
	function get($name, $type)
	{
		$className = __NAMESPACE__.'\\Templates\\'.$type;
		if(!class_exists($className))
			throw new \InvalidArgumentException('Given type does not exist');
		
		return $className::read(MANIALIB_APP_PATH.'templates/'.$name.'.'.$type.'.txt');
	}
	
	/**
	 * @param string $type
	 * @return string[]
	 * @throws \InvalidArgumentException
	 */
	function getList($type)
	{
		$className = __NAMESPACE__.'\\Templates\\'.$type;
		if(!class_exists($className))
			throw new \InvalidArgumentException('Given type does not exist');
		
		$currentDir = getcwd();
		chdir($this->directory);

		$files = array();
		foreach(glob('*.'.$type.'.[tT][xX][tT]') as $file)
		{
			try
			{
				$className::validate($this->directory.$file);
				$files[] = stristr($file, '.'.$type.'.txt', true);
			}
			catch(\Exception $e)
			{
				\ManiaLib\Application\ErrorHandling::logException($e);
			}
		}
		chdir($currentDir);
		return $files;
	}

	/**
	 * @param string[] $files
	 */
	function deleteList(array $files)
	{
		foreach($files as $file)
		{
			if(file_exists($this->directory.$file.'.txt'))
			{
				unlink($this->directory.$file.'.txt');
			}
		}
	}
}

?>
