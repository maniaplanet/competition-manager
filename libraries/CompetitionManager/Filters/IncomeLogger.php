<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Filters;

class IncomeLogger extends \ManiaLib\Application\AdvancedFilter
{
	const EXPECTED_KEY = 'transaction:expected';
	static $expected = null;
	static $isPaid = false;
	
	function preFilter()
	{
		self::$expected = $this->session->get(self::EXPECTED_KEY);
		if(!self::$expected)
			return;
		
		$transactionId = $this->request->get('transaction');
		if($transactionId == self::$expected->remoteId)
		{
			$wService = new \Maniaplanet\WebServices\Payments();
			self::$isPaid = $wService->isPaid($transactionId);
			if(self::$isPaid)
			{
				$service = new \CompetitionManager\Services\TransactionService();
				$service->registerIncome(self::$expected);
			}
		}
		$this->request->delete('transaction');
		$this->session->delete(self::EXPECTED_KEY);
	}
}

?>
