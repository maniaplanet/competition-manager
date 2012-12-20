<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9122 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-17 13:58:48 +0100 (lun., 17 déc. 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

class Stage extends AbstractObject
{
	/** @var int */
	public $stageId;
	/** @var string */
	public $type;
	/** @var int */
	public $competitionId;
	/** @var int */
	public $previousId;
	/** @var int */
	public $nextId;
	/** @var int */
	public $minSlots;
	/** @var int */
	public $maxSlots;
	/** @var int */
	public $state;
	/** @var \DateTime */
	public $startTime;
	/** @var \DateTime */
	public $endTime;
	/** @var Rules\AbstractRules */
	public $rules;
	/** @var mixed[] */
	public $matches;
	/** @var mixed[] */
	public $parameters;
	
	/** @var Competition */
	public $competition;
	/** @var Participant[] */
	public $participants = array();
	
	protected function onFetchObject()
	{
		if($this->startTime)
			$this->startTime = new \DateTime($this->startTime);
		if($this->endTime)
			$this->endTime = new \DateTime($this->endTime);
		$this->rules = JSON::unserialize($this->rules);
		$this->matches = JSON::unserialize($this->matches);
		$this->parameters = JSON::unserialize($this->parameters);
		
		$result = self::db()->execute('SELECT * FROM Competitions WHERE competitionId=%d', $this->competitionId);
		$this->competition = Competition::fromRecordSet($result);
	}
	
	function updateParticipantList()
	{
		$result = self::db()->execute(
				'SELECT Pa.participantId, Pl.*, T.*, IFNULL(Pl.name, T.name) as name, IFNULL(Pl.path, T.path) as path, SP.rank, SP.score, SP.scoreDetails, SP.qualified '.
				'FROM StageParticipants SP '.
					'INNER JOIN Participants Pa USING(participantId) '.
					'LEFT JOIN Players Pl USING(login) '.
					'LEFT JOIN Teams T USING(teamId) '.
				'WHERE SP.stageId=%d '.
				'ORDER BY SP.rank IS NULL ASC, SP.rank ASC',
				$this->stageId
			);
		
		$newValues = Participant::assocFromRecordSet($result, $this->stage->competition->isTeam ? 'teamId' : 'login');
		$this->participants = array_intersect_key($this->participants, $newValues) + $newValues;
	}
	
	/**
	 * @return Server
	 */
	function getLobby()
	{
		if(!$this->competition->lobbyId)
			return null;
		
		$result = self::db()->execute('SELECT * FROM Servers WHERE matchId=%d LIMIT 1', $this->competition->lobbyId);
		try
		{
			return Server::fromRecordSet($result);
		}
		catch(\Exception $e)
		{
			return null;
		}
	}
}

?>
