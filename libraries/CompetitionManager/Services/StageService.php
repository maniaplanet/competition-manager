<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9148 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-19 16:57:08 +0100 (mer., 19 déc. 2012) $:
 */

namespace CompetitionManager\Services;

use CompetitionManager\Constants\Qualified;
use CompetitionManager\Constants\State;

class StageService extends \DedicatedManager\Services\AbstractService
{
	/**
	 * @param int $stageId
	 * @return Stage
	 */
	function get($stageId)
	{
		$result = $this->db()->execute('SELECT * FROM Stages WHERE stageId=%d', $stageId);
		return Stage::fromRecordSet($result);
	}
	
	/**
	 * @param int[] $stageIds
	 * @return Stage[]
	 */
	function getByIds($stageIds)
	{
		$result = $this->db()->execute('SELECT * FROM Stages WHERE stageId IN (%s)', implode(',', $stageIds));
		return Stage::assocFromRecordSet($result, 'stageId');
	}
	
	/**
	 * @param int $competitionId
	 * @return Stage[]
	 */
	function getByCompetition($competitionId)
	{
		$result = $this->db()->execute('SELECT * FROM Stages WHERE competitionId=%d', $competitionId);
		return Stage::assocFromRecordSet($result, 'stageId');
	}
	
	/**
	 * @return Stage[]
	 */
	function getNextToStart()
	{
		$result = $this->db()->execute(
				'SELECT * FROM Stages '.
				'WHERE state=%d '.
					'AND (startTime IS NULL OR startTime < DATE_ADD(NOW(), INTERVAL 5 MINUTE))',
				State::READY
			);
		return Stage::arrayFromRecordSet($result);
	}
	
	/**
	 * @return Stage[]
	 */
	function getRunning()
	{
		$result = $this->db()->execute('SELECT * FROM Stages WHERE state=%d', State::STARTED);
		return Stage::arrayFromRecordSet($result);
	}
	
	/**
	 * @return Stage[]
	 */
	function getOver()
	{
		$result = $this->db()->execute('SELECT * FROM Stages WHERE state=%d', State::OVER);
		return Stage::arrayFromRecordSet($result);
	}
	
	/**
	 * @param int $stageId
	 * @param mixed[] $participants
	 * @param ScoreDetails\BasicDetails $scoreDetails
	 * @param int $qualified
	 */
	function assignParticipants($stageId, $participants, $scoreDetails=null, $qualified=Qualified::UNKNOWN)
	{
		if(empty($participants))
			return;
		
		$db = $this->db();
		$values = array_map(
				function ($participant) use ($stageId, $scoreDetails, $db, $qualified)
				{
					$participantId = $participant instanceof Participant ? $participant->participantId : $participant;
					return sprintf('(%d, %d, %s, %d)', $stageId, $participantId, $db->quote(JSON::serialize($scoreDetails)), $qualified);
				},
				$participants);
		$this->db()->execute('INSERT IGNORE INTO StageParticipants(stageId, participantId, scoreDetails, qualified) VALUES %s', implode(',', $values));
	}
	
	/**
	 * @param int $stageId
	 * @param int[] $participants
	 */
	function excludeParticipants($stageId, $participants)
	{
		if(empty($participants))
			return;
		
		$this->db()->execute(
				'DELETE FROM StageParticipants WHERE stageId=%d AND participantId IN (%s)',
				$stageId,
				implode(',', array_map(function ($participantId) { return sprintf('%d', $participantId); }, $participants))
			);
	}
	
	/**
	 * @param int $stageId
	 * @param string[] $maps
	 */
	function assignMaps($stageId, $maps)
	{
		$db = $this->db();
		$values = array_map(
				function ($map) use ($stageId, $db)
				{
					return sprintf('(%d, %s, %s)', $stageId, $db->quote(dirname($map) != '.' ? dirname($map).'/' : ''), $db->quote(basename($map)));
				},
				$maps);
		$this->db()->execute('INSERT IGNORE INTO StageMaps(stageId, path, filename) VALUES %s', implode(',', $values));
	}
	
	/**
	 * @param int $stageId
	 * @param string[] $maps
	 */
	function removeMaps($stageId, $maps)
	{
		$db = $this->db();
		$mapKeys = array_map(
				function ($map) use ($db)
				{
					return sprintf('(%s, %s)', $db->quote(dirname($map).'/'), $db->quote(basename($map)));
				},
				$maps);
		$this->db()->execute('DELETE FROM StageMaps WHERE stageId=%d AND (path, filename) IN (%s)', $stageId, implode(',', $mapKeys));
	}
	
	/**
	 * @param int $stageId
	 * @return int
	 */
	function countRemainingMatches($stageId)
	{
		return $this->db()->execute(
				'SELECT COUNT(*) FROM Matches WHERE stageId=%d AND state<%d', $stageId, State::ARCHIVED
			)->fetchSingleValue();
	}
	
	/**
	 * @param Stage $stage
	 */
	function create(Stage $stage)
	{
		$this->db()->execute(
				'INSERT INTO Stages(competitionId, type, previousId, nextId, minSlots, maxSlots, startTime, endTime, rules, schedule, matches, parameters) '.
				'VALUES (%d, %d, %s, %s, %d, %d, %s, %s, %s, %s, %s, %s)',
				$stage->competitionId,
				$stage->type,
				$stage->previousId ?: 'NULL',
				$stage->nextId ?: 'NULL',
				$stage->minSlots,
				$stage->maxSlots,
				$stage->startTime ? $this->db()->quote($stage->startTime) : 'NULL',
				$stage->endTime ? $this->db()->quote($stage->endTime) : 'NULL',
				$this->db()->quote(JSON::serialize($stage->rules)),
				$this->db()->quote(JSON::serialize($stage->schedule)),
				$this->db()->quote(JSON::serialize(null)),
				$this->db()->quote(JSON::serialize($stage->parameters)));
		
		$stage->stageId = $this->db()->insertID();
		
		if($stage->previousId)
			$this->db()->execute('UPDATE Stages SET nextId=%d WHERE stageId=%d', $stage->stageId, $stage->previousId);
	}
	
	/**
	 * @param int $stageId
	 * @param int $state 
	 */
	function setState($stageId, $state)
	{
		$this->db()->execute('UPDATE Stages SET state=%d WHERE stageId=%d AND state < %1$d', $state, $stageId);
	}
	
	/**
	 * @param Stage $stage
	 */
	function update(Stage $stage)
	{
		$this->db()->execute(
				'UPDATE Stages SET maxSlots=%d, minSlots=%d ,startTime=%s, endTime=%s, rules=%s, schedule=%s, matches=%s, parameters=%s WHERE stageId=%d',
				$stage->maxSlots,
				$stage->minSlots,
				$stage->startTime ? $this->db()->quote(is_string($stage->startTime) ? $stage->startTime : $stage->startTime->format('Y-m-d H:i:s')) : 'NULL',
				$stage->endTime ? $this->db()->quote(is_string($stage->endTime) ? $stage->endTime : $stage->endTime->format('Y-m-d H:i:s')) : 'NULL',
				$this->db()->quote(JSON::serialize($stage->rules)),
				$this->db()->quote(JSON::serialize($stage->schedule)),
				$this->db()->quote(JSON::serialize($stage->matches)),
				$this->db()->quote(JSON::serialize($stage->parameters)),
				$stage->stageId);
	}
	
	/**
	 * @param int $stageId
	 * @param bool $keepNext
	 */
	function delete($stageId, $keepNext=true)
	{
		if($keepNext)
		{
			list($previousId, $nextId) = $this->db()->execute('SELECT previousId, nextId FROM Stages WHERE stageId=%d', $stageId)->fetchRow();
			if($nextId)
			{
				$this->db()->execute('UPDATE Stages SET previousId=%d WHERE stageId=%d', $previousId, $nextId);
				if($previousId)
					$this->db()->execute('UPDATE Stages SET nextId=%d WHERE stageId=%d', $nextId, $previousId);
			}
		}
		$this->db()->execute('DELETE FROM Stages WHERE stageId=%d', $stageId);
	}
}

?>
