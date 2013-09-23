<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9104 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-13 15:01:15 +0100 (jeu., 13 déc. 2012) $:
 */

namespace CompetitionManager;

/**
 * @method \CompetitionManager\Config getInstance()
 **/
class Config extends \DedicatedManager\Config 
{
	public $manialinkName;
	
	public $postToManiaHome = false;
	
	public $paymentLogin;
	public $paymentPassword;
	public $paymentCode;
	
	public $filesGroup;
	
	protected function __construct()
	{
		$this->maniaConnect = true;
		// FIXME: find something better than that hack
		self::$instances['DedicatedManager\Config'] = $this;
	}
	
	function arePaymentsConfigured()
	{
		return \ManiaLib\Application\Config::getInstance()->manialink
				&& $this->paymentLogin
				&& $this->paymentPassword
				&& $this->paymentCode
				&& strlen($this->paymentPassword) <= 20
				&& preg_match('/^[a-z0-9\._-]{1,25}$/i', $this->paymentLogin)
				&& preg_match('/^[A-Z]{5}$/', $this->paymentCode);
	}
}

?>
