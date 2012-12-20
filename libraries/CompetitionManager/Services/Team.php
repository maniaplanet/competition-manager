<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services;

class Team extends Participant
{
	/** @var int */
	public $teamId;
	/** @var string */
	public $tag;
	/** @var string */
	public $name;
	/** @var string */
	public $path;
	/** @var string */
	public $city;
	/** @var string[] */
	public $players;
	
	protected function onFetchObject()
	{
		parent::onFetchObject();
		$this->players = explode(',', $this->players);
	}
	
	function updatePlayers()
	{
		$service = new ParticipantService();
		$this->players = WebServicesProxy::getTeamPlayers($this->teamId);
		$service->updateTeamPlayers($this->teamId, $this->players);
	}
}

?>
