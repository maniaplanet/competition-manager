<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9079 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-10 18:19:31 +0100 (lun., 10 déc. 2012) $:
 */

namespace CompetitionManager\Cron;

use CompetitionManager\Constants\State;

class CompetitionHandling extends Cron
{
	protected $logName = 'competition';
	
	protected function onRun()
	{
		$this->head('Competition Handling');
		
		$matchService = new \CompetitionManager\Services\MatchService();
		$stageService = new \CompetitionManager\Services\StageService();
		$competitionService = new \CompetitionManager\Services\CompetitionService();

		$this->debug('Handling last results...');
		$this->beginSection();
		$matchFiles = array();
		foreach($matchService->getFinished() as $match)
		{
			$this->debug('Match #'.$match->matchId);
			$stageService->get($match->stageId)->onMatchOver($match);
			
			$matchFiles[] = 'competition.match-'.$match->matchId;
		}
		if($matchFiles)
		{
			$fileService = new \DedicatedManager\Services\ConfigFileService();
			$fileService->deleteList($matchFiles);
			$fileService = new \CompetitionManager\Services\GuestListFileService();
			$fileService->deleteList($matchFiles);
			$fileService = new \DedicatedManager\Services\MatchSettingsFileService();
			$fileService->deleteList($matchFiles);
			$fileService = new \DedicatedManager\Services\ManialiveFileService();
			$fileService->deleteList($matchFiles);
		}
		$this->endSection();
		
		////////////////////////////////////////////////////////////////////////
		
		$this->debug('Updating running stages...');
		$this->beginSection();
		foreach($stageService->getRunning() as $stage)
		{
			$stage->onRun();
			$this->debug('Stage #'.$stage->stageId.' updated');
		}
		$this->endSection();
		
		////////////////////////////////////////////////////////////////////////
		
		$this->debug('Handling finished stages...');
		$this->beginSection();
		foreach($stageService->getOver() as $stage)
		{
			/* @var $stage \CompetitionManager\Services\Stage */
			$stage->fetchParticipants();
			$stage->onEnd();
			$competition = $competitionService->get($stage->competitionId);
			
			if($competition->isTeam && !$stage->previousId)
			{
				foreach($stage->participants as $team)
					$team->updatePlayers();
			}

			if($stage->nextId)
			{
				if(count($stage->participants) < $stage->minSlots)
				{
					$this->debug('Stage #'.$stage->stageId.' finished, not enough participants so competition #'.$stage->competitionId.' cancelled');
					$stageService->setState($stage->nextId, State::CANCELLED);
					$competitionService->setState($stage->competitionId, State::CANCELLED);
					$competitionService->get($stage->competitionId)->refundEveryone();
				}
				else
				{
					$this->debug('Stage #'.$stage->stageId.' finished, setting up stage #'.$stage->nextId);
					$nextStage = $stageService->get($stage->nextId);
					$participantsToKeep = array_filter(
							$stage->participants,
							function ($p) use ($nextStage, $competition)
							{
								if($competition->isTeam && count($p->players) < $nextStage->rules->getTeamSize())
									return false;
								return !$nextStage->maxSlots || ($p->rank !== null && $p->rank <= $nextStage->maxSlots);
							}
						);
					$stageService->assignParticipants($nextStage->stageId, $participantsToKeep, $nextStage->getDefaultDetails());
					$stageService->setState($nextStage->stageId, State::READY);
					$nextStage->onReady($participantsToKeep);
				}
			}
			else
			{
				$this->debug('Stage #'.$stage->stageId.' finished, Competition #'.$stage->competitionId.' finished');
				$competitionService->setState($stage->competitionId, State::OVER);
				$competitionService->get($stage->competitionId)->giveRewards();
				$competitionService->setState($stage->competitionId, State::ARCHIVED);
				\CompetitionManager\Services\WebServicesProxy::onResults($stage->competitionId);
			}

			$stageService->setState($stage->stageId, State::ARCHIVED);
		}
		$this->endSection();
		
		////////////////////////////////////////////////////////////////////////
		
		$this->debug('Launching next stages...');
		$this->beginSection();
		foreach($stageService->getNextToStart() as $stage)
		{
			$stageService->setState($stage->stageId, State::STARTED);
			$this->debug('Stage #'.$stage->stageId.' started');
			if(!$stage->previousId)
				$competitionService->setState($stage->competitionId, State::STARTED);
		}
		$this->endSection();
		
		////////////////////////////////////////////////////////////////////////
		
		$this->debug('Starting servers for next matches...');
		$this->beginSection();
		if( ($matches = $matchService->getNextToAssign()) )
		{
			$serverService = new \CompetitionManager\Services\ServerService();
			while($match = array_shift($matches) && $account = $serverService->getAvailableAccount())
			{
				try
				{
					$match->startServer($account);
					$match->fetchServer();
					$match->server->startManialive();
					$matchService->setState($match->matchId, State::STARTED);
				}
				catch(\Exception $e)
				{
					$this->debug('Error while starting match #'.$match->matchId.' using account `'.$account->login.'`');
				}
			}
		}
		$this->endSection();
		$this->foot('Competition Handling Done!');
	}
}

?>
