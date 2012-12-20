<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class LobbyTMTimeAttack extends TMTimeAttack
{
	/** @setting ms Map duration */
	public $timeLimit = 300000;
	
	function getName()
	{
		return _('Time-Attack');
	}
	
	function getInfo()
	{
		return _('Classic Time-Attack');
	}
	
	function getDefaultDetails()
	{
		return null;
	}
}

?>
