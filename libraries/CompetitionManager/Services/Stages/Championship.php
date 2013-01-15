<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9065 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-06 18:09:45 +0100 (jeu., 06 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

class Championship extends \CompetitionManager\Services\Stage
{
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::CHAMPIONSHIP;
		$this->schedule = new \CompetitionManager\Services\Schedules\Range();
		$this->parameters['nbRounds'] = 1;
	}
	
	function getName()
	{
		return _('Championship');
	}
	
	function getInfo()
	{
		
	}
	
	function getRoundsCount()
	{
		return $this->maxSlots;
	}
	
	function getScheduleNames()
	{
		return array();
	}
	
	function getIcon()
	{
		
	}
	
	function getAction()
	{
		return 'championship';
	}
	
	function onCreate()
	{
		
	}
	
	function onReady($participants)
	{
		
	}
	
	function onMatchOver($match)
	{
		
	}
	
	function onEnd()
	{
		
	}
}

?>
