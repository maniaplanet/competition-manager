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
use CompetitionManager\Services\Transaction;
use CompetitionManager\Views\Competition\Index;

class Competition extends \ManiaLib\Application\Controller implements \ManiaLib\Application\Filterable
{
	/** @var \CompetitionManager\Services\Competition */
	private $competition;
	/** @var \CompetitionManager\Services\Stage */
	private $stage;
	/** @var \CompetitionManager\Services\Team[] */
	private $registrableTeams;
	/** @var \CompetitionManager\Services\Team[] */
	private $unregistrableTeams;
	
	/** @var Filters\RankingDisplay */
	private $rankingDisplay;
	/** @var Filters\NextUserEvent */
	private $nextUserEvent;
	
	protected function onConstruct()
	{
		//$this->addFilter(new Filters\UserAgentAdapt(Filters\UserAgentAdapt::MANIAPLANET));
		$this->addFilter(new \ManiaLib\WebServices\ManiaConnectFilter());
		$this->addFilter(new Filters\NextPageMessage());
		$this->addFilter(new Filters\IncomeLogger());
		$this->addFilter($this);
		$this->addFilter($this->rankingDisplay = new Filters\RankingDisplay());
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
		
		$this->rankingDisplay->userId = $this->getUserParticipation() ? $this->getUserParticipation()->participantId : 0;
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
				$this->response->displayState = Index::OPENED_ALLOWED;
			else if($firstStage->maxSlots != 0 && $service->countByStage($firstStage->stageId) >= $firstStage->maxSlots)
				$this->response->displayState = Index::OPENED_FULL;
			else
				$this->response->displayState = Index::OPENED_FORBIDDEN;
			
			$this->response->canRegister = $this->canRegister();
			$this->response->canUnregister = $this->canUnregister();
			if($this->competition->isTeam)
			{
				if($this->response->canRegister)
					$this->response->registrableTeams = $this->registrableTeams;
				if($this->response->canUnregister)
					$this->response->unregistrableTeams = $this->unregistrableTeams;
//				$this->response->teams = WebServicesProxy::getUserTeams();
			}
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
		
		$this->request->redirectArgList('..', 'c', 'external');
	}
	
	function unregister($team=null)
	{
		if(!$this->canUnregister($team))
		{
			Filters\NextPageMessage::error(_('You are not allowed to unregister...'));
			$this->request->redirectArgList('..', 'c');
		}
		
		$this->doUnregister($team);
		$this->request->redirectArgList('..', 'c');
	}
	
	function registrations()
	{
		$this->stage->fetchParticipants();
		$this->rankingDisplay->prepareBasic($this->stage);
		$this->rankingDisplay->showRanks = false;
		$this->rankingDisplay->showScores = false;
		$this->rankingDisplay->linesToShow = 1;
		$this->rankingDisplay->emptyLabels = _('No one registered yet...');
	}
	
	function lobby()
	{
		$this->stage->fetchParticipants();
		$this->rankingDisplay->prepareBasic($this->stage);
		$this->rankingDisplay->autoButton(reset($this->stage->matches));
		$this->rankingDisplay->showRanks = false;
		$this->rankingDisplay->showScores = false;
		if(!$this->getUserParticipation())
			$this->rankingDisplay->card->setButton(_('Play!'), $this->request->createLinkArgList('../register', 'c', 'external'));
		$this->rankingDisplay->linesToShow = 1;
		$this->rankingDisplay->emptyLabels = _('No one registered yet...');
	}
	
	function match()
	{
		$this->rankingDisplay->prepareMatch(reset($this->stage->matches));
		$this->rankingDisplay->linesToShow = 1;
		$this->rankingDisplay->emptyLabels = _('Waiting for previous stage to end...');
	}
	
	protected function openStage()
	{
		$this->response->resetViews();
		$this->response->registerView('CompetitionManager\\Views\\Competition\\OpenStage');
		
		if(!$this->rankingDisplay->isPrepared())
		{
			if(count($this->stage->matches) == 1)
				$this->rankingDisplay->prepareMatch($this->stage->matches[0]);
			else
				$this->rankingDisplay->prepareBasic($this->stage);
			$this->rankingDisplay->card->setName(_('Global ranking'));
		}
		if(count($this->stage->matches) > 5)
			$this->response->matchOffset = $this->request->get('offset');
		
		$this->rankingDisplay->linesToShow = min($this->stage->maxSlots ?: 16, 16);
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
	
	function brackets($bracket=Stages\Brackets::WINNERS_BRACKET)
	{
		// forcing bracket if a match display has been requested in URL
		if($this->rankingDisplay->isPrepared())
			list($bracket, $round, $offset) = $this->stage->findMatch($this->request->get('m'));
		
		$bracketMatches = $this->stage->matches[$bracket];
		// forcing match display if there's only one
		if(count($bracketMatches) == 1)
		{
			$round = $offset = 0;
			$this->rankingDisplay->prepareMatch($bracketMatches[0][0]);
		}
		// or adding close link if there's a match to display
		else if($this->rankingDisplay->isPrepared())
		{
			$this->request->delete('m');
			$this->rankingDisplay->card->setCloseLink($this->request->createLink());
			$this->request->restore('m');
		}
		
		// configuring match display
		if($this->rankingDisplay->isPrepared())
		{
			$this->rankingDisplay->linesToShow = min($this->stage->parameters['slotsPerMatch'], 16);
			$this->rankingDisplay->emptyLabels = array();
			if($bracketMatches[$round][$offset]->state > State::UNKNOWN)
				$this->rankingDisplay->emptyLabels = _('BYE');
			else
				$this->rankingDisplay->emptyLabels = $this->stage->getEmptyLabels($bracket, $round, $offset);
		}
		
		$this->response->bracket = $bracket;
		$maxColumns = $bracket == Stages\Brackets::LOSERS_BRACKET || $this->stage->parameters['slotsPerMatch'] < 5 ? 4 : 3;
		if(count($bracketMatches) > $maxColumns)
		{
			$this->response->multipageTree = new \CompetitionManager\Utils\MultipageTree($bracketMatches, $maxColumns, $bracket);
			$this->response->matches = $this->response->multipageTree->getSubTree();
			list($this->response->baseRound, $this->response->baseOffset) = $this->response->multipageTree->getTreeBase();
		}
		else
		{
			$this->response->matches = $bracketMatches;
			$this->response->baseRound = 0;
			$this->response->baseOffset = 0;
		}
	}
	
	function championship($round=null)
	{
		$this->rankingDisplay->prepareChampionship($this->stage);
		if($this->stage->state == State::UNKNOWN)
		{
			$this->rankingDisplay->emptyLabels = $this->stage->getEmptyLabels();
			$this->rankingDisplay->linesToShow = count($this->rankingDisplay->emptyLabels);
		}
		
		$this->stage->fetchMatches();
		$this->prepareChampionshipMatches($this->stage->matches, $round);
	}
	
	function groups($group=null, $round=null)
	{
		$this->stage->fetchParticipants();
		
		if($group === null)
		{
			$groups = array();
			foreach($this->stage->parameters['groupParticipants'] as $groupParticipants)
				$groups[] = array_intersect_key($this->stage->participants, array_flip($groupParticipants));
			
			if(count($groups) > 9)
			{
				$this->response->multipageList = new \CompetitionManager\Utils\MultipageList(count($groups), 9, 'groups');
				list($offset, $length) = $this->response->multipageList->getLimit();
				$this->response->groups = array_slice($groups, $offset, $length, true);
			}
			else
				$this->response->groups = $groups;
		}
		else
		{
			$this->response->resetViews();
			$this->response->registerView('\\CompetitionManager\\Views\\Competition\\Championship');
			
			$this->rankingDisplay->prepareChampionship($this->stage, $group);
			if($this->stage->state == State::UNKNOWN)
			{
				$this->rankingDisplay->emptyLabels = $this->stage->getEmptyLabels($group);
				$this->rankingDisplay->linesToShow = count($this->rankingDisplay->emptyLabels);
			}
			
			$this->stage->fetchMatches();
			$this->prepareChampionshipMatches($this->stage->matches[$group], $round);
		}
	}
	
	private function prepareChampionshipMatches($matches, $round)
	{
		if($this->stage->parameters['isFreeForAll'])
		{
			$slotsPerMatch = min(8, max($this->rankingDisplay->linesToShow, count($this->rankingDisplay->participants)));
			$matchesPerPage = intval(24 / $slotsPerMatch);
			if(count($matches) > $matchesPerPage)
			{
				$this->response->matchesMultipage = new \CompetitionManager\Utils\MultipageList(count($matches), $matchesPerPage, 'matches');
				list($offset, $length) = $this->response->matchesMultipage->getLimit();
				$this->response->matches = array_slice($matches, $offset, $length, true);
			}
			else
				$this->response->matches = $matches;
		}
		else
		{
			$this->response->round = $round = (int) $round;
			$this->response->maxRound = count($matches)-1;
			
			if(count($matches[$round]) > 10)
			{
				$this->response->matchesMultipage = new \CompetitionManager\Utils\MultipageList(count($matches[$round]), 10, 'matches');
				list($offset, $length) = $this->response->matchesMultipage->getLimit();
				$this->response->matches = array_slice($matches[$round], $offset, $length, true);
			}
			else
				$this->response->matches = $matches[$round];
		}
	}
	
	function rules($details=0, $external=0)
	{
//		if($external)
//			$this->response->disableDefaultViews();
		$this->response->details = $details;
	}
	
	function results()
	{
		$this->rankingDisplay->prepareBasic(end($this->competition->stages));
		$this->rankingDisplay->card->setName(_('Results'));
		$this->rankingDisplay->card->setTime(null);
		$this->rankingDisplay->showScores = false;
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
		static $canRegister = null;
		if($canRegister !== null)
			return $canRegister;
		
		$first = reset($this->competition->stages);
		
		// Registrations are closed or not opened yet
		if($first->state != State::STARTED)
			return $canRegister = false;
		
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
					return $canRegister = false;
			}
			// When trying to register a team
			else if(!\ManiaLib\Utils\Arrays::get(WebServicesProxy::getUserTeams(), $team))
				return $canRegister = false;
		}
		else
		{
			// Player is already registered
			if($this->getUserParticipation())
				return $canRegister = false;
			// Player does not own the title used in this competition
			if(!$service->hasTitle(WebServicesProxy::getUser()->participantId, $this->competition->title))
				return $canRegister = false;
		}
		
		// No slot limits
		if($first->maxSlots == 0)
			return $canRegister = true;
		
		// Check slots availability
		$service = new \CompetitionManager\Services\ParticipantService();
		return $canRegister = $service->countByStage($first->stageId) < $first->maxSlots;
	}
	
	private function canUnregister($team=null)
	{
		static $canUnregister = null;
		if($canUnregister !== null)
			return $canUnregister;
		
		$first = reset($this->competition->stages);
		
		// Registrations are closed or not opened yet
		if($first->state != State::STARTED || !($first instanceof Stages\Registrations))
			return $canUnregister = false;
		
		$service = new \CompetitionManager\Services\ParticipantService();
		if($this->competition->isTeam)
		{
			// When testing if buttons should be displayed
			if($team === null)
			{
				foreach(WebServicesProxy::getUserTeams() as $uniqId => $team)
				{
					if($service->isRegisteredInCompetition($team->participantId, $this->competition->competitionId))
						$this->unregistrableTeams[$uniqId] = $team;
				}
				
				if(!$this->unregistrableTeams)
					return $canUnregister = false;
			}
			// When trying to unregister a team
			else if( !($team = \ManiaLib\Utils\Arrays::get(WebServicesProxy::getUserTeams(), $team))
					|| !$service->isRegisteredInCompetition($team->participantId, $this->competition->competitionId))
				return $canUnregister = false;
		}
		else if(!$this->getUserParticipation())
			return $canUnregister = false;
		
		return $canUnregister = !$first->parameters['unregisterEndTime'] || $first->parameters['unregisterEndTime'] > new \DateTime();
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
	
	private function doUnregister($team=null)
	{
		if($this->competition->isTeam)
		{
			$teams = WebServicesProxy::getUserTeams();
			reset($this->competition->stages)->onUnregistration($teams[$team]->participantId);
			WebServicesProxy::onUnregistration($this->competition->competitionId, $teams[$team]->participantId);
			Filters\NextPageMessage::success(sprintf(_('You successfully unregistered %s!'), '$<$i'.$teams[$team]->name.'$>'));
		}
		else
		{
			reset($this->competition->stages)->onUnregistration(WebServicesProxy::getUser()->participantId);
			WebServicesProxy::onUnregistration($this->competition->competitionId, WebServicesProxy::getUser()->participantId);
			Filters\NextPageMessage::success(_('You have been successfully unregistered!'));
		}
		
		if($this->competition->registrationCost)
		{
			$service = new CompetitionService();
			$service->alterPlanetsPool($this->competition->competitionId, -$this->competition->registrationCost);
			
			$service = new \CompetitionManager\Services\TransactionService();
			$baseRefund = new Transaction();
			$baseRefund->competitionId = $this->competitionId;
			$baseRefund->type = Transaction::REGISTRATION | Transaction::REFUND;
			if($this->competition->isTeam)
			{
				$teams = WebServicesProxy::getUserTeams();
				$transactions = $service->getByParticipant($this->competition->competitionId, $teams[$team]->participantId);
				$baseRefund->teamId = $teams[$team]->teamId;
				$baseRefund->message = sprintf('Refund registration of $<%s$> in $<%s$> (reason: unregistered)', $teams[$team]->name, $this->competition->name);
			}
			else
			{
				$transactions = $service->getByParticipant($this->competition->competitionId, WebServicesProxy::getUser()->participantId);
				$baseRefund->message = sprintf('Refund registration in $<%s$> (reason: unregistered)', $this->competition->name);
			}
			
			$transactions = array_filter($transactions, function($t) { return $t->type & Transaction::REGISTRATION; });
			$amounts = array_reduce($transactions, function(&$v, $t) {
					@$v[$t->login] += $t->amount * ($t & Transaction::REFUND ? -1 : 1);
					return $v;
				}, array());
			foreach($amounts as $login => $amount)
			{
				if($login != \CompetitionManager\Config::getInstance()->paymentLogin && $amount > 0)
				{
					$refund = clone $baseRefund;
					$refund->login = $login;
					$refund->amount = $amount;
					$service->registerOutcome($refund);
				}
			}
		}
	}
}

?>
