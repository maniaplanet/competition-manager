<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9115 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-14 18:12:50 +0100 (ven., 14 déc. 2012) $:
 */

namespace CompetitionManager\Services\Stages;

use CompetitionManager\Constants\State;

class SingleMatch extends \CompetitionManager\Services\Stage
{
	function __construct()
	{
		$this->type = \CompetitionManager\Constants\StageType::SINGLE_MATCH;
		$this->schedule = new \CompetitionManager\Services\Schedules\Simple();
	}
	
	function getName()
	{
		return _('Match');
	}
	
	function getInfo()
	{
		if($this->rules->fixedSlots == 2)
			$info[] =  sprintf(_('%d on %1$d match'), $this->rules->getTeamSize() ?: 1);
		$info[] = _('Rules');
		$info[] = $this->rules->getInfo();
		return $info;
	}
	
	function getScheduleNames()
	{
		return array(_('Match time'));
	}
	
	function getIcon()
	{
		
	}
	
	function getAction()
	{
		return 'match';
	}
	
	function onCreate()
	{
		$this->matches = array();
		$match = new \CompetitionManager\Services\Match();
		$match->stageId = $this->stageId;
		$match->name = $this->rules->getName().' Match';
		$match->startTime = $this->schedule->startTime;
		$service = new \CompetitionManager\Services\MatchService();
		$service->create($match);
		
		$this->matches[] = $match->matchId;
	}
	
	function onReady($participants)
	{
		$this->participants = $participants;
		$service = new \CompetitionManager\Services\MatchService();
		$service->assignParticipants(reset($this->matches), $this->participants, $this->getDefaultDetails());
		$service->setState(reset($this->matches), \CompetitionManager\Constants\State::READY);
		\CompetitionManager\Services\WebServicesProxy::onMatchReady(reset($this->matches));
	}
	
	function onMatchOver($match)
	{
		$match->fetchParticipants();
		$service = new \CompetitionManager\Services\ParticipantService();
		foreach($match->participants as $participantId => $participant)
			$service->updateStageInfo($this->stageId, $participantId, $participant->rank, $participant->score, $participant->scoreDetails);
		
		$service = new \CompetitionManager\Services\MatchService();
		$service->setState($match->matchId, State::ARCHIVED);
	}
	
	function onEnd()
	{
		
	}
}

?>
