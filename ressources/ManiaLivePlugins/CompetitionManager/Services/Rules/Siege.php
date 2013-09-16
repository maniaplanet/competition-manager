<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services\Rules;

class Siege extends NadeoTeamScript
{
	public $name = 'Siege.Script.txt';
	public $fixedSlots = 2;
	public $slotsPerTeam = 5;
	public $timeLimit = 45;
	public $capturableLimit = 15;
	public $captureLimit = 5;
	public $roundsMax = 5;
	public $mapsLimit = 2;
	
	function getTeamSize()
	{
		return $this->slotsPerTeam;
	}
	
	function configure(\DedicatedApi\Connection $dedicated)
	{
		parent::configure($dedicated);
		
		$settings = $dedicated->getModeScriptSettings();
		$settings['S_TimeBetweenCapture'] = (int) $this->timeLimit;
		$settings['S_CaptureTimeLimit'] = (int) $this->capturableLimit;
		$settings['S_GoalCaptureTime'] = (float) $this->captureLimit;
		$settings['S_NbRoundMax'] = (int) $this->roundsMax;
		$dedicated->setModeScriptSettings($settings);
	}
}

?>
