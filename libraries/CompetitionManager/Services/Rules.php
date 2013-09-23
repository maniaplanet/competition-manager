<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9157 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-20 15:47:38 +0100 (jeu., 20 déc. 2012) $:
 */

namespace CompetitionManager\Services;

abstract class Rules
{
	private $id;
	private $settings = array();
	/** @var int */
	public $gameMode;
	/** @var int|null */
	public $fixedSlots = null;
	
	static function GetList($title, $isTeam=false, $isLobby=false, $isOpen=false)
	{
		if($isTeam)
		{
			switch($title)
			{
//				case 'TMCanyon':
//				case 'TMStadium':
//					return array(new Rules\Team($title));
				case 'SMStorm':
					return array(
						new Rules\BattleWaves(),
						new Rules\Siege(),
					);
				case 'SMStormElite@nadeolabs':
					return array(new Rules\Elite());
//				case 'SMStormHeroes@nadeolabs':
//					return array(new Rules\Heroes());
			}
		}
		else if($isLobby)
		{
			switch($title)
			{
				case 'TMCanyon':
				case 'TMStadium':
					return array(
						new Rules\LobbyTMTimeAttack($title),
						new Rules\LobbyRounds($title)
					);
				case 'SMStorm':
//				case 'SMStormElite@nadeolabs':
//				case 'SMStormHeroes@nadeolabs':
					return array(
						new Rules\Melee(),
						new Rules\BattleWaves(),
						new Rules\LobbySMTimeAttack()
					);
				case 'SMStormRoyal@nadeolabs':
					return array(new Rules\Royal());
				case 'SMStormJoust@nadeolabs':
					return array(new Rules\LobbyJoust());
			}
		}
		else if($isOpen)
		{
			switch($title)
			{
//				case 'TMCanyon':
//				case 'TMStadium':
//					return array(new Rules\AsynchronousTMTimeAttack($title));
//				case 'SMStorm':
//					return array(new Rules\AsynchronousSMTimeAttack());
			}
		}
		else
		{
			switch($title)
			{
				case 'TMValley':
				case 'TMCanyon':
				case 'TMStadium':
					return array(
//						new Rules\CumulativeTMTimeAttack($title),
//						new Rules\TMTimeAttackDuel($title),
//						new Rules\CumulativeRounds($title),
//						new Rules\RoundsDuel($title),
						new Rules\Laps($title),
//						new Rules\Cup($title)
					);
				case 'SMStorm':
					return array(
						new Rules\Melee(),
//						new Rules\CumulativeSMTimeAttack(),
//						new Rules\SMTimeAttackDuel()
					);
				case 'SMStormRoyal@nadeolabs':
					return array(new Rules\Royal());
				case 'SMStormJoust@nadeolabs':
					return array(new Rules\Joust());
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
	
	function getDefaultScore()
	{
		return new Scores\None();
	}
}

?>
