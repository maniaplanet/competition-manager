<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8551 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-17 17:56:45 +0200 (mer., 17 oct. 2012) $:
 */

namespace CompetitionManager\Services;

class MapService extends \DedicatedManager\Services\MapService
{
	/**
	 * @param string[] $filenames
	 * @return \DedicatedManager\Services\Map[]
	 */
	protected function getSeveralByFilename(array $filenames)
	{
		$maps = array();
		foreach($filenames as $filename)
		{
			try
			{
				$maps[] = $this->get(basename($filename), dirname($filename).'/');
			}
			catch(\InvalidArgumentException $e)
			{
			}
		}
		return $maps;
	}
	
	/**
	 * @param int $competitionId
	 * @return \DedicatedManager\Services\Map[]
	 */
	function getByCompetition($competitionId)
	{
		$filenames = $this->db()->execute(
				'SELECT CONCAT(path, filename) '.
				'FROM Competitions '.
					'INNER JOIN Stages USING(competitionId) '.
					'INNER JOIN StageMaps SM USING(stageId) '.
				'WHERE competitionId=%d'.
			'UNION '.
				'SELECT CONCAT(path, filename) '.
				'FROM Competitions '.
					'INNER JOIN Stages USING(competitionId) '.
					'INNER JOIN Matches USING(stageId) '.
					'INNER JOIN MatchMaps USING(matchId) '.
				'WHERE competitionId=%d',
				$competitionId
			)->fetchArrayOfSingleValues();
		return $this->getSeveralByFilename(array_unique($filenames));
	}
	
	/**
	 * @param int $stageId
	 * @return \DedicatedManager\Services\Map[]
	 */
	function getByStage($stageId)
	{
		$filenames = $this->db()->execute('SELECT CONCAT(path, filename) FROM StageMaps WHERE stageId=%d', $stageId)->fetchArrayOfSingleValues();
		return $this->getSeveralByFilename($filenames);
	}
	
	/**
	 * @param int $matchId
	 * @return \DedicatedManager\Services\Map[]
	 */
	function getByMatch($matchId)
	{
		$filenames = $this->db()->execute('SELECT CONCAT(path, filename) FROM MatchMaps WHERE matchId=%d', $matchId)->fetchArrayOfSingleValues();
		return $this->getSeveralByFilename($filenames);
	}
}

?>
