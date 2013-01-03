<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class LobbyJoust extends Script
{
	public $name = 'Joust.Script.txt';
	
	function getName()
	{
		return _('Joust');
	}
	
	function getInfo()
	{
		$info[] = _('Joust lobby mode');
		return $info;
	}
	
	function getTitle()
	{
		return 'SMStormJoust@nadeolabs';
	}
}

?>
