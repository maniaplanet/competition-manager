<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9122 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-17 13:58:48 +0100 (lun., 17 déc. 2012) $:
 */

namespace CompetitionManager\Controllers;

use CompetitionManager\Constants\State;
use CompetitionManager\Filters;
use CompetitionManager\Services\CompetitionService;
use CompetitionManager\Services\WebServicesProxy;
use CompetitionManager\Services\Stages;
use CompetitionManager\Views\Competition\Index;

class Competition extends \ManiaLib\Application\Controller implements \ManiaLib\Application\Filterable
{
	/** @var \CompetitionManager\Services\Competition */
	private $competition;
	/** @var \CompetitionManager\Services\Stage */
	private $stage;
	/** @var \CompetitionManager\Services\Team[] */
	private $registrableTeams;
	
	/** @var Filters\MatchDisplay */
	private $matchDisplay;
	/** @var Filters\NextUserEvent */
	private $nextUserEvent;
	
	protected function onConstruct()
	{
		//$this->addFilter(new UserAgentAdapt(Filters\UserAgentAdapt::MANIAPLANET));
		$this->addFilter(new \ManiaLib\WebServices\ManiaConnectFilter());
		$this->addFilter(new Filters\NextPageMessage());
		$this->addFilter(new Filters\IncomeLogger());
		$this->addFilter($this);
		$this->addFilter($this->matchDisplay = new Filters\MatchDisplay());
		$this->addFilter($this->nextUserEvent = new Filters\NextUserEvent());
	}
	
	function preFilter()
	{
		$c = $this->request->get('c');
		if(!$c)
			$this->request->redirect('/');
		
		$service = new CompetitionService();
		$this->competition = $service->get($c);
		
		$s = $this->request->get('s');
		if($s && !in_array($this->request->getAction('index'), array('index', 'rules', 'results', 'register')))
		{
			if(!isset($this->competition->stages[$s]) || $this->competition->stages[$s]->getAction() != $this->request->getAction())
				$this->request->redirect('..');
			$this->stage = $this->competition->stages[$s];
			$this->stage->fetchMatches();
		}
		
		$this->matchDisplay->userId = $this->getUserParticipation() ? $this->getUserParticipation()->participantId : 0;
		$this->nextUserEvent->userId = $this->getUserParticipation() ? $this->getUserParticipation()->participantId : 0;
		$this->nextUserEvent->competition = $this->competition;
		$this->nextUserEvent->showNotification = $this->request->getAction('index') != 'index';
		$this->response->external = $this->request->get('external');
	}
	
	function postFilter()
	{
		$this->response->competition = $this->competition;
		$this->response->stage = $this->stage;
		$this->response->userParticipation = $this->getUserParticipation();
		
		// Prepare custom images
		$menuConfig = \ManiaLib\Gui\Cards\Navigation\Config::getInstance();
		$imagesUrl = \ManiaLib\Application\Config::getInstance()->getImagesURL();
//		if($this->competition->logo)
//			$menuConfig->titleBgURL = $imagesUrl.'competitions/'.$this->competition->logo;
//		else
			$menuConfig->titleBgURL = 'http://files.maniaplanet.com/manialinks/images/nav-header.dds';
//		if($this->competition->background)
//			$this->response->background = $imagesUrl.'competitions/'.$this->competition->background;
	}
	
	function index()
	{
		$firstStage = reset($this->competition->stages);
		$service = new \CompetitionManager\Services\ParticipantService();
		
		if($this->competition->state == State::CANCELLED)
			$this->response->displayState = Index::CANCELLED;
		else if($this->competition->state == State::ARCHIVED)
		{
			$this->response->displayState = Index::OVER;
			$lastStage = end($this->competition->stages);
			$lastStage->fetchParticipants();
			$podium = array(1 => array(), array(), array());
			foreach($lastStage->participants as $participant)
			{
				if($participant->rank > 0 && $participant->rank < 4)
					$podium[$participant->rank][] = $participant;
			}
			$this->response->podium = $podium;
		}
		else if($firstStage->state < State::STARTED)
		{
			$this->response->displayState = Index::UPCOMING;
			$this->response->time = reset($this->competition->stages)->startTime;
		}
		else if($firstStage->state == State::STARTED)
		{
			$participant = $this->getUserParticipation();
			if($participant)
			{
				if($firstStage instanceof Stages\OpenStage)
				{
					$this->request->set('s', $firstStage->stageId);
					$this->response->displayState = Index::OPENED_REGISTERED_QUALIFIERS;
					$this->response->ranking = $service->getWithStageScore($participant->participantId, $firstStage->stageId);
					$this->response->link = $this->request->createLinkArgList('../'.$firstStage->getAction(), 'c', 's', 'external');
					$this->request->restore('s');
				}
				else if($firstStage instanceof Stages\Lobby)
				{
					$this->response->displayState = Index::OPENED_REGISTERED_LOBBY;
					$firstStage->fetchMatches();
					$firstStage->matches[0]->fetchServer();
					if($firstStage->matches[0]->server)
						$this->response->link = $firstStage->matches[0]->server->getLink('qjoin');
					else
						$this->response->link = true;
				}
				else
					$this->response->displayState = Index::OPENED_REGISTERED_DEFAULT;
			}
			else if($this->canRegister())
			{
				$this->response->displayState = Index::OPENED_ALLOWED;
				if($this->competition->isTeam)
					$this->response->teams = $this->registrableTeams;
//					$this->response->teams = WebServicesProxy::getUserTeams();
			}
			else if($firstStage->maxSlots != 0 && $service->countByStage($firstStage->stageId) >= $firstStage->maxSlots)
				$this->response->displayState = Index::OPENED_FULL;
			else
				$this->response->displayState = Index::OPENED_FORBIDDEN;
		}
		else //if($firstStage->state > State::STARTED)
		{
			$currentStage = $this->competition->getCurrentStage();
			$participant = $this->getUserParticipation();
			
			if($participant && $service->isRegisteredInStage($participant->participantId, $currentStage->stageId))
				$this->response->displayState = Index::CLOSED_PLAYER;
			else
				$this->response->displayState = Index::CLOSED_VISITOR;
			
			$service = new \CompetitionManager\Services\MatchService();
			$this->response->runningMatches = $service->getRunningInCompetition($this->competition->competitionId);
			if(!$this->response->runningMatches)
				$this->response->nextMatches = $service->getNextInCompetition($this->competition->competitionId);
		}
	}
	
	function register($external=false, $team=null)
	{
		if(!$this->canRegister($team))
		{
			Filters\NextPageMessage::error(_('You are not allowed to register...'));
			$this->request->redirectArgList('..', 'c', 'external');
		}
		
		$config = \CompetitionManager\Config::getInstance();
		if($this->competition->registrationCost && $this->session->login != $config->paymentLogin && $config->arePaymentsConfigured())
		{
			if(!Filters\IncomeLogger::$expected || Filters\IncomeLogger::$expected->competitionId != $this->competition->competitionId)
			{
				$transaction = new \CompetitionManager\Services\Transaction();
				$transaction->competitionId = $this->competition->competitionId;
				$transaction->login = $this->session->login;
				$transaction->amount = $this->competition->registrationCost;
				$transaction->type = \CompetitionManager\Services\Transaction::REGISTRATION;
				if($team)
					$transaction->message = 'Registration of $<'.$team->name.'$> in $<'.$this->competition->name.'$> by $<'.$this->session->nickname.'$>';
				else
					$transaction->message = 'Registration of $<'.$this->session->nickname.'$> in $<'.$this->competition->name.'$>';
				
				$service = new \CompetitionManager\Services\TransactionService();
				if($service->create($transaction))
					$this->billRegister($transaction, $external, $team);
				else
					Filters\NextPageMessage::error(_('Transaction cannot be created, please try again later.'));
			}
			else if(Filters\IncomeLogger::$isPaid)
				$this->doRegister($external, $team);
			else
				$this->billRegister(Filters\IncomeLogger::$expected, $external, $team);
		}
		else
			$this->doRegister($external, $team);
		
		$this->request->redirectArgList('..', 'c');
	}
	
	function registrations()
	{
		$this->stage->fetchParticipants();
		$this->matchDisplay->prepare($this->stage);
		$this->matchDisplay->showRanks = false;
		$this->matchDisplay->showScores = false;
		$this->matchDisplay->linesToShow = 1;
		$this->matchDisplay->emptyLabels = _('No one registered yet...');
	}
	
	function lobby()
	{
		$this->stage->fetchParticipants();
		$this->matchDisplay->prepare($this->stage);
		$this->matchDisplay->autoButton(reset($this->stage->matches));
		$this->matchDisplay->showRanks = false;
		$this->matchDisplay->showScores = false;
		if(!$this->getUserParticipation())
			$this->matchDisplay->card->setButton(_('Play!'), $this->request->createLinkArgList('../register', 'c', 'external'));
		$this->matchDisplay->linesToShow = 1;
		$this->matchDisplay->emptyLabels = _('No one registered yet...');
	}
	
	function match()
	{
		$this->matchDisplay->prepare(reset($this->stage->matches));
		$this->matchDisplay->linesToShow = 1;
		$this->matchDisplay->emptyLabels = _('Waiting for previous stage to end...');
	}
	
	protected function openStage()
	{
		$this->response->resetViews();
		$this->response->registerView('CompetitionManager\\Views\\Competition\\OpenStage');
		
		if(!$this->matchDisplay->isPrepared())
		{
			if(count($this->stage->matches) == 1)
				$this->matchDisplay->prepare($this->stage->matches[0]);
			else
				$this->matchDisplay->prepare($this->stage);
			$this->matchDisplay->card->setName(_('Global ranking'));
		}
		if(count($this->stage->matches) > 5)
			$this->response->matchOffset = $this->request->get('offset');
		
		$this->matchDisplay->linesToShow = min($this->stage->maxSlots ?: 16, 16);
	}
	
	function qualifiers()
	{
		$this->openStage();
	}
	
	function openQualifiers()
	{
		$this->openStage();
	}
	
	function openMatch()
	{
		$this->openStage();
	}
	
	function brackets($m=null)
	{
		$winnerBracket = $this->stage->matches[Stages\EliminationTree::WINNERS_BRACKET];
		if(count($winnerBracket) == 1)
		{
			$this->matchDisplay->prepare($winnerBracket[0][0]);
			$this->matchDisplay->emptyLabels = array();
			if($winnerBracket[0][0]->state > State::UNKNOWN)
				$this->matchDisplay->emptyLabels = 'BYE';
			else
				$this->matchDisplay->emptyLabels = $this->stage->getEmptyLabels(Stages\EliminationTree::WINNERS_BRACKET, 0, 0);
			$this->matchDisplay->linesToShow = min($this->stage->maxSlots, $this->stage->parameters['slotsPerMatch']);
			return;
		}
		
		if($m)
		{
			list($bracket, $round, $offset) = $this->stage->findMatch($m);
			if(isset($winnerBracket[$round][$offset]))
			{
				$this->matchDisplay->prepare($winnerBracket[$round][$offset]);
				$this->matchDisplay->emptyLabels = array();
				$this->matchDisplay->linesToShow = $this->stage->parameters['slotsPerMatch'];
				if($winnerBracket[$round][$offset]->state > State::UNKNOWN)
					$this->matchDisplay->emptyLabels = 'BYE';
				else
					$this->matchDisplay->emptyLabels = $this->stage->getEmptyLabels($bracket, $round, $offset);
			}
			
			$this->request->delete('m');
			$this->matchDisplay->card->setCloseLink($this->request->createLink());
			$this->request->restore('m');
		}
		
		$maxColumns = $this->stage->parameters['slotsPerMatch'] == 2 ? 4 : 3;
		if(count($winnerBracket[0]) > 1 << ($maxColumns - 1))
		{
			$this->response->multipageTree = new \CompetitionManager\Utils\MultipageTree($winnerBracket, $maxColumns);
			$this->response->matches = $this->response->multipageTree->getSubTree();
			list($this->response->baseRound, $this->response->baseOffset) = $this->response->multipageTree->getTreeBase();
		}
		else
		{
			$this->response->matches = $winnerBracket;
			$this->response->baseRound = 0;
			$this->response->baseOffset = 0;
		}
	}
	
	function championship()
	{
		
	}
	
	function groupedChampionship($group=null)
	{
		
	}
	
	function rules($details=0, $external=0)
	{
//		if($external)
//			$this->response->disableDefaultViews();
		$this->response->details = $details;
	}
	
	function results()
	{
		$this->matchDisplay->prepare(end($this->competition->stages));
		$this->matchDisplay->card->setName(_('Results'));
		$this->matchDisplay->card->setTime(null);
		$this->matchDisplay->showScores = false;
	}
	
	/**
	 * @return \CompetitionManager\Services\Participant
	 */
	private function getUserParticipation()
	{
		static $userParticipation = null;
		
		if($userParticipation === null)
		{
			$service = new \CompetitionManager\Services\ParticipantService();
			if($this->competition->isTeam)
			{
				foreach(WebServicesProxy::getUserContracts() as $team)
					if($service->isRegisteredInCompetition($team->participantId, $this->competition->competitionId))
					{
						$userParticipation = $team;
						break;
					}
			}
			else if($service->isRegisteredInCompetition(WebServicesProxy::getUser()->participantId, $this->competition->competitionId))
				$userParticipation = WebServicesProxy::getUser();
			else
				$userParticipation = false;
		}
		
		return $userParticipation;
	}
	
	private function canRegister($team=null)
	{
		$first = reset($this->competition->stages);
		
		// Registrations are closed or not opened yet
		if($first->state != State::STARTED)
			return false;
		
		$service = new \CompetitionManager\Services\ParticipantService();
		if($this->competition->isTeam)
		{
			// When testing if buttons should be displayed
			if($team === null)
			{
				foreach(WebServicesProxy::getUserTeams() as $uniqId => $team)
				{
					if($service->hasTitle($team->participantId, $this->competition->title)
							&& !$service->isRegisteredInCompetition($team->participantId, $this->competition->competitionId))
//							&& count(WebServicesProxy::getTeamPlayers($team->teamId)) >= $this->competition->teamSize) FIXME
						$this->registrableTeams[$uniqId] = $team;
				}
				
				if(!$this->registrableTeams)
					return false;
			}
			// When trying to register a team
			else if(!\ManiaLib\Utils\Arrays::get(WebServicesProxy::getUserTeams(), $team))
				return false;
		}
		else
		{
			// Player is already registered
			if($this->getUserParticipation())
				return false;
			// Player does not own the title used in this competition
			if(!$service->hasTitle(WebServicesProxy::getUser()->participantId, $this->competition->title))
				return false;
		}
		
		// No slot limits
		if($first->maxSlots == 0)
			return true;
		
		// Check slots availability
		$service = new \CompetitionManager\Services\ParticipantService();
		return $service->countByStage($first->stageId) < $first->maxSlots;
	}
	
	private function billRegister($transaction, $external=false, $team=null)
	{
		$this->session->set(Filters\IncomeLogger::EXPECTED_KEY, $transaction);
		$queryVars = array(
			'transaction' => $transaction->remoteId,
			\ManiaLib\Application\Dispatcher::PATH_INFO_OVERRIDE_PARAM => '/competition/register',
			'c' => $this->competition->competitionId
		);
		if($team)
			$queryVars['team'] = $team;
		if($external)
			$queryVars['external'] = $external;
		$this->request->redirectAbsolute(\ManiaLib\Application\Config::getInstance()->manialink.'?'.http_build_query($queryVars));
	}
	
	private function doRegister($external=false, $team=null)
	{
		if($this->competition->isTeam)
		{
			$teams = WebServicesProxy::getUserTeams();
			reset($this->competition->stages)->onRegistration($teams[$team]->participantId);
			WebServicesProxy::onRegistration($this->competition->competitionId, $teams[$team]->participantId);
			
			$teams[$team]->updatePlayers();
			if(count($teams[$team]->players) < $this->competition->teamSize)
				Filters\NextPageMessage::warning(_('This team does not have enough players. It will be disqualified if this does not change when competition starts.'));
			else
				Filters\NextPageMessage::success(sprintf(_('%s has been successfully registered!'), '$<$i'.$teams[$team]->name.'$>'));
		}
		else
		{
			reset($this->competition->stages)->onRegistration(WebServicesProxy::getUser()->participantId);
			WebServicesProxy::onRegistration($this->competition->competitionId, WebServicesProxy::getUser()->participantId);
			Filters\NextPageMessage::success(_('You have been successfully registered!'));
		}
		
		if($this->competition->registrationCost && (Filters\IncomeLogger::$isPaid || $this->session->login == \CompetitionManager\Config::getInstance()->paymentLogin))
		{
			$service = new CompetitionService();
			$service->alterPlanetsPool($this->competition->competitionId, $this->competition->registrationCost);
		}
		
		if(!$external && !$this->competition->isScheduled())
		{
			$lobby = reset($this->competition->stages);
			$lobby->fetchMatches();
			$lobby->matches[0]->fetchServer();
			Filters\NextPageMessage::setRedirection($lobby->matches[0]->server->getLink('qjoin'));
		}
	}
}

?>
