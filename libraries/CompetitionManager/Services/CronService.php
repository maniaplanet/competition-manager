<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Services;

class CronService extends \DedicatedManager\Services\AbstractService
{
	function start()
	{
		$isWindows = stripos(PHP_OS, 'WIN') === 0;
		if($isWindows)
			$startCommand = 'START php.exe cron.php';
		else
			$startCommand = 'php cron.php &';
		
		$procHandle = proc_open($startCommand, array(), $pipes, MANIALIB_APP_PATH);
		proc_close($procHandle);
		
		$this->db()->execute('TRUNCATE Cron');
		$this->db()->execute('INSERT INTO Cron(lastExecution) VALUES (NOW())');
	}
	
	function lifeSign()
	{
		$this->db()->execute('UPDATE Cron SET lastExecution=NOW()');
	}
	
	/**
	 * @return bool
	 */
	function isRunning()
	{
		return (bool) $this->db()->execute('SELECT DATE_ADD(lastExecution, INTERVAL 1 MINUTE) > NOW() FROM Cron')->fetchSingleValue(false);
	}
}

?>
