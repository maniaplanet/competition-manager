<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9142 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-18 15:09:58 +0100 (mar., 18 déc. 2012) $:
 */

namespace CompetitionManager\Services;

use CompetitionManager\Constants\StageType;
use CompetitionManager\Services\Scores;

abstract class Stage extends AbstractObject
{
	/** @var int */
	public $stageId;
	/** @var int */
	public $type;
	/** @var int */
	public $competitionId;
	/** @var int */
	public $previousId;
	/** @var int */
	public $nextId;
	/** @var int */
	public $minSlots = 2;
	/** @var int */
	public $maxSlots;
	/** @var int */
	public $state;
	/** @var \DateTime */
	public $startTime;
	/** @var \DateTime */
	public $endTime;
	/** @var Rules */
	public $rules;
	/** @var Schedule */
	public $schedule;
	/** @var mixed[] */
	public $matches;
	/** @var mixed[] */
	public $parameters;
	
	/** @var Participant[] */
	public $participants = array();
	/** @var Map[] */
	public $maps = array();
	
	/** @var bool */
	private $participantsFetched = false;
	/** @var bool */
	private $matchesFetched = false;
	/** @var bool */
	private $mapsFetched = false;
	
	static function fromType($type)
	{
		switch($type)
		{
			case StageType::REGISTRATIONS: return new Stages\Registrations();
			case StageType::LOBBY: return new Stages\Lobby();
			case StageType::OPEN_STAGE: return new Stages\OpenStage();
			case StageType::SINGLE_MATCH: return new Stages\SingleMatch();
			case StageType::BRACKETS: return new Stages\Brackets();
			case StageType::CHAMPIONSHIP: return new Stages\Championship();
			case StageType::GROUPS: return new Stages\Groups();
		}
	}
	
	static function fromRecordSet(\ManiaLib\Database\RecordSet $result, $strict=true, $default=null, $message='Object not found')
	{
		if(!($assoc = $result->fetchAssoc()))
		{
			if($strict)
				throw new NotFoundException(sprintf($message, get_called_class()));
			else
				return $default;
		}
		$object = self::fromType($assoc['type']);
		foreach($assoc as $key => $value)
			$object->$key = $value;
		$object->onFetchObject();
		
		return $object;
	}
	
	protected function onFetchObject()
	{
		$this->rules = JSON::unserialize($this->rules);
		$this->schedule = JSON::unserialize($this->schedule);
		$this->matches = JSON::unserialize($this->matches);
		$this->parameters = JSON::unserialize($this->parameters);
		if($this->startTime)
			$this->startTime = new \DateTime($this->startTime);
		if($this->endTime)
			$this->endTime = new \DateTime($this->endTime);
	}
	
	function fetchParticipants()
	{
		if(!$this->participantsFetched)
		{
			$service = new ParticipantService();
			$this->participants = $service->getByStage($this->stageId);
		}
		
		$this->participantsFetched = true;
	}
	
	function fetchMatches()
	{
		if(!$this->matchesFetched && $this->matches)
		{
			$service = new MatchService();
			$matchesById = $service->getByStage($this->stageId);
			array_walk_recursive($this->matches, function(&$match, $key) use($matchesById) { $match = $matchesById[$match]; });
		}
		
		$this->matchesFetched = true;
	}
	
	function fetchMaps()
	{
		if(!$this->mapsFetched)
		{
			$service = new MapService();
			$this->maps = $service->getByStage($this->stageId);
		}
		
		$this->mapsFetched = true;
	}
	
	function getManialink()
	{
		$request = \ManiaLib\Application\Request::getInstance();
		$request->set('s', $this->stageId);
		$link = $request->createAbsoluteLinkArgList(\ManiaLib\Application\Config::getInstance()->manialink, 's', 'external');
		$request->restore('s');
		
		return $link;
	}
	
	/**
	 * @return string
	 */
	abstract function getName();
	
	/**
	 * @return string[]
	 */
	abstract function getInfo();
	
	/**
	 * @return string[]
	 */
	abstract function getScheduleNames();
	
	abstract function getIcon();
	
	/**
	 * @return string
	 */
	abstract function getAction();
	
	/**
	 * @return Score
	 */
	function getDefaultScore()
	{
		return new Scores\None();
	}
	
	abstract function onCreate();
	
	/**
	 * @param Participant[]
	 */
	abstract function onReady($participants);
	
	function onRun()
	{
		$service = new StageService();
		if($service->countRemainingMatches($this->stageId) == 0)
			$service->setState($this->stageId, \CompetitionManager\Constants\State::OVER);
	}
	
	/**
	 * @param Match $match
	 */
	abstract function onMatchOver($match);
	
	abstract function onEnd();
}

?>
