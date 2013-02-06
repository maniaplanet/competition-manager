<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9065 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-06 18:09:45 +0100 (jeu., 06 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

use CompetitionManager\Constants;
use CompetitionManager\Services\ParticipantService;
use CompetitionManager\Services\StageService;

class Registrations extends \CompetitionManager\Services\Stage implements FirstCompliant, IntermediateCompliant
{
	function __construct()
	{
		$this->type = Constants\StageType::REGISTRATIONS;
		$this->schedule = new \CompetitionManager\Services\Schedules\Range();
		$this->parameters['unregisterEndTime'] = null;
	}
	
	protected function onFetchObject()
	{
		parent::onFetchObject();
		if($this->parameters['unregisterEndTime'])
			$this->parameters['unregisterEndTime'] = new \DateTime($this->parameters['unregisterEndTime']);
	}
	
	function getName()
	{
		return _('Registrations');
	}
	
	function getInfo()
	{
		return null;
	}
	
	function getScheduleNames()
	{
		return array(_('Opening Period'));
	}
	
	function getIcon()
	{
		
	}
	
	function getAction()
	{
		return 'registrations';
	}
	
	function onCreate() { /* Nothing to schedule */ }
	
	function onReady($participants) { /* Nothing to do */ }
	
	function onRun()
	{
		if($this->endTime < new \DateTime())
		{
			$service = new StageService();
			$service->setState($this->stageId, Constants\State::OVER);
		}
	}
	
	function onMatchOver($match) { /* Can't happen as there is no match */ }
	
	function onEnd()
	{
		
	}
	
	///////////////////////////////////////////////////////////////////////////
	// Interfaces implementation
	///////////////////////////////////////////////////////////////////////////
	
	function onRegistration($participantId)
	{
		$service = new StageService();
		$service->assignParticipants($this->stageId, array($participantId), $this->getDefaultScore());
		$service = new ParticipantService();
		$service->updateStageInfo($this->stageId, $participantId, rand(1, $this->maxSlots ?: 1337), $this->getDefaultScore());
	}
	
	function onUnregistration($participantId)
	{
		$service = new StageService();
		$service->excludeParticipants($this->stageId, array($participantId));
	}
	
	function getPlaceholder($rank, $max)
	{
		return sprintf(_('#%d of random seed'), $rank);
	}
}

?>
