<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 8508 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-10-15 15:18:28 +0200 (lun., 15 oct. 2012) $:
 */

namespace CompetitionManager\Filters;

class UserAgentAdapt extends \ManiaLib\Application\AdvancedFilter
{
	const EITHER      = 0;
	const MANIAPLANET = 1;
	const WEB_BROWSER = 2;
	
	private $forceUserAgent;
	private $routeRedirection;
	
	function __construct($forceUserAgent = self::MANIAPLANET, $routeRedirection = null)
	{
		parent::__construct();
		$this->forceUserAgent = $forceUserAgent;
		$this->routeRedirection = $routeRedirection;
	}
	
	static function isManiaplanet()
	{
		return substr(\ManiaLib\Utils\Arrays::get($_SERVER, 'HTTP_USER_AGENT'), 0, 11) == 'ManiaPlanet';
	}
	
	function preFilter()
	{
		$userAgentOk = true;
		
		if(!self::isManiaplanet())
		{
			$this->response->setRenderer('\ManiaLib\Application\Rendering\SimpleTemplates');
			$userAgentOk = $this->forceUserAgent != self::MANIAPLANET;
		}
		else
			$userAgentOk = $this->forceUserAgent != self::WEB_BROWSER;
		
		if(!$userAgentOk)
		{
			if($this->routeRedirection)
				$this->request->redirect($this->routeRedirection);
			else
			{
				$this->response->disableDefaultViews();
				$this->response->resetViews();
				$this->response->registerView('\CompetitionManager\Views\WrongUserAgent');
				$this->response->render();
			}
			exit;
		}
	}
}

?>
