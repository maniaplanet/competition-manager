<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Templates;

class Rewards extends \CompetitionManager\Services\Template
{
	public $rules = array();
	
	function getReleventRules($nbParticipants)
	{
		return array_filter($this->rules, function ($r) use ($nbParticipants) {
			return (!$r->min || $nbParticipants >= $r->min) && (!$r->max || $nbParticipants <= $r->max);
		});
	}
	
	function _json_sleep()
	{
		$this->rules = array_map('\CompetitionManager\Services\JSON::serialize', $this->rules);
	}
	
	function _json_wakeup()
	{
		$this->rules = array_map('\CompetitionManager\Services\JSON::unserialize', $this->rules);
	}
	
	/**
	 * @param string $filename
	 * @return Rewards
	 */
	static function read($filename)
	{
		self::validate($filename);
		
		$xml = simplexml_load_file($filename);
		$obj = new self();
		$obj->name = self::readName($filename);
		$obj->description = (string) $xml->description;
		$obj->rules = self::extractRules($xml->children());
		
		return $obj;
	}
	
	private static function extractRules($tags, $min=null, $max=null)
	{
		$rules = array();
		foreach($tags as $tag)
		{
			if($tag->getName() == 'position')
			{
				if($tag->attributes()->is)
				{
					$rule = new IsRule();
					$rule->is = (int) $tag->attributes()->is;
				}
				else
				{
					$rule = new RangeRule();
					$rule->from = self::extractPercentageOrInteger((string) $tag->attributes()->from);
					$rule->to = self::extractPercentageOrInteger((string) $tag->attributes()->to);
				}
				$rule->planets = self::extractPercentageOrInteger((string) $tag->attributes()->planets);
				$rule->min = (int) $min;
				$rule->max = (int) $max;

				$rules[] = $rule;
			}
			else if($tag->getName() == 'registrations')
			{
				$newMin = $tag->attributes()->atLeast ?: $min;
				$newMax = $tag->attributes()->atMost ?: $max;
				array_splice($rules, count($rules), 0, self::extractRules($tag->children(), (int) $newMin, (int) $newMax));
			}
		}
		
		return $rules;
	}
	
	private static function extractPercentageOrInteger($value)
	{
		if(!$value)
			return null;
		if(strpos($value, '%'))
			return (int) $value * -1;
		return (int) $value;
	}
}

abstract class Rule
{
	public $planets;
	public $min;
	public $max;
	
	abstract function getReleventParticipants($participants);
	
	function getPlanetsByParticipants($pool, $nbParticipants)
	{
		return intval(($this->planets < 0 ? $pool * abs($this->planets) / 100 : $this->planets) / $nbParticipants);
	}
}

class IsRule extends Rule
{
	public $is;
	
	function getReleventParticipants($participants)
	{
		$toReward = array();
		$rank = $this->is;
		while($rank > 0 && !$toReward)
		{
			$toReward = array_filter($participants, function ($p) use ($rank) {
				return $p->rank == $rank;
			});
			--$rank;
		}
		
		return $toReward;
	}
}

class RangeRule extends Rule
{
	public $from;
	public $to;
	
	function getReleventParticipants($participants)
	{
		$from = $this->from < 0 ? intval(count($participants) * abs($this->from) / 100) : $this->from;
		$to = $this->to < 0 ? intval(count($participants) * abs($this->to) / 100) : $this->to;
		
		return array_filter($participants, function ($p) use ($from, $to) {
			return (!$from || $p->rank >= $from) && (!$to || $p->rank <= $to);
		});
	}
}

?>
