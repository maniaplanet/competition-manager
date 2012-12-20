<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Templates;

class Format extends \CompetitionManager\Services\Template
{
	/** @var int[] */
	public $stages;
	
	/**
	 * @param string $filename
	 * @return Format
	 */
	static function read($filename)
	{
		self::validate($filename);
		
		$xml = simplexml_load_file($filename);
		$obj = new self();
		$obj->name = self::readName($filename);
		$obj->description = (string) $xml->description;
		
		foreach($xml->stages->children() as $stageTag)
		{
			$constant = strtoupper(preg_replace('/([A-Z])/', '_$1', $stageTag->getName()));
			$obj->stages[] = constant('\\CompetitionManager\\Constants\\StageType::'.$constant);
		}
		
		return $obj;
	}
}

?>
