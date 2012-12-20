<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Templates;

class Scoring extends \CompetitionManager\Services\Template
{
	/** @var int[] */
	public $points;
	
	/**
	 * @param string $filename
	 * @return Scoring
	 */
	static function read($filename)
	{
		self::validate($filename);
		
		$xml = simplexml_load_file($filename);
		$obj = new self();
		$obj->name = self::readName($filename);
		$obj->description = (string) $xml->description;
		foreach($xml->points as $p)
			$obj->points[] = (int) $p;
		return $obj;
	}
}

?>
