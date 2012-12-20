<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Helpers;

class Header extends \DedicatedManager\Helpers\Header
{
	function __construct()
	{
		parent::__construct();
		$this->title = 'Competition Manager '.(defined('COMPETITION_MANAGER_VERSION') ? COMPETITION_MANAGER_VERSION : '&lt;no version&gt;');
	}
}

?>
