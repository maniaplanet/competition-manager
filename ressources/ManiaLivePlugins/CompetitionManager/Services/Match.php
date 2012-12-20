<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

class Match extends AbstractObject
{
	static private $instance;
	
	/** @var int */
	public $matchId;
	/** @var string */
	public $name;
	/** @var int */
	public $stageId;
	/** @var string */
	public $serverLogin;
	/** @var \DateTime */
	public $startTime;
	/** @var \DateTime */
	public $endTime;
	/** @var Rules\AbstractRules */
	public $rules;
	/** @var int */
	public $state;
	
	/** @var \DateTime */
	public $availabilityTime;
	/** @var Stage */
	public $stage;
	/** @var Participant[] */
	public $participants = array();

	/**
	 * @return Match
	 * @throws \ManiaLive\Application\FatalException
	 */
	static function getInstance()
	{
		if(!self::$instance)
		{
			$matchId = \ManiaLivePlugins\CompetitionManager\Config::getInstance()->matchId;
			if(!$matchId)
				throw new \ManiaLive\Application\FatalException('Match not set');

			$result = self::db()->execute('SELECT * FROM Matches WHERE matchId=%d', $matchId);
			self::$instance = self::fromRecordSet($result);
		}
		
		return self::$instance;
	}
	
	protected function __construct() {}
	
	protected function onFetchObject()
	{
		if($this->startTime)
			$this->startTime = new \DateTime($this->startTime);
		if($this->endTime)
			$this->endTime = new \DateTime($this->endTime);
		$this->rules = JSON::unserialize($this->rules);
		
		$result = self::db()->execute('SELECT DATE_ADD(startTime, INTERVAL 2 MINUTE) FROM Servers WHERE matchId=%d', $this->matchId);
		$this->availabilityTime = new \DateTime($result->fetchSingleValue());
		$result = self::db()->execute('SELECT * FROM Stages WHERE stageId=%d', $this->stageId);
		$this->stage = Stage::fromRecordSet($result);
		if(!$this->rules)
			$this->rules = $this->stage->rules;
		
		$this->updateParticipantList();
	}
	
	function updateParticipantList()
	{
		$result = self::db()->execute(
				'SELECT Pa.participantId, Pl.*, T.*, IFNULL(Pl.name, T.name) as name, IFNULL(Pl.path, T.path) as path, MP.rank, MP.score, MP.scoreDetails, MP.qualified '.
				'FROM MatchParticipants MP '.
					'INNER JOIN Participants Pa USING(participantId) '.
					'LEFT JOIN Players Pl USING(login) '.
					'LEFT JOIN Teams T USING(teamId) '.
				'WHERE MP.matchId=%d '.
				'ORDER BY MP.rank IS NULL ASC, MP.rank ASC',
				$this->matchId
			);
		
		$newValues = Participant::assocFromRecordSet($result, $this->stage->competition->isTeam ? 'teamId' : 'login');
		$this->participants = array_intersect_key($this->participants, $newValues) + $newValues;
	}
	
	function getManialink($external=true)
	{
		return $this->stage->competition->getManialink(null, $external, array('c' => null, 'm' => $this->matchId));
	}
}

?>
