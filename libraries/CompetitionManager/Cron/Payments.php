<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Cron;

class Payments extends Cron
{
	protected $logName = 'payments';
	
	protected function onRun()
	{
		$this->head('Paying Transactions');
		
		$service = new \CompetitionManager\Services\TransactionService();
		
		$this->debug('Looping through pending transactions...');
		$this->beginSection();
		foreach($service->getAllPending() as $transaction)
		{
			if($service->pay($transaction))
			{
				$this->debug(sprintf('Payed %d Planets to login `%s`', $transaction->amount, $transaction->login));
				$service->setOutcomePaid($transaction->transactionId, $transaction->remoteId);
			}
			else
				$this->debug(sprintf('Failed to pay %d Planets to login `%s`...', $transaction->amount, $transaction->login));
		}
		$this->endSection();
		$this->foot('Paying Transactions Done!');
	}
}

?>
