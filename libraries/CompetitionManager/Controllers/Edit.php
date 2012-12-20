<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace CompetitionManager\Controllers;

use CompetitionManager\Filters\UserAgentAdapt;

class Edit extends \DedicatedManager\Controllers\AbstractController
{
	protected function onConstruct()
	{
		parent::onConstruct();
		
		$this->addFilter(new UserAgentAdapt(UserAgentAdapt::WEB_BROWSER));
	}
	
	function index()
	{
		
	}
}

?>
