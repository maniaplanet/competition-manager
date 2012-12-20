<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9107 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-13 15:32:32 +0100 (jeu., 13 déc. 2012) $:
 */

namespace ManiaLivePlugins\CompetitionManager\Services;

class Competition extends AbstractObject
{
	/** @var int */
	public $competitionId;
	/** @var string */
	public $name;
	/** @var string */
	public $title;
	/** @var int */
	public $lobbyId;
	/** @var bool */
	public $isLan;
	/** @var bool */
	public $isTeam;
	/** @var int */
	public $teamSize;
	/** @var int */
	public $registrationCost;
	/** @var int */
	public $planetsPool;
	/** @var int */
	public $state;
	
	function getManialink($page=null, $external=true, $params=array())
	{
		$params += array(
			'c' => $this->competitionId,
			'external' => $external,
			'ml-forcepathinfo' => $page ? '/competition/'.$page : null
		);
		return \ManiaLivePlugins\CompetitionManager\Config::getInstance()->manialink.'?'.http_build_query($params);
	}
}

?>
