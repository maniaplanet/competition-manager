<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

use CompetitionManager\Services\Scores;

class AsynchronousTMTimeAttack extends TMTimeAttack
{
	/** @setting none Maximum number of "official" tries, or 0 for unlimited */
//	public $maxTries = 0;
	
	function getName()
	{
		return _('Asynchronous Time Attack');
	}
	
	function getInfo()
	{
		return _('Players come and play anytime with an optional maximum of tries');
	}
	
	function getDefaultScore()
	{
		return new Scores\Time();
	}
}

?>
