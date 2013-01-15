<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class RoundsDuel extends Rounds
{
	public $fixedSlots = 2;
	/** @setting none Rounds needed to win the map */
	public $roundsLimit = 5;
	/** @setting none Maps needed to win the match */
	public $mapsLimit = 2;
	/** @setting bool Allow or forbid respawn */
	public $disableRespawn = false;
	
	function getName()
	{
		return _('Rounds Duel');
	}
	
	function getInfo()
	{
		return _('1on1, first to "rounds limit" wins the map, first to "maps limit" wins the match');
	}
}

?>
