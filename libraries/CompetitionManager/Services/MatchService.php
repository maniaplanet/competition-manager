<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9148 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-19 16:57:08 +0100 (mer., 19 déc. 2012) $:
 */

namespace CompetitionManager\Services;

use CompetitionManager\Constants\State;
use CompetitionManager\Utils\Formatting;

class MatchService extends \DedicatedManager\Services\AbstractService
{
	/**
	 * @param int $matchId
	 * @return Match
	 */
	function get($matchId)
	{
		$result = $this->db()->execute('SELECT * FROM Matches WHERE matchId=%d', $matchId);
		return Match::fromRecordSet($result);
	}
	
	/**
	 * @param int $stageId
	 * @return Match[]
	 */
	function getByStage($stageId)
	{
		$result = $this->db()->execute('SELECT * FROM Matches WHERE stageId=%d', $stageId);
		return Match::assocFromRecordSet($result, 'matchId');
	}
	
	/**
	 * @return Match[]
	 */
	function getNextToAssign()
	{
		$result = $this->db()->execute(
				'SELECT M.* FROM Matches M LEFT JOIN Servers S USING(matchId) '.
				'WHERE M.state=%d '.
					'AND S.rpcHost IS NULL '.
					'AND (M.startTime IS NULL OR M.startTime < DATE_ADD(NOW(), INTERVAL 10 MINUTE))',
				State::READY);
		return Match::arrayFromRecordSet($result);
	}
	
	/**
	 * @return Match[]
	 */
	function getFinished()
	{
		$result = $this->db()->execute('SELECT * FROM Matches WHERE state=%d', State::OVER);
		return Match::assocFromRecordSet($result, 'matchId');
	}
	
	/**
	 * @return Match[]
	 */
	function getRunningInCompetition($competitionId)
	{
		$result = $this->db()->execute(
				'SELECT M.* FROM Matches M '.
					'INNER JOIN Stages S USING(stageId) '.
					'LEFT JOIN Competitions C ON C.lobbyId=M.matchId '.
				'WHERE S.competitionId=%d AND M.state=%d AND C.lobbyId IS NULL '.
				'ORDER BY M.startTime ASC',
				$competitionId,
				State::STARTED
			);
		return Match::assocFromRecordSet($result, 'matchId');
	}
	
	/**
	 * @return Match[]
	 */
	function getNextInCompetition($competitionId)
	{
		$result = $this->db()->execute(
				'SELECT M.* FROM Matches M '.
					'INNER JOIN Stages S USING(stageId) '.
					'LEFT JOIN Competitions C ON C.lobbyId=M.matchId '.
				'WHERE S.competitionId=%d AND M.state=%d AND C.lobbyId IS NULL '.
				'ORDER BY M.startTime ASC',
				$competitionId,
				State::READY
			);
		return Match::assocFromRecordSet($result, 'matchId');
	}
	
	/**
	 * @param int $participantId
	 * @param int $competitionId
	 * @return Match
	 */
	function getNextForParticipant($participantId, $competitionId)
	{
		$result = $this->db()->execute(
				'SELECT M.* FROM Matches M '.
					'INNER JOIN MatchParticipants MP USING(matchId) '.
					'INNER JOIN Stages S USING(stageId) '.
					'LEFT JOIN Competitions C ON C.lobbyId=M.matchId '.
				'WHERE S.competitionId=%d AND MP.participantId=%d AND M.state<%d AND C.lobbyId IS NULL '.
				'ORDER BY M.state DESC, M.startTime ASC',
				$competitionId,
				$participantId,
				State::OVER
			);
		if($result->recordCount())
			return Match::fromRecordSet($result);
		else
			return null;
	}
	
	/**
	 * @param int $matchId
	 * @return bool
	 */
	function isLAN($matchId)
	{
		return (bool) $this->db()->execute(
				'SELECT isLan '.
				'FROM Matches '.
					'INNER JOIN Stages USING(stageId) '.
					'INNER JOIN Competitions USING(competitionId) '.
				'WHERE matchId=%d',
				$matchId
			)->fetchSingleValue();
	}
	
	/**
	 * @param int $matchId
	 * @return bool
	 */
	function isLobby($matchId)
	{
		return (bool) $this->db()->execute('SELECT COUNT(*) FROM Competitions WHERE lobbyId=%d', $matchId)->fetchSingleValue();
	}
	
	/**
	 * @param int $matchId
	 * @param mixed[] $participants
	 * @param Score $score
	 */
	function assignParticipants($matchId, $participants, $score)
	{
		if(empty($participants))
			return;
		
		$db = $this->db();
		$values = array_map(
				function ($participant) use ($matchId, $score, $db)
				{
					$participantId = $participant instanceof Participant ? $participant->participantId : $participant;
					return sprintf('(%d, %d, %s)', $matchId, $participantId, $db->quote(JSON::serialize($score)));
				},
				$participants);
		$this->db()->execute('INSERT IGNORE INTO MatchParticipants(matchId, participantId, score) VALUES %s', implode(',', $values));
	}
	
	/**
	 * @param int $matchId
	 * @param int[] $participants
	 */
	function excludeParticipants($matchId, $participants)
	{
		if(empty($participants))
			return;
		
		$this->db()->execute(
				'DELETE FROM MatchParticipants WHERE matchId=%d AND participantId IN (%s)',
				$matchId,
				implode(',', array_map(function ($participantId) { return sprintf('%d', $participantId); }, $participants))
			);
	}
	
	/**
	 * @param int $matchId
	 * @param string[] $maps
	 */
	function assignMaps($matchId, $maps)
	{
		$db = $this->db();
		$values = array_map(
				function ($map) use ($matchId, $db)
				{
					return sprintf('(%d, %s, %s)', $matchId, $db->quote(dirname($map) != '.' ? dirname($map).'/' : ''), $db->quote(basename($map)));
				},
				$maps);
		$this->db()->execute('INSERT IGNORE INTO MatchMaps(matchId, filename, path) VALUES %s', implode(',', $values));
	}
	
	/**
	 * @param int $matchId
	 * @param string[] $maps
	 */
	function removeMaps($matchId, $maps)
	{
		$db = $this->db();
		$mapKeys = array_map(
				function ($map) use ($db)
				{
					return sprintf('(%s, %s)', $db->quote(dirname($map).'/'), $db->quote(basename($map)));
				},
				$maps);
		$this->db()->execute('DELETE FROM MatchMaps WHERE matchId=%d AND (path, filename) IN (%s)', $matchId, implode(',', $mapKeys));
	}
	
	/**
	 * @param Match $match 
	 */
	function create(Match $match)
	{
		$this->db()->execute(
				'INSERT INTO Matches(name, stageId, startTime, endTime, rules) VALUES (%s, %d, %s, %s, %s)',
				$this->db()->quote($match->name),
				$match->stageId,
				$match->startTime ? $this->db()->quote(Formatting::dateTimeToString($match->startTime)) : 'NULL',
				$match->endTime ? $this->db()->quote(Formatting::dateTimeToString($match->endTime)) : 'NULL',
				$this->db()->quote(JSON::serialize($match->rules))
			);
		
		$match->matchId = $this->db()->insertID();
	}
	
	/**
	 * @param int $id
	 */
	function getState($id)
	{
		return $this->db()->execute('SELECT state FROM Matches WHERE matchId=%d', $id)->fetchSingleValue();
	}
	
	/**
	 * @param int $id
	 * @param int $state
	 */
	function setState($id, $state)
	{
		$this->db()->execute('UPDATE Matches SET state=%d WHERE matchId=%d AND state < %1$d', $state, $id);
	}
	
	/**
	 * @param int $id 
	 */
	function delete($id)
	{
		$this->db()->execute('DELETE FROM Matches WHERE matchId=%d', $id);
	}
}

?>
