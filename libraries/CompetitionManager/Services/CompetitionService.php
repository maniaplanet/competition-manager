<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9111 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-14 11:38:10 +0100 (ven., 14 déc. 2012) $:
 */

namespace CompetitionManager\Services;

use ManiaLib\Database\Tools;
use CompetitionManager\Constants\State;

class CompetitionService extends \DedicatedManager\Services\AbstractService
{
	/**
	 * @param int $competitionId
	 * @return Competition
	 */
	function get($competitionId)
	{
		$result = $this->db()->execute('SELECT * FROM Competitions WHERE competitionId=%d', $competitionId);
		return Competition::fromRecordSet($result);
	}
	
	/**
	 * @param int $offset
	 * @param int $length
	 * @return Competition[]
	 */
	function getAll($offset=0, $length=0)
	{
		$result = $this->db()->execute(
				'SELECT Competitions.* FROM Stages INNER JOIN Competitions USING(competitionId) WHERE previousId IS NULL '.
				'ORDER BY Competitions.state ASC, startTime DESC '.Tools::getLimitString($offset, $length));
		return Competition::arrayFromRecordSet($result);
	}
	
	/**
	 * @param int $offset
	 * @param int $length
	 * @return Competition[]
	 */
	function getCurrent($offset=0, $length=0)
	{
		$result = $this->db()->execute(
				'SELECT * FROM Competitions WHERE state=%d '.Tools::getLimitString($offset, $length),
				State::STARTED);
		return Competition::arrayFromRecordSet($result);
	}
	
	/**
	 * @param int $offset
	 * @param int $length
	 * @return Competition[]
	 */
	function getArchived($offset=0, $length=0)
	{
		$result = $this->db()->execute(
				'SELECT * FROM Competitions WHERE state>=%d '.Tools::getLimitString($offset, $length),
				State::OVER);
		return Competition::arrayFromRecordSet($result);
	}
	
	/**
	 * @param int $offset
	 * @param int $length
	 * @return Competition[]
	 */
	function getUpcoming($offset=0, $length=0)
	{
		$result = $this->db()->execute(
				'SELECT * FROM Competitions WHERE state=%d '.Tools::getLimitString($offset, $length),
				State::READY);
		return Competition::arrayFromRecordSet($result);
	}
	
	/**
	 * @return int
	 */
	function count()
	{
		return (int) $this->db()->execute('SELECT COUNT(*) FROM Competitions')->fetchSingleValue();
	}
	
	/**
	 * @return int
	 */
	function countCurrent()
	{
		return (int) $this->db()->execute(
				'SELECT COUNT(*) FROM Competitions WHERE state=%d',
				State::STARTED
			)->fetchSingleValue();
	}
	
	/**
	 * @return int
	 */
	function countArchived()
	{
		return (int) $this->db()->execute(
				'SELECT COUNT(*) FROM Competitions WHERE state>=%d',
				State::OVER
			)->fetchSingleValue();
	}
	
	/**
	 * @return int
	 */
	function countUpcoming()
	{
		return (int) $this->db()->execute(
				'SELECT COUNT(*) FROM Competitions WHERE state=%d',
				State::READY
			)->fetchSingleValue();
	}
	
	/**
	 * @param Competition $competition
	 */
	function create(Competition $competition)
	{
		$this->db()->execute(
				'INSERT INTO Competitions(name, description, title, isLan, isTeam, registrationCost, rewards) VALUES (%s, %s, %s, %d, %d, %d, %s)',
				$this->db()->quote($competition->name),
				$this->db()->quote($competition->description),
				$this->db()->quote($competition->title),
				$competition->isLan,
				$competition->isTeam,
				$competition->registrationCost,
				$this->db()->quote(JSON::serialize($competition->rewards)));
		$competition->competitionId = $this->db()->insertID();
	}
	
	/**
	 * @param int $competitionId
	 * @param int $matchId
	 */
	function setLobby($competitionId, $matchId)
	{
		$this->db()->execute('UPDATE Competitions SET lobbyId=%d WHERE competitionId=%d', $matchId, $competitionId);
	}
	
	/**
	 * @param int $competitionId
	 * @param int $size
	 */
	function setTeamSize($competitionId, $size)
	{
		$this->db()->execute('UPDATE Competitions SET teamSize=%d WHERE competitionId=%d', $size, $competitionId);
	}
	
	/**
	 * @param int $competitionId
	 * @param int $state 
	 */
	function setState($competitionId, $state)
	{
		$this->db()->execute('UPDATE Competitions SET state=%d WHERE competitionId=%d AND state < %1$d', $state, $competitionId);
	}
	
	/**
	 * @param int $competitionId
	 * @param int $remoteId
	 */
	function setRemoteId($competitionId, $remoteId)
	{
		$this->db()->execute('UPDATE Competitions SET remoteId=%d WHERE competitionId=%d', $remoteId, $competitionId);
	}
	
	/**
	 * @param int $competitionId
	 * @param int $amount
	 */
	function alterPlanetsPool($competitionId, $amount)
	{
		$this->db()->execute('UPDATE Competitions SET planetsPool=planetsPool+%d WHERE competitionId=%d', $amount, $competitionId);
	}
	
	/**
	 * @param int $competitionId
	 */
	function delete($competitionId)
	{
		$this->db()->execute('DELETE FROM Competitions WHERE competitionId=%d', $competitionId);
	}
}

?>
