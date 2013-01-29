<?php
/**
 * @copyright   Copyright (c) 2009-2013 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Services\Stages;

interface IntermediateCompliant
{
	/**
	 * @param int $rank
	 * @param int $max
	 * @return string
	 */
	function getPlaceholder($rank, $max);
}

?>
