<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9107 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-13 15:32:32 +0100 (jeu., 13 déc. 2012) $:
 */

namespace CompetitionManager\Services;

use CompetitionManager\Constants\State;

class Competition extends AbstractObject
{
	/** @var int */
	public $competitionId;
	/** @var int */
	public $remoteId;
	/** @var string */
	public $name;
	/** @var string */
	public $description;
	/** @var string */
	public $title;
	/** @var int */
	public $lobbyId;
	/** @var bool */
	public $isLan;
	/** @var bool */
	public $isTeam;
	/** @var int */
	public $teamSize;
	/** @var string */
	public $password;
	/** @var int */
	public $registrationCost;
	/** @var int */
	public $planetsPool;
	/** @var Templates\Rewards */
	public $rewards;
	/** @var int */
	public $state;
	
	/** @var Stage[] */
	public $stages = array();
	/** @var int */
	public $nbParticipants = 0;
	
	/** @var int[] */
	public $format;
	
	protected function onFetchObject()
	{
		$service = new StageService();
		$this->stages = $service->getByCompetition($this->competitionId);
		$service = new ParticipantService();
		$this->nbParticipants = $service->countByCompetition($this->competitionId);
		$this->rewards = JSON::unserialize($this->rewards);
	}
	
	function isScheduled()
	{
		return !(reset($this->stages) instanceof Stages\Lobby);
	}
	
	function getInfo()
	{
		$firstStage = reset($this->stages);
		$secondStage = next($this->stages);
		
		if($this->isScheduled())
		{
			$info[] = sprintf(_('%s ends on %s'), $firstStage->getName(), $firstStage->endTime->format('j F Y \a\t G:i T'));
			$info[] = sprintf(_('Competition starts on %s'), $secondStage->startTime->format('j F Y \a\t G:i T'));
		}
		else
			$info[] = _('Pick-up competition');
		if($this->registrationCost)
			$info[] = sprintf(_('%d planets to register'), $this->registrationCost);
		$info[] = sprintf(_('Minimum %d participants required'), $firstStage->minSlots);
		return $info;
	}
	
	function getStatus()
	{
		switch($this->state)
		{
			case State::UNKNOWN:  return 'Creating...';
			case State::READY:    return 'Ready';
			case State::STARTED:  return 'Started';
			case State::OVER:     return 'Over, giving rewards';
			case State::ARCHIVED: return 'Archived';
		}
	}
	
	/**
	 * @param string $page
	 * @return string
	 */
	function getManialink($page=null)
	{
		$request = \ManiaLib\Application\Request::getInstance();
		$request->set('c', $this->competitionId);
		if($page)
			$request->set(\ManiaLib\Application\Dispatcher::PATH_INFO_OVERRIDE_PARAM, '/competition/'.$page);
		
		$link = $request->createAbsoluteLinkArgList(\ManiaLib\Application\Config::getInstance()->manialink, 'c', \ManiaLib\Application\Dispatcher::PATH_INFO_OVERRIDE_PARAM, 'external');
		
		$request->restore('c');
		$request->restore(\ManiaLib\Application\Dispatcher::PATH_INFO_OVERRIDE_PARAM);
		
		return $link;
	}
	
	/**
	 * @return Stage
	 */
	function getFirstPlayStage()
	{
		$stage = reset($this->stages);
		if($stage instanceof Stages\Registrations || $stage instanceof Stages\Lobby)
			$stage = next($this->stages);
		return $stage;
	}
	
	
	/**
	 * @return Stage
	 */
	function getCurrentStage()
	{
		foreach($this->stages as $stage)
			if($stage->state < State::OVER)
				return $stage;
		return null;
	}
	
	function giveRewards()
	{
		if(!$this->rewards)
			return;
		
		$service = new ParticipantService();
		$lastStage = end($this->stages);
		$lastStage->fetchParticipants();
		$rewards = array_fill_keys(array_keys($lastStage->participants), 0);
		foreach($this->rewards->getReleventRules($service->countByCompetition($this->competitionId)) as $rule)
		{
			$toReward = $rule->getReleventParticipants($lastStage->participants);
			if(!$toReward)
				continue;
			
			$amount = $rule->getPlanetsByParticipants($this->planetsPool, count($toReward));
			foreach($toReward as $participant)
				$rewards[$participant->participantId] += $amount;
		}
		
		$service = new TransactionService();
		if($this->isTeam)
		{
			foreach($rewards as $participantId => $amount)
			{
				if(!$amount)
					continue;
				
				
			}
		}
		else
		{
			foreach($rewards as $participantId => $amount)
			{
				if(!$amount)
					continue;
				
				$prize = new Transaction();
				$prize->competitionId = $this->competitionId;
				$prize->login = $lastStage->participants[$participantId]->login;
				$prize->amount = $amount;
				$prize->type = Transaction::REWARD;
				$prize->message = 'Rewards after your participation in $<'.$this->name.'$>';
				$service->registerOutcome($prize);
			}
		}
	}
	
	function refundEveryone()
	{
		$service = new TransactionService();
		
		// Compute balances
		$refunds = array();
		foreach($service->getByCompetition($this->competitionId) as $transaction)
		{
			if(!isset($refunds[$transaction->login]))
				$refunds[$transaction->login] = array();
			if($transaction->isPaid())
			{
				$type = $transaction->type | Transaction::REFUND;
				$amount = $transaction->isIncome() ? $transaction->amount : -$transaction->amount;

				if(!isset($refunds[$transaction->login][$type]))
					$refunds[$transaction->login][$type] = $amount;
				else
					$refunds[$transaction->login][$type] += $amount;
			}
		}
		// Create transactions if necessary
		foreach($refunds as $login => $refundsByType)
		{
			foreach($refundsByType as $type => $amount)
			{
				if($amount > 0)
				{
					$refund = new Transaction();
					$refund->competitionId = $this->competitionId;
					$refund->login = $login;
					$refund->amount = $amount;
					$refund->type = $type;
					switch($refund->type ^ Transaction::REFUND)
					{
						case Transaction::REGISTRATION:
							$refund->message = sprintf('Refund registration in $<%s$> (reason: competition cancelled)', $this->name);
							break;
						case Transaction::SPONSOR:
							$refund->message = sprintf('Refund sponsoring of $<%s$> (reason: competition cancelled)', $this->name);
							break;
					}
					$service->registerOutcome($refund);
				}
			}
		}
	}
}

?>
