<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Cron;

class CronTab extends \ManiaLib\Utils\Singleton
{
	private $crons = array();
	private $frequencies = array();
	private $nextRunTimes = array();
	
	protected function __construct()
	{
		error_reporting(E_ALL);
		set_error_handler(array('\ManiaLib\Application\ErrorHandling', 'exceptionErrorHandler'));

		try
		{
			\ManiaLib\Application\ConfigLoader::disableCache();
			\ManiaLib\Application\ConfigLoader::setHostname('127.0.0.1');
			\ManiaLib\Application\ConfigLoader::load();
		}
		catch(\Exception $e)
		{
			Logger::log('ERROR WHILE LOADING CONFIG.', 'tab');
			Logger::log(\ManiaLib\Application\ErrorHandling::computeMessage($e), 'tab');
			exit;
		}
	}
	
	function addCron(Cron $cron, $frequency)
	{
		$key = get_class($cron);
		$this->crons[$key] = $cron;
		$this->frequencies[$key] = $frequency;
		$this->nextRunTimes[$key] = microtime(true);
	}
	
	function run()
	{
		$currentTime = microtime(true);
		$service = new \CompetitionManager\Services\CronService();
		while(true)
		{
			try
			{
				$nextWakeUp = $currentTime + 60;
				foreach($this->nextRunTimes as $cronClass => &$nextRunTime)
				{
					if($nextRunTime < $currentTime)
					{
						$this->crons[$cronClass]->run();
						$nextRunTime += $this->frequencies[$cronClass];
					}
					if(!$nextWakeUp || $nextRunTime < $nextWakeUp)
						$nextWakeUp = $nextRunTime;
				}

				while(($currentTime = microtime(true)) < $nextWakeUp)
				{
					time_sleep_until($nextWakeUp);
					$service->lifeSign();
				}
			}
			catch(\Exception $e)
			{
				Logger::log(\ManiaLib\Application\ErrorHandling::computeMessage($e), 'tab');
			}
		}
	}
}

?>
