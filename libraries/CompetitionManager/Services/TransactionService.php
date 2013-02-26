<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

class TransactionService extends \DedicatedManager\Services\AbstractService
{
	/**
	 * @param int $competitionId
	 * @return Transaction[]
	 */
	function getByCompetition($competitionId)
	{
		$result = $this->db()->execute('SELECT * FROM Transactions WHERE competitionId=%d', $competitionId);
		return Transaction::arrayFromRecordSet($result);
	}
	
	/**
	 * @param int $competitionId
	 * @param int $participantId
	 * @return Transaction[]
	 */
	function getByParticipant($competitionId, $participantId)
	{
		$service = new ParticipantService();
		$participant = $service->get($participantId);
		if($participant instanceof Player)
			$result = $this->db()->execute(
					'SELECT * FROM Transactions WHERE competitionId=%d AND login=%s',
					$competitionId,
					$participant->login
				);
		else
			$result = $this->db()->execute(
					'SELECT * FROM Transactions WHERE competitionId=%d AND teamId=%d',
					$competitionId,
					$participant->teamId
				);
	}
	
	/**
	 * @param int $competitionId
	 * @return int
	 */
	function getCompetitionBalance($competitionId)
	{
		return $this->db()->execute(
				'SELECT SUM(IF(type & %d, -amount, amount)) '.
				'FROM Transactions '.
				'WHERE competitionId=%d AND remoteId IS NOT NULL',
				 Transaction::REFUND,
				$competitionId
			)->fetchSingleValue();
	}
	
	/**
	 * @return Transaction[]
	 */
	function getAllPending()
	{
		$result = $this->db()->execute('SELECT * FROM Transactions WHERE remoteId IS NULL');
		return Transaction::arrayFromRecordSet($result);
	}
	
	/**
	 * @param Transaction $transaction
	 */
	function registerIncome(Transaction $transaction)
	{
		$this->db()->execute(
				'INSERT INTO Transactions(remoteId, competitionId, login, teamId, amount, type, message) VALUES (%d, %d, %s, %s, %d, %d, %s)',
				$transaction->remoteId,
				$transaction->competitionId,
				$this->db()->quote($transaction->login),
				intval($transaction->teamId) ?: 'NULL',
				$transaction->amount,
				$transaction->type,
				$this->db()->quote($transaction->message)
			);
	}
	
	/**
	 * @param Transaction $transaction
	 */
	function registerOutcome(Transaction $transaction)
	{
		$this->db()->execute(
				'INSERT INTO Transactions(competitionId, login, teamId, amount, type, message) VALUES (%d, %s, %s, %d, %d, %s)',
				$transaction->competitionId,
				$this->db()->quote($transaction->login),
				intval($transaction->teamId) ?: 'NULL',
				$transaction->amount,
				$transaction->type,
				$this->db()->quote($transaction->message)
			);
	}
	
	/**
	 * @param int $transactionId
	 * @param int $remoteId
	 */
	function setOutcomePaid($transactionId, $remoteId)
	{
		$this->db()->execute('UPDATE Transactions SET remoteId=%d WHERE transactionId=%d', $remoteId, $transactionId);
	}
	
	/**
	 * @param Transaction $bill
	 * @return int
	 */
	function create(Transaction $bill)
	{
		$config = \CompetitionManager\Config::getInstance();
		$wService = new \Maniaplanet\WebServices\Payments();
		
		$t = new \Maniaplanet\WebServices\Transaction();
		$t->creatorLogin = $config->paymentLogin;
		$t->creatorPassword = $config->paymentPassword;
		$t->creatorSecurityKey = $config->paymentCode;
		$t->fromLogin = $bill->login;
		$t->toLogin = $config->paymentLogin;
		$t->cost = $bill->amount;
		$t->message = $bill->message;
		
		try
		{
			$bill->remoteId = $wService->create($t);
			return true;
		}
		catch(\Exception $e)
		{
			\ManiaLib\Application\ErrorHandling::logException($e);
			return false;
		}
	}
	
	/**
	 * @param Transaction $payment
	 * @return int
	 */
	function pay(Transaction $payment)
	{
		$config = \CompetitionManager\Config::getInstance();
		$wService = new \Maniaplanet\WebServices\Payments();
		
		$t = new \Maniaplanet\WebServices\Transaction();
		$t->creatorLogin = $config->paymentLogin;
		$t->creatorPassword = $config->paymentPassword;
		$t->creatorSecurityKey = $config->paymentCode;
		$t->fromLogin = $config->paymentLogin;
		$t->toLogin = $payment->login;
		$t->cost = $payment->amount;
		$t->message = $payment->message;
		
		try
		{
			$t->id = $payment->remoteId = $wService->create($t);
			$wService->pay($t);

			return $wService->isPaid($t->id);
		}
		catch(\Exception $e)
		{
			\ManiaLib\Application\ErrorHandling::logException($e);
			return false;
		}
	}
}

?>
