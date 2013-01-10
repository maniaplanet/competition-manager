<?php
/**
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Cron;

/**
 * @method \CompetitionManager\Cron\Cron getInstance()
 */
abstract class Cron extends \ManiaLib\Utils\Singleton
{
	protected $logName;
	private $sectionCount = 0;
	
	abstract protected function onRun();
	
	final function run()
	{
		try
		{
			$this->onRun();
		}
		catch(\Exception $e)
		{
			$this->debugException($e);
		}
	}

	final protected function debug($message, $log=false)
	{
		if($log)
			Logger::log(str_repeat('    ', $this->sectionCount).$message, $this->logName);
		echo str_repeat('    ', $this->sectionCount).$message.PHP_EOL;
	}

	final protected function debugException(\Exception $e)
	{
		while($this->sectionCount)
			$this->endSection();
		
		$this->foot('EXCEPTION! EXCEPTION! EXCEPTION!', '*', true);
		$this->debug(get_class($e), true);
		$this->debug($e->getMessage().' ('.$e->getCode().')', true);
		$this->debug('File: '.$e->getFile(), true);
		$this->debug('Line: '.$e->getLine(), true);
		$this->debug($e->getTraceAsString(), true);
		$this->debug('', true);
	}
	
	final protected function head($str, $c='-', $log=false)
	{
		$this->separator($c);
		$this->debug($c.$c.str_pad(' '.$str, 60).$c.$c);
		$this->separator($c);
	}
	
	final protected function foot($str, $c='-', $log=false)
	{
		$this->debug('');
		$this->debug(str_pad($c.$c.' '.$str.' ', 64, $c));
		$this->debug('');
	}

	final protected function separator($c='-')
	{
		$this->debug(str_repeat($c, 64));
	}

	final protected function beginSection()
	{
		$this->sectionCount++;
	}

	final protected function endSection()
	{
		if($this->sectionCount)
			$this->sectionCount--;
	}
}

?>