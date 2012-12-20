<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9093 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-12 18:10:45 +0100 (mer., 12 déc. 2012) $:
 */

namespace CompetitionManager\Controllers;

use CompetitionManager\Filters\UserAgentAdapt;

class Home extends \ManiaLib\Application\Controller implements \ManiaLib\Application\Filterable
{
	protected function onConstruct()
	{
		$this->addFilter(new UserAgentAdapt(UserAgentAdapt::MANIAPLANET, '/manager'));
		$this->addFilter($this);
	}
	
	function preFilter()
	{
		try
		{
			$c = $this->request->get('c');
			if($c)
				$this->request->redirectArgList('/competition', 'c', 'external');

			$s = $this->request->get('s');
			if($s)
			{
				$service = new \CompetitionManager\Services\StageService();
				$stage = $service->get($s);
				$this->request->set('c', $stage->competitionId);
				$this->request->redirectArgList('/competition/'.$stage->getAction(), 'c', 's', 'external');
			}

			$m = $this->request->get('m');
			if($m)
			{
				$service = new \CompetitionManager\Services\MatchService();
				$match = $service->get($m);
				$service = new \CompetitionManager\Services\StageService();
				$stage = $service->get($match->stageId);
				$this->request->set('c', $stage->competitionId);
				$this->request->set('s', $match->stageId);
				$this->request->redirectArgList('/competition/'.$stage->getAction(), 'c', 's', 'm', 'external');
			}
		}
		catch(\Exception $e) {}
	}
	
	function postFilter() {}
	
	function index()
	{
		$service = new \CompetitionManager\Services\CompetitionService();
		$multipage = new \ManiaLib\Utils\MultipageList(5);
		$multipage->setSize($service->count());
		list($offset, $length) = $multipage->getLimit();
		$this->response->competitions = $service->getAll($offset, $length);
		$this->response->multipage = $multipage;
	}
}

?>
