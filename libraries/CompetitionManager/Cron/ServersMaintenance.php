<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9011 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-11-29 18:57:57 +0100 (jeu., 29 nov. 2012) $:
 */

namespace CompetitionManager\Cron;

class ServersMaintenance extends Cron
{
	protected $logName = 'servers';
	
	protected function onRun()
	{
		$this->head('Servers Maintenance');
		
		$service = new \CompetitionManager\Services\ServerService();
		$matchService = new \CompetitionManager\Services\MatchService();
		
		$this->debug('Checking if servers are still running...');
		$this->beginSection();
		foreach($service->getLives() as $server)
		{
			try
			{
				$server->fetchDetails();
				$server->connection->enableCallbacks(true);
				$server->connection->dedicatedEcho('CompetitionManager Cron '.COMPETITION_MANAGER_CRON_VERSION, '?census');
				sleep(1);
				$manialiveOk = false;
				foreach($server->connection->executeCallbacks() as $call)
				{
					if($call[0] == 'ManiaPlanet.Echo'
							&& $call[1][0] == '!census:CompetitionManager Cron '.COMPETITION_MANAGER_CRON_VERSION
							&& stripos($call[1][1], 'ManiaLive') !== false)
						$manialiveOk = true;
				}
				$server->closeConnection();
				
				if(!$manialiveOk)
				{
					$this->debug('Restarting ManiaLive for match #'.$server->matchId);
					$server->startManialive();
				}
			}
			catch(\Exception $e)
			{
				// Remove from table
				$service->delete($server->rpcHost, $server->rpcPort);
				
				if(!$server->matchId)
					continue;
				
				$match = $matchService->get($server->matchId);
				if($match->state >= \CompetitionManager\Constants\State::OVER)
					continue;
				
				// Start server if a dedicated account is available
				if( ($account = $service->getAvailableAccount()) )
				{
					$this->debug('Restarting server and ManiaLive for match #'.$server->matchId);
					try
					{
						$match->startServer($account);
						$match->fetchServer();
						$match->server->startManialive();
					}
					catch(\Exception $e)
					{
						$this->debug('Error while restarting match #'.$match->matchId);
					}
				}
				else
				{
					$this->debug('No account available to restart match #'.$server->matchId);
				}
			}
		}
		$this->endSection();
		$this->foot('Servers Maintenance Done!');
	}
}

?>
