<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

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
	/** @var bool[string] */
	public $players = array();
	
	protected function onFetchObject()
	{
		parent::onFetchObject();
		$this->players = array_fill_keys(explode(',', $this->players), false);
	}
	
	function getPresent()
	{
		return array_keys(array_filter($this->players));
	}
}

?>
