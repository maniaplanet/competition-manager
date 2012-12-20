<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class Joust extends Script
{
	public $name = 'Joust.Script.txt';
	public $maxSlots = 2;
	/** @setting s How long a round can lasts at most */
	public $timeLimit = 300;
	/** @setting none Hits needed to win the round */
	public $hitsLimit = 7;
	/** @setting none Gap needed between players */
	public $hitsGap = 2;
	/** @setting none Max hits */
	public $hitsMax = 11;
	/** @setting none Rounds needed to win the map */
	public $roundsLimit = 3;
	/** @setting none Maps needed to win the match */
	public $mapsLimit = 2;
	
	function getName()
	{
		return _('Joust');
	}
	
	function getInfo()
	{
		$info[] = _('Joust mode');
		$info[] = sprintf(ngettext('Best of %d map', 'Best of %d maps', $this->mapsLimit), $this->mapsLimit*2-1);
		$info[] = sprintf(ngettext('Best of %d round on each map', 'Best of %d rounds on each map', $this->roundsLimit), $this->roundsLimit*2-1);
		$info[] = sprintf(_('%d hits to win the round, with a gap of %d and a limit of %d'), $this->hitsLimit, $this->hitsGap, $this->hitsMax);
		return $info;
	}
	
	function getTitle()
	{
		return 'SMStormJoust@nadeolabs';
	}
}

?>
