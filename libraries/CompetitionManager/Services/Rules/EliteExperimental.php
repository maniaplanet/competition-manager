<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Rules;

class EliteExperimental extends Elite
{
	function getName()
	{
		return _('Elite (experimental)');
	}
	
	function getTitle()
	{
		return 'SMStormEliteExperimental@nadeolabs';
	}
}

?>