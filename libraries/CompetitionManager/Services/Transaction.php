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
	const REGISTRATION        = 0x01;
	const SPONSOR             = 0x02;
	const REWARD              = 0x80;
	const REFUND              = 0x80;
	
	/** @var int */
	public $transactionId;
	/** @var int */
	public $remoteId;
	/** @var int */
	public $competitionId;
	/** @var string */
	public $login;
	/** @var int */
	public $teamId;
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
		return !($this->type & self::REFUND);
	}
	
	/**
	 * @return bool
	 */
	function isPaid()
	{
		return (bool) $this->remoteId;
	}
}

?>
