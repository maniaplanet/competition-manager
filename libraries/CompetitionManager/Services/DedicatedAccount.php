<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

class DedicatedAccount extends AbstractObject
{
	/** @var string */
	public $login;
	/** @var string */
	public $password;
	/** @var string */
	public $rpcHost;
	/** @var int */
	public $rpcPort;
	
	protected function onFetchObject()
	{
		$this->password = ServerService::decrypt($this->password);
	}
}

?>
