<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

abstract class Participant extends AbstractObject
{
	/** @var int */
	public $participantId;
	/** @var string[] */
	public $titles;
	
	/** @var int */
	public $rank = null;
	/** @var Score */
	public $score = null;
	/** @var int */
	public $qualified = \CompetitionManager\Constants\Qualified::UNKNOWN;
	
	static function fromRecordSet(\ManiaLib\Database\RecordSet $result, $strict=true, $default=null, $message='Object not found')
	{
		if(!($assoc = $result->fetchAssoc()))
		{
			if($strict)
				throw new NotFoundException(sprintf($message, get_called_class()));
			else
				return $default;
		}
		
		if($assoc['login'])
			$object = new Player();
		else
			$object = new Team();
		
		foreach($assoc as $key => $value)
			if(property_exists($object, $key))
				$object->$key = $value;
		$object->onFetchObject();
		
		return $object;
	}
	
	protected function onFetchObject()
	{
		if($this->score)
			$this->score = JSON::unserialize($this->score);
	}
}

?>
