<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class CumulativeTMTimeAttack extends TMTimeAttack
{
	/** @setting ms Map duration */
	public $timeLimit = 300000;
	
	function getName()
	{
		return _('Cumulative Time Attack');
	}
	
	function getInfo()
	{
		return _('Results are accumulated between maps');
	}
	
	function getDefaultDetails()
	{
		$details = new \CompetitionManager\Services\ScoreDetails\MapsCount();
		$details->isTime = true;
		return $details;
	}
}

?>
