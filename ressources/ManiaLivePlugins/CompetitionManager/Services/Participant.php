<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

abstract class Participant extends AbstractObject
{
	/** @var int */
	public $participantId;
	
	/** @var int */
	public $rank = null;
	/** @var string */
	public $score = null;
	/** @var ScoreDetails\BasicDetails */
	public $scoreDetails = null;
	/** @var int */
	public $qualified = \ManiaLivePlugins\CompetitionManager\Constants\Qualified::UNKNOWN;
	
	static function fromRecordSet(\ManiaLive\Database\RecordSet $result, $strict=true, $default=null, $message='Object not found')
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
		if($this->scoreDetails)
			$this->scoreDetails = JSON::unserialize($this->scoreDetails);
	}
	
	function formatScore($detailsStyle='$888$i')
	{
		if(!$this->scoreDetails)
			return $this->score === null ? '-' : $this->score;
		
		if($this->scoreDetails->isTime)
			$scoreStr = $this->score === null ? '-:--.---' : \ManiaLive\Utilities\Time::fromTM($this->score);
		else
			$scoreStr = $this->score === null ? '-' : $this->score;
		
		if($detailsStyle)
		{
			if($this->scoreDetails instanceof \CompetitionManager\Services\ScoreDetails\TriesCount)
				$scoreStr .= $detailsStyle.' ('.$this->scoreDetails->nbTries.' '.ngettext('try', 'tries', $this->scoreDetails->nbTries).')';
			else if($this->scoreDetails instanceof \CompetitionManager\Services\ScoreDetails\MapsCount)
				$scoreStr .= $detailsStyle.' ('.$this->scoreDetails->nbMaps.' '.ngettext('map', 'maps', $this->scoreDetails->nbMaps).')';
		}
		
		return $scoreStr;
	}
}

?>
