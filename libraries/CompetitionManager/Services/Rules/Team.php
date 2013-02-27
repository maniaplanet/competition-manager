<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

use DedicatedApi\Structures\GameInfos;
use ManiaLib\Gui\Elements\Icon;
use ManiaLib\Gui\Elements\Icons128x32_1;
use CompetitionManager\Services\Scores;

class Team extends \CompetitionManager\Services\Rules
{
	public $gameMode = GameInfos::GAMEMODE_TEAM;
	public $fixedSlots = 2;
	/** @setting none Max players per team */
	public $slotsPerTeam = 3;
	/** @setting ms Time limit after the first cross the line (0 to disable, 1 for automatic) */
	public $finishTimeLimit = 1;
	/** @setting none Rounds needed to win the map */
	public $roundsLimit = 7;
	/** @setting none Maps needed to win the match */
	public $mapsLimit = 2;
	/** @setting bool Allow or forbid respawn */
	public $disableRespawn = false;
	private $title;
	
	function __construct($title)
	{
		$this->title = $title;
	}
	
	function getName()
	{
		return _('Team');
	}
	
	function getInfo()
	{
		return _('Team with most points wins the round, first to "rounds limit" wins the map, first to "maps limit" wins the match');
	}
	
	function getIcon()
	{
		return array(Icon::Icons128x32_1, Icons128x32_1::RT_Team);
	}
	
	function getTitle()
	{
		return $this->title;
	}
	
	function getDefaultScore()
	{
		$score = new Scores\Detailed();
		$score->main = new Scores\Points();
		return $score;
	}
}

?>
