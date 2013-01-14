<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9107 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-13 15:32:32 +0100 (jeu., 13 déc. 2012) $:
 */

namespace CompetitionManager\Controllers;

use DedicatedApi\Structures\GameInfos;
use CompetitionManager\Filters\UserAgentAdapt;
use CompetitionManager\Constants\State;
use CompetitionManager\Services\Rules;
use CompetitionManager\Services\Stages;
use CompetitionManager\Services\TemplateService;

class Create extends \DedicatedManager\Controllers\AbstractController
{
	protected $defaultAction = 'prepare';
	
	/** @var \CompetitionManager\Services\Competition */
	private $competition;
	/** @var \CompetitionManager\Services\Stage */
	private $stage;
	/** @var int */
	private $stageIndex;
	
	protected function onConstruct()
	{
		parent::onConstruct();
		
		$this->addFilter(new UserAgentAdapt(UserAgentAdapt::WEB_BROWSER));
		
		$header = \CompetitionManager\Helpers\Header::getInstance();
		$header->leftLink = $this->request->createLinkArgList('../go-home');
	}
	
	function preFilter()
	{
		parent::preFilter();
		if(!$this->isAdmin)
		{
			$this->session->set('error', _('You have to be an admin to create a competition.'));
			$this->request->redirectArgList('/');
		}
		$this->competition = $this->session->get('creation:competition');
		if(!$this->competition)
		{
			if(\ManiaLib\Application\Dispatcher::getInstance()->getAction($this->defaultAction) != $this->defaultAction)
				$this->request->redirectArgList('..');
				
			$this->competition = new \CompetitionManager\Services\Competition();
		}
		$this->stageIndex = $this->request->get('s');
		if($this->stageIndex !== null)
			$this->stage = $this->competition->stages[$this->stageIndex];
	}
	
	function postFilter()
	{
		parent::postFilter();
		$this->session->set('creation:competition', $this->competition);
		$this->response->competition = $this->competition;
		$this->response->stage = $this->stage;
		$this->response->stageIndex = $this->stageIndex;
	}
	
	function prepare()
	{
		$service = new TemplateService();
		$rewards = array();
		$formats = array();
		
		foreach($service->getList(TemplateService::REWARDS) as $name)
			$rewards[$name] = $service->get($name, TemplateService::REWARDS);
		foreach($service->getList(TemplateService::FORMAT) as $name)
			$formats[$name] = $service->get($name, TemplateService::FORMAT);
		
		$this->response->planetsUsable = \CompetitionManager\Config::getInstance()->arePaymentsConfigured();
		$this->response->rewards = $rewards;
		$this->response->formats = $formats;
	}
	
	function doPrepare()
	{
		$errors = array();
		
		$service = new TemplateService();
		$this->competition->name = $this->request->getPost('name');
		if(!$this->competition->name)
			$errors[] =  _('You need to give a name to your competition');
		$this->competition->title = $this->request->getPost('title');
		if(!$this->competition->title)
			$errors[] =  _('You need to choose a title for your competition');
		$this->competition->isLan = (bool) $this->request->getPost('isLan');
		$this->competition->isTeam = (bool) $this->request->getPost('isTeam');
		$this->competition->remoteId = (bool) $this->request->getPost('useRemote');
		$this->competition->registrationCost = $this->request->getPost('registrationCost', 0);
		$name = $this->request->getPost('rewards');
		$this->competition->rewards = $name ? $service->get($name, TemplateService::REWARDS) : null;
		
		$name = $this->request->getPost('format');
		if(!$name && !$this->competition->stages)
			$errors[] =  _('You need to choose the format of your competition');
		if($name)
		{
			$this->competition->format = $service->get($name, TemplateService::FORMAT)->stages;
			$this->competition->stages = array();
			foreach($this->competition->format as $stageType)
				$this->competition->stages[] = \CompetitionManager\Services\Stage::fromType($stageType);
		}
		
		if($this->request->getPost('isScheduled', 1))
		{
			if($this->request->getPost('hasOpenQualifiers'))
			{
				if(reset($this->competition->stages) instanceof Stages\OpenStage)
					$first = null;
				else
					$first = new Stages\OpenStage();
			}
			else
				$first = new Stages\Registrations();
		}
		else
			$first = new Stages\Lobby();
		
		if($first)
		{
			if(count($this->competition->stages) > count($this->competition->format))
			{
				if(get_class($first) != get_class($this->competition->stages[0]))
					$this->competition->stages[0] = $first;
			}
			else
			{
				array_unshift($this->competition->stages, $first);
			}
		}
		
		if($errors)
		{
			$this->session->set('error', $errors);
			$this->request->redirectArgList('..');
		}
		
		$this->request->set('s', 0);
		if($this->competition->stages[0] instanceof Stages\Registrations)
			$this->request->redirectArgList('../choose-schedule', 's');
		else
			$this->request->redirectArgList('../choose-rules', 's');
	}
	
	function chooseRules()
	{
		$this->response->availableModes = \CompetitionManager\Services\Rules\AbstractRules::GetList(
				$this->competition->title,
				$this->competition->isTeam,
				$this->stage instanceof Stages\Lobby
			);
		$service = new TemplateService();
		$systems = array();
		foreach($service->getList(TemplateService::SCORING) as $name)
			$systems[$name] = $service->get($name, TemplateService::SCORING);
		$this->response->scoringSystems = $systems;

		$header = \CompetitionManager\Helpers\Header::getInstance();
		$header->rightIcon = 'back';
		if($this->stageIndex == 0)
		{
			$header->rightText = _('Back to competition preparation');
			$header->rightLink = $this->request->createLinkArgList('..');
		}
		else if($this->competition->isScheduled())
		{
			$this->request->set('s', $this->stageIndex - 1);
			$header->rightText = _('Back to previous stage schedule');
			$header->rightLink = $this->request->createLinkArgList('../choose-schedule', 's');
			$this->request->restore('s');
		}
		else
		{
			$this->request->set('s', $this->stageIndex - 1);
			$header->rightText = _('Back to previous stage maps selection');
			$header->rightLink = $this->request->createLinkArgList('../choose-maps', 's');
			$this->request->restore('s');
		}
	}
	
	function setRules()
	{
		$errors = array();
		$this->stage->minSlots = $this->request->getPost('minSlots', 0);
		$this->stage->maxSlots = $this->request->getPost('maxSlots');
		if($this->stage instanceof Stages\Groups)
		{
			$this->stage->parameters['nbGroups'] = $this->request->getPost('nbGroups');
			if($this->stage->parameters['nbGroups'] < 2)
				$errors[] = _('There should be at least 2 groups');
			if($this->request->getPost('slotsPerGroup') < 2)
				$errors[] = _('There should be at least 2 slots per group');
		}
		else if($this->stage instanceof Stages\Brackets)
		{
			$this->stage->parameters['slotsPerMatch'] = $this->request->getPost('slotsPerMatch');
			if($this->stage->parameters['slotsPerMatch'] < 2)
				$errors[] = _('There should be at least 2 slots per match');
			$this->stage->parameters['withLosersBracket'] = $this->request->getPost('withLosersBracket', false);
			$this->stage->parameters['withSmallFinal'] = $this->stage->parameters['slotsPerMatch'] == 2 && $this->request->getPost('withSmallFinal', false);
		}
		if(!($this->stage instanceof Stages\Lobby))
		{
			$isFirst = $this->competition->getFirstPlayStage() === $this->stage;
			if($isFirst && $this->stage->minSlots < 2)
				$errors[] = _('Minimum slots should be at least 2');
			if($this->stage->maxSlots < 2)
				$errors[] = _('Maximum slots should be at least 2');
			else if($isFirst && $this->stage->maxSlots < $this->stage->minSlots)
				$errors[] = _('Minimum slots should be lower than maximum slots');
		}
		
		$rulesClass = $this->request->getPost('gamemode');
		if(!class_exists($rulesClass))
			$errors[] = _('Invalid game mode');
		else
		{
			$this->stage->rules = new $rulesClass;
			$service = new TemplateService();
			foreach($this->stage->rules->getSettings() as $setting => $description)
			{
				if($description[0] == 'scoring')
				{
					$name = @reset($this->request->getPost($setting));
					$this->stage->rules->$setting = $name ? $service->get($name, TemplateService::SCORING) : null;
				}
				else if($description[0] == 'bool')
					$this->stage->rules->$setting = (bool) @reset($this->request->getPost($setting));
				else
					$this->stage->rules->$setting = @reset($this->request->getPost($setting));
			}
			array_splice($errors, count($errors), 0, $this->stage->rules->validate());
		}
		
		if($errors)
		{
			$this->session->set('error', $errors);
			$this->request->redirectArgList('../choose-rules', 's');
		}
		$this->request->redirectArgList('../choose-maps', 's');
	}
	
	function chooseMaps()
	{
		$environment = $this->stage->rules->getTitle() == 'TMCanyon' ? 'Canyon' : 'Storm';

		if($this->stage->rules->gameMode == GameInfos::GAMEMODE_SCRIPT)
		{
			$service = new \DedicatedManager\Services\MatchSettingsFileService();
			$type = $service->getScriptMapType($this->stage->rules->name, $this->stage->rules->getTitle());
		}
		else
			$type = array('Race');

		$service = new \DedicatedManager\Services\MapService();
		$this->response->files = $service->getList('', true, $this->stage->rules instanceof Rules\Laps, $type, $environment);

		$header = \CompetitionManager\Helpers\Header::getInstance();
		$header->rightText = _('Back to stage rules');
		$header->rightIcon = 'back';
		$header->rightLink = $this->request->createLinkArgList('../choose-rules', 's');
	}
	
	function setMaps()
	{
		$this->stage->maps = array_filter(explode('|', $this->request->getPost('maps', '')));
		if(!$this->stage->maps)
		{
			$this->session->set('error', _('You have to select at least one map.'));
			$this->request->redirectArgList('../choose-maps', 's');
		}
		if($this->competition->isScheduled())
			$this->request->redirectArgList('../choose-schedule', 's');
		else if(($this->stageIndex + 1) == count($this->competition->stages))
			$this->request->redirectArgList('../preview');
		else
		{
			$this->request->set('s', $this->stageIndex + 1);
			$this->request->redirectArgList('../choose-rules', 's');
		}
	}
	
	function chooseSchedule()
	{
		if($this->stage->schedule instanceof \CompetitionManager\Services\Schedules\MultiSimple)
		{
			$nbRounds = $this->stage->getRoundsCount();
			$this->stage->schedule->startTimes = array_slice($this->stage->schedule->startTimes, 0, $nbRounds);
			while(count($this->stage->schedule->startTimes) < $nbRounds)
				$this->stage->schedule->startTimes[] = '';
		}
		
		$header = \CompetitionManager\Helpers\Header::getInstance();
		$header->rightIcon = 'back';
		if($this->stageIndex == 0 && $this->competition->stages[0] instanceof Stages\Registrations)
		{
			$header->rightText = _('Back to competition preparation');
			$header->rightLink = $this->request->createLinkArgList('..');
		}
		else
		{
			$header->rightText = _('Back to stage maps selection');
			$header->rightLink = $this->request->createLinkArgList('../choose-maps', 's');
		}
	}
	
	function setSchedule()
	{
		if($this->stageIndex > 0)
		{
			$minTime = $this->competition->stages[$this->stageIndex - 1]->schedule->getTimesLimit();
			$minTime = $minTime[1] ?: $minTime[0];
		}
		else
		{
			$minTime = null;
		}
		
		$errors = array();
		if($this->request->getPost('startTime'))
		{
			$this->stage->schedule->startTime = $this->request->getPost('startTime');
			if(!$this->stage->schedule->startTime)
				$errors[] = _('You have to set a start time.');
			else if($minTime && $this->stage->schedule->startTime < $minTime)
				$errors[] = _('This stage cannot start before the end of the previous one.');
			if($this->request->getPost('endTime'))
			{
				$this->stage->schedule->endTime = $this->request->getPost('endTime');
				if(!$this->stage->schedule->endTime)
					$errors[] = _('You have to set an end time.');
				else if($this->stage->schedule->startTime && $this->stage->schedule->endTime < $this->stage->schedule->startTime)
					$errors[] = _('This stage cannot end before it starts.');
			}
		}
		else
		{
			$matchNames = $this->stage->getScheduleNames();
			foreach($this->request->getPost('startTimes') as $i => $startTime)
			{
				$this->stage->schedule->startTimes[$i] = $startTime;
				if(!$startTime)
					$errors[] = sprintf(_('You forgot a date for %s'), $matchNames[$i]);
				else if($minTime && $startTime < $minTime)
				{
					if($i == 0)
						$errors[] = sprintf(_('The date of %s cannot be before the end of previous stage.'), $matchNames[0]);
					else
						$errors[] = sprintf(_('The date of %s cannot be before %s.'), $matchNames[$i], $matchNames[$i-1]);
				}
				$minTime = $startTime;
			}
		}
		
		if($errors)
		{
			$this->session->set('error', $errors);
			$this->request->redirectArgList('../choose-schedule', 's');
		}
		if(($this->stageIndex + 1) == count($this->competition->stages))
			$this->request->redirectArgList('../preview');
		else
		{
			$this->request->set('s', $this->stageIndex + 1);
			$this->request->redirectArgList('../choose-rules', 's');
		}
	}
	
	function preview()
	{
		$this->request->set('s', count($this->competition->stages) - 1);
		$header = \CompetitionManager\Helpers\Header::getInstance();
		$header->rightIcon = 'back';
		$header->rightText = _('Back to last stage schedule');
		$header->rightLink = $this->request->createLinkArgList('../choose-schedule', 's');
		$this->request->restore('s');
	}
	
	function doCreate()
	{
		// Competition
		$competitionService = new \CompetitionManager\Services\CompetitionService();
		$competitionService->create($this->competition);
		
		// Stages
		$stageService = new \CompetitionManager\Services\StageService();
		$lastStageId = null;
		$teamSize = 0;
		foreach($this->competition->stages as $stageIndex => $stage)
		{
			$stage->competitionId = $this->competition->competitionId;
			$stage->previousId = $lastStageId;
			if($stage instanceof Stages\Registrations || $stage instanceof Stages\Lobby)
			{
				$stage->minSlots = $this->competition->stages[$stageIndex+1]->minSlots;
				$stage->maxSlots = $this->competition->stages[$stageIndex+1]->maxSlots;
			}
			if($this->competition->isScheduled())
				list($stage->startTime, $stage->endTime) = $stage->schedule->getTimesLimit();
			$stageService->create($stage);
			
			if(!($stage instanceof Stages\Registrations))
			{
				$stageService->assignMaps($stage->stageId, $stage->maps);
				$stage->onCreate();
				$stageService->update($stage);
				
				if($stage instanceof Stages\Lobby)
					$competitionService->setLobby($this->competition->competitionId, reset($stage->matches));
				
				$teamSize = max($teamSize, $stage->rules->getTeamSize());
			}
			if(!$stage->previousId)
			{
				$stageService->setState($stage->stageId, State::READY);
				$stage->onReady(array());
			}
			
			$lastStageId = $stage->stageId;
		}
		$competitionService->setTeamSize($this->competition->competitionId, $teamSize);
		$competitionService->setState($this->competition->competitionId, State::READY);
		if($this->competition->remoteId)
			\CompetitionManager\Services\WebServicesProxy::onCreate($this->competition->competitionId);
		
		$this->session->set('success', _('Competition has been successfully created!'));
		$this->goHome();
	}
	
	function goHome()
	{
		$this->session->delete('creation:competition');
		$this->request->redirectArgList('/');
	}
}

?>
