<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Constants;

abstract class UI
{
	const PIXEL = .166666667;
	
	const MATCH_WIDTH = 45;
	const GROUP_WIDTH = 60;
	const MEDIUM_WIDTH = 100;
	const EVENT_WIDTH = 100;
	const DIALOG_WIDTH = 120;
	const STANDARD_WIDTH = 150;
	
	const EVENT_HEIGHT = 8;
	const TITLE_HEIGHT = 12;
	const DIALOG_HEIGHT = 45;
	const ACCORDION_HEIGHT = 140;
	
	static function STATE_COLOR($state, $focus=false)
	{
		static $colors = array(
			State::UNKNOWN   => '044',
			State::READY     => '088',
			State::STARTED   => '08f',
			State::OVER      => '00f',
			State::ARCHIVED  => '008',
			State::CANCELLED => '000',
		);
		return $colors[$state].($focus ? '9' : '5');
	}
}

?>
