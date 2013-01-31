<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9086 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-11 18:14:05 +0100 (mar., 11 déc. 2012) $:
 */

namespace CompetitionManager\Services;

use ManiaLib\Database\Tools;

class ParticipantService extends \DedicatedManager\Services\AbstractService
{
	const TEAM_OR_PLAYER_INFO = 'Pl.*, T.*, IFNULL(Pl.name, T.name) as name, IFNULL(Pl.path, T.path) as path ';
	
	/**
	 * @param id $id
	 * @return Participant
	 */
	function get($id)
	{
		$result = $this->db()->execute(
				'SELECT Pa.participantId, '.self::TEAM_OR_PLAYER_INFO.' '.
				'FROM Participants Pa '.
					'LEFT JOIN Players Pl USING(login) '.
					'LEFT JOIN Teams T USING(teamId) '.
				'WHERE participantId=%d',
				$id
			);
		return Participant::fromRecordSet($result);
	}
	
	/**
	 * @param string $login
	 * @return Player
	 */
	function getPlayerUId($login)
	{
		return $this->db()->execute(
				'SELECT participantId FROM Participants WHERE login=%s',
				$this->db()->quote($login)
			)->fetchSingleValue();
	}
	
	/**
	 * @param int $teamId
	 * @return Team
	 */
	function getTeamUId($teamId)
	{
		return $this->db()->execute(
				'SELECT participantId FROM Participants WHERE teamId=%d',
				$teamId
			)->fetchSingleValue();
	}
	
	/**
	 * @param int $participantId
	 * @param int $matchId
	 * @return Participant
	 */
	function getWithMatchScore($participantId, $matchId)
	{
		$result = $this->db()->execute(
				'SELECT Pa.participantId, '.self::TEAM_OR_PLAYER_INFO.', MP.rank, MP.score, MP.qualified '.
				'FROM Participants Pa '.
					'LEFT JOIN Players Pl USING(login) '.
					'LEFT JOIN Teams T USING(teamId) '.
					'INNER JOIN MatchParticipants MP USING(participantId) '.
				'WHERE participantId=%d AND matchId=%d',
				$participantId,
				$matchId);
		return Participant::fromRecordSet($result);
	}
	
	/**
	 * @param int $participantId
	 * @param int $stageId
	 * @return Participant
	 */
	function getWithStageScore($participantId, $stageId)
	{
		$result = $this->db()->execute(
				'SELECT Pa.participantId, '.self::TEAM_OR_PLAYER_INFO.', SP.rank, SP.score, SP.qualified '.
				'FROM Participants Pa '.
					'LEFT JOIN Players Pl USING(login) '.
					'LEFT JOIN Teams T USING(teamId) '.
					'INNER JOIN StageParticipants SP USING(participantId) '.
				'WHERE participantId=%d AND stageId=%d',
				$participantId,
				$stageId);
		return Participant::fromRecordSet($result);
	}
	
	/**
	 * @param int $matchId
	 * @return Participant[]
	 */
	function getByMatch($matchId, $offset=0, $length=0)
	{
		$result = $this->db()->execute(
				'SELECT Pa.participantId, '.self::TEAM_OR_PLAYER_INFO.', MP.rank, MP.score, MP.qualified '.
				'FROM MatchParticipants MP '.
					'INNER JOIN Participants Pa USING(participantId) '.
					'LEFT JOIN Players Pl USING(login) '.
					'LEFT JOIN Teams T USING(teamId) '.
				'WHERE MP.matchId=%d '.
				'ORDER BY MP.rank IS NULL ASC, MP.rank ASC '.
				Tools::getLimitString($offset, $length),
				$matchId);
		return Participant::assocFromRecordSet($result, 'participantId');
	}
	
	/**
	 * @param int $stageId
	 * @return Participant[]
	 */
	function getByStage($stageId, $offset=0, $length=0)
	{
		$result = $this->db()->execute(
				'SELECT Pa.participantId, '.self::TEAM_OR_PLAYER_INFO.', SP.rank, SP.score, SP.qualified '.
				'FROM StageParticipants SP '.
					'INNER JOIN Participants Pa USING(participantId) '.
					'LEFT JOIN Players Pl USING(login) '.
					'LEFT JOIN Teams T USING(teamId) '.
				'WHERE SP.stageId=%d '.
				'ORDER BY SP.rank IS NULL ASC, SP.rank ASC '.
				Tools::getLimitString($offset, $length),
				$stageId);
		return Participant::assocFromRecordSet($result, 'participantId');
	}
	
	/**
	 * @param int $competitionId
	 * @return Participant[]
	 */
	function getByCompetition($competitionId, $offset=0, $length=0)
	{
		$result = $this->db()->execute(
				'SELECT Pa.participantId, '.self::TEAM_OR_PLAYER_INFO.' '.
				'FROM StageParticipants SP '.
					'INNER JOIN Participants Pa USING(participantId) '.
					'INNER JOIN Stages S USING(stageId) '.
					'LEFT JOIN Players Pl USING(login) '.
					'LEFT JOIN Teams T USING(teamId) '.
				'WHERE S.competitionId=%d AND S.previousId IS NULL '.
				Tools::getLimitString($offset, $length),
				$competitionId);
		return Participant::assocFromRecordSet($result, 'participantId');
	}
	
	/**
	 * @param int $participantId
	 * @return int[]
	 */
	function getCompetitionIds($participantId)
	{
		return $this->db()->execute(
				'SELECT competitionId FROM StageParticipants INNER JOIN Stages USING(stageId) WHERE previousId IS NULL AND participantId=%d',
				$participantId
			)->fetchArrayOfSingleValues();
	}
	
	/**
	 * @param int $participantId
	 * @param int $stageId
	 * @return int[]
	 */
	function getStageMatchIds($participantId, $stageId)
	{
		return $this->db()->execute(
				'SELECT matchId FROM MatchParticipants INNER JOIN Matches USING(matchId) WHERE participantId=%d AND stageId=%d',
				$participantId,
				$stageId
			)->fetchArrayOfSingleValues();
	}
	
	/**
	 * @param int $participantId
	 * @param int $competitionId
	 * @return bool
	 */
	function isRegisteredInCompetition($participantId, $competitionId)
	{
		return (bool) $this->db()->execute(
				'SELECT COUNT(*) '.
				'FROM StageParticipants SP '.
					'INNER JOIN Stages S USING(stageId) '.
				'WHERE S.competitionId=%d AND S.previousId IS NULL AND SP.participantId=%d',
				$competitionId,
				$participantId
			)->fetchSingleValue(false);
	}
	
	/**
	 * @param int $participantId
	 * @param int $stageId
	 * @return bool
	 */
	function isRegisteredInStage($participantId, $stageId)
	{
		return (bool) $this->db()->execute(
				'SELECT COUNT(*) FROM StageParticipants WHERE stageId=%d AND participantId=%d',
				$stageId,
				$participantId
			)->fetchSingleValue(false);
	}
	
	/**
	 * @param int $participantId
	 * @param int $matchId
	 * @return bool
	 */
	function isRegisteredInMatch($participantId, $matchId)
	{
		return (bool) $this->db()->execute(
				'SELECT COUNT(*) FROM MatchParticipants WHERE matchId=%d AND participantId=%d',
				$matchId,
				$participantId
			)->fetchSingleValue();
	}
	
	/**
	 * @param int $participantId
	 * @param string $title
	 * @return bool
	 */
	function hasTitle($participantId, $title)
	{
		return (bool) $this->db()->execute(
				'SELECT COUNT(*) FROM ParticipantTitles WHERE participantId=%d AND title=%s',
				$participantId,
				$this->db()->quote($title)
			)->fetchSingleValue();
	}
	
	/**
	 * @param int $competitionId
	 * @return int
	 */
	function countByCompetition($competitionId)
	{
		return $this->db()->execute(
				'SELECT COUNT(*) FROM StageParticipants INNER JOIN Stages USING(stageId) WHERE previousId IS NULL AND competitionId=%d',
				$competitionId
			)->fetchSingleValue();
	}
	
	/**
	 * @param int $stageId
	 * @return int
	 */
	function countByStage($stageId)
	{
		return $this->db()->execute(
				'SELECT COUNT(*) FROM StageParticipants WHERE stageId=%d',
				$stageId
			)->fetchSingleValue();
	}
	
	/**
	 * @param int $matchId
	 * @return int
	 */
	function countByMatch($matchId)
	{
		return $this->db()->execute(
				'SELECT COUNT(*) FROM MatchParticipants WHERE matchId=%d',
				$matchId
			)->fetchSingleValue();
	}
	
	/**
	 * @param int $competitionId
	 * @param int $teamId
	 * @return int
	 */
	function countPlayersInRoster($competitionId, $teamId)
	{
		return $this->db()->execute(
				'SELECT COUNT(*) FROM Rosters WHERE competitionId=%d AND teamId=%d',
				$competitionId,
				$teamId
			)->fetchSingleValue();
	}
	
	/**
	 * @param Player $player
	 */
	function createPlayer(Player $player)
	{
		if(!$this->getPlayerUId($player->login))
			$this->db()->execute('INSERT IGNORE INTO Participants(login) VALUES (%s)', $this->db()->quote($player->login));
		$this->db()->execute(
				'INSERT INTO Players(login, name, path) '.
				'VALUES (%s, %s, %s) '.
				'ON DUPLICATE KEY UPDATE name=VALUES(name), path=VALUES(path)',
				$this->db()->quote($player->login),
				$this->db()->quote($player->name),
				$this->db()->quote($player->path)
			);
		
		return $this->getPlayerUId($player->login);
	}
	
	/**
	 * @param Team $team
	 */
	function createTeam(Team $team)
	{
		if(!$this->getTeamUId($team->teamId))
			$this->db()->execute('INSERT IGNORE INTO Participants(teamId) VALUES (%d)', $team->teamId);
		$this->db()->execute(
				'INSERT INTO Teams(teamId, tag, name, path, city) '.
				'VALUES (%d, %s, %s, %s, %s) '.
				'ON DUPLICATE KEY UPDATE tag=VALUES(tag), name=VALUES(name), path=VALUES(path), city=VALUES(city)',
				$team->teamId,
				$this->db()->quote($team->tag),
				$this->db()->quote($team->name),
				$this->db()->quote($team->path),
				$this->db()->quote($team->city)
			);
		
		return $this->getTeamUId($team->teamId);
	}
	
	/**
	 * @param int $teamId
	 * @param string[] $players
	 */
	function updateTeamPlayers($teamId, $players)
	{
		$this->db()->execute(
				'UPDATE Teams SET players=%s WHERE teamId=%d',
				$this->db()->quote(strtolower(implode(',', $players))),
				$teamId
			);
	}
	
	/**
	 * @param int $participantId
	 * @param string[] $titles
	 */
	function updateTitles($participantId, $titles)
	{
		$db = $this->db();
		$values = array_map(function($t) use($participantId, $db) { return sprintf('(%d, %s)', $participantId, $db->quote($t)); }, $titles);
		$this->db()->execute('DELETE FROM ParticipantTitles WHERE participantId=%d', $participantId);
		$this->db()->execute('INSERT INTO ParticipantTitles(participantId, title) VALUES %s', implode(',', $values));
	}
	
	/**
	 * @param int $matchId
	 * @param int $participantId
	 * @param int $rank
	 * @param Score $score
	 */
	function updateMatchInfo($matchId, $participantId, $rank, $score)
	{
		$this->db()->execute(
				'UPDATE MatchParticipants SET rank=%s, score=%s WHERE matchId=%d AND participantId=%d',
				$rank !== null ? $rank : 'NULL',
				$this->db()->quote(JSON::serialize($score)),
				$matchId,
				$participantId
			);
	}
	
	/**
	 * @param int $matchId
	 * @param int $participantId
	 * @param int $qualified
	 */
	function setMatchQualification($matchId, $participantId, $qualified)
	{
		$this->db()->execute(
				'UPDATE MatchParticipants SET qualified=%d WHERE matchId=%d AND participantId=%d',
				$qualified, $matchId, $participantId
			);
	}
	
	/**
	 * @param int $stageId
	 * @param int $participantId
	 * @param int $rank
	 * @param Score $score
	 */
	function updateStageInfo($stageId, $participantId, $rank, $score)
	{
		$this->db()->execute(
				'UPDATE StageParticipants SET rank=%s, score=%s WHERE stageId=%d AND participantId=%d',
				$rank !== null ? $rank : 'NULL',
				$this->db()->quote(JSON::serialize($score)),
				$stageId,
				$participantId
			);
	}
	
	/**
	 * @param int $stageId
	 * @param int $participantId
	 * @param int $qualified
	 */
	function setStageQualification($stageId, $participantId, $qualified)
	{
		$this->db()->execute(
				'UPDATE StageParticipants SET qualified=%d WHERE stageId=%d AND participantId=%d',
				$qualified, $stageId, $participantId
			);
	}
}

?>
