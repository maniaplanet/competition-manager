<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

use CompetitionManager\Services\Scores;

class Melee extends Script
{
	public $name = 'Melee.Script.txt';
	/** @setting s How long the match can last at most */
	public $timeLimit = 600;
	/** @setting none Hits needed to win the match */
	public $hitsLimit = 25;
	
	function getName()
	{
		return _('Melee');
	}
	
	function getInfo()
	{
		return _('Classic free for all, first to "hits limit" wins the match');
	}
	
	function getTitle()
	{
		return 'SMStorm';
	}
	
	function getDefaultScore()
	{
		return new Scores\Points();
	}
}

?>
