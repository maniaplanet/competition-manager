<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

class Transaction extends AbstractObject
{
	const REGISTRATION        = 0x00;
	const SPONSOR             = 0x01;
	const REWARD              = 0x80;
	const REGISTRATION_REFUND = 0x81;
	const SPONSOR_REFUND      = 0x82;
	
	/** @var int */
	public $transactionId;
	/** @var int */
	public $remoteId;
	/** @var int */
	public $competitionId;
	/** @var string */
	public $login;
	/** @var int */
	public $amount;
	/** @var int */
	public $type;
	/** @var string */
	public $message;
	
	/**
	 * @return bool
	 */
	function isIncome()
	{
		return !($this->type & 0x80);
	}
	
	/**
	 * @return bool
	 */
	function isPaid()
	{
		return (bool) $this->remoteId;
	}
	
	/**
	 * @return int
	 */
	function getRefundType()
	{
		switch($this->type)
		{
			case self::REGISTRATION: return self::REGISTRATION_REFUND;
			case self::SPONSOR: return self::SPONSOR_REFUND;
		}
	}
}

?>
