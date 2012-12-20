<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9157 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-20 15:47:38 +0100 (jeu., 20 déc. 2012) $:
 */

namespace CompetitionManager\Services\Rules;

abstract class AbstractRules
{
	private $id;
	private $settings;
	/** @var int */
	public $gameMode;
	/** @var int|null */
	public $maxSlots = null;
	
	static function GetList($title, $isTeam=false, $isLobby=false, $isOpen=false)
	{
		if($isTeam)
		{
			switch($title)
			{
//				case 'TMCanyon':
//					return array(new Team());
//				case 'SMStorm':
//					return array(new BattleWaves());
				case 'SMStormElite@nadeolabs':
					return array(new Elite());
//				case 'SMStormEliteExperimental@nadeolabs':
//					return array(new EliteExperimental());
//				case 'SMStormHeroes@nadeolabs':
//					return array(new Heroes());
			}
		}
		else if($isLobby)
		{
			switch($title)
			{
//				case 'TMCanyon':
//					return array(
//						new LobbyTMTimeAttack(),
//						new LobbyRounds()
//					);
				case 'SMStorm':
//				case 'SMStormElite@nadeolabs':
//				case 'SMStormEliteExperimental@nadeolabs':
//				case 'SMStormHeroes@nadeolabs':
				case 'SMStormJoust@nadeolabs':
					return array(
						new Melee(),
						new Royal(),
						new BattleWaves(),
						new LobbySMTimeAttack()
					);
			}
		}
		else if($isOpen)
		{
			switch($title)
			{
//				case 'TMCanyon':
//					return array(new AsynchronousTMTimeAttack());
//				case 'SMStorm':
//					return array(new AsynchronousSMTimeAttack());
			}
		}
		else
		{
			switch($title)
			{
//				case 'TMCanyon':
//					return array(
//						new CumulativeTMTimeAttack(),
//						new TMTimeAttackDuel(),
//						new CumulativeRounds(),
//						new RoundsDuel(),
//						new Cup()
//					);
				case 'SMStorm':
					return array(
						new Melee(),
						new Royal(),
//						new CumulativeSMTimeAttack(),
//						new SMTimeAttackDuel()
					);
				case 'SMStormJoust@nadeolabs':
					return array(new Joust());
			}
		}
		
		return array();
	}
	
	final function __construct()
	{
		$this->id = md5(get_class($this));
		
		$rc = new \ReflectionClass($this);
		$properties = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);
		foreach($properties as $property)
		{
			$doc = $property->getDocComment();
			if($doc && preg_match('/@setting\s+(.+?)\s+(.+)(?=\s+\*\/)/i', $doc, $matches))
			{
				$this->settings[$property->getName()] = array($matches[1] != 'none' ? $matches[1] : '', $matches[2]);
			}
		}
	}
	
	function validate()
	{
		$errors = array();
		foreach($this->settings as $setting => $description)
		{
			if($description[0] == 'scoring')
			{
				if(!(is_null($this->$setting) || $this->$setting instanceof \CompetitionManager\Services\Templates\Scoring))
				{
					$errors[] = sprintf(_('Invalid value for "%s"'), ucfirst(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $setting)));
				}
			}
			else if($description[0] == 'bool')
			{
				if(!is_bool($this->$setting))
				{
					$errors[] = sprintf(_('Invalid value for "%s"'), ucfirst(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $setting)));
				}
			}
			else if(!is_numeric($this->$setting))
			{
				$errors[] = sprintf(_('Invalid value for "%s"'), ucfirst(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $setting)));
			}
		}
		return $errors;
	}
	
	final function getId()
	{
		return $this->id;
	}
	
	final function getSettings()
	{
		return $this->settings;
	}
	
	function compare($scoreA, $scoreB)
	{
		return $scoreB - $scoreA;
	}
	
	abstract function getName();
	abstract function getInfo();
	abstract function getIcon();
	abstract function getTitle();
	
	function getTeamSize()
	{
		return 0;
	}
	
	function getDefaultDetails()
	{
		return new \CompetitionManager\Services\ScoreDetails\BasicDetails();
	}
}

?>
