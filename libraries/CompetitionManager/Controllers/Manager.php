<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9040 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-04 13:05:22 +0100 (mar., 04 déc. 2012) $:
 */

namespace CompetitionManager\Controllers;

use DedicatedManager\Utils\GbxReader\Map;
use CompetitionManager\Filters\UserAgentAdapt;
use CompetitionManager\Services\CompetitionService;
use CompetitionManager\Services\MapService;

class Manager extends \DedicatedManager\Controllers\AbstractController 
{
	function preFilter()
	{
		parent::preFilter();
		
		$config = \CompetitionManager\Config::getInstance();
		$currentDir = getcwd();
		
		$errors = array();
		$writables[] = $config->dedicatedPath;
		$writables[] = $config->dedicatedPath.'Logs/';
		$writables[] = $config->dedicatedPath.'UserData/Config/';
		$writables[] = $config->dedicatedPath.'UserData/Maps/MatchSettings/';
		if(file_exists($config->dedicatedPath.'UserData/Maps/MatchSettings/'))
		{
			chdir($config->dedicatedPath.'UserData/Maps/MatchSettings/');
			$tmp = glob('*.[tT][xX][tT]');
			$tmp = array_map(function ($f) use ($config) { return $config->dedicatedPath.'UserData/Maps/MatchSettings/'.$f; }, $tmp);
			$writables = array_merge($writables, $tmp);
		}
		if(file_exists($config->dedicatedPath.'UserData/Config/'))
		{
			chdir($config->dedicatedPath.'UserData/Config/');
			$tmp = glob('*.[tT][xX][tT]');
			$tmp = array_map(function ($f) use ($config) { return $config->dedicatedPath.'UserData/Config/'.$f; }, $tmp);
			$writables = array_merge($writables, $tmp);
		}
		
		$writables[] = $config->manialivePath;
		$writables[] = $config->manialivePath.'logs/';
		$writables[] = $config->manialivePath.'data/';
		chdir($currentDir);

		$executables[] = stripos(PHP_OS, 'win') !== false ? $config->dedicatedPath.'ManiaPlanetServer.exe' : $config->dedicatedPath.'ManiaPlanetServer';
		
		$failed = array_filter(array_merge($writables, $executables), function ($f) { return !file_exists($f); });
		if($failed)
		{
			$errors[] = _('The following files does not exist.').' '._('Contact the admin to check this.').'<br/>'.
					_('File list: ').'<ul>'.implode('', array_map(function ($f) { return '<li>'.$f.'</li>'; }, $failed)).'</ul>';
		}

		$failed = array_filter($writables, function ($f) { return file_exists($f) && !is_writable($f); });
		if($failed)
		{
			$errors[] = _('The following folders cannot be written by Apache user.').' '._('Contact the admin to check this.').'<br/>'.
					_('Folder list: ').'<ul>'.implode('', array_map(function ($f) { return '<li>'.$f.'</li>'; }, $failed)).'</ul>';
		}

		$failed = array_filter($executables, function ($f) { return file_exists($f) && !is_executable($f); });
		if($failed)
		{
			$errors[] = _('The following files cannot be executed by Apache user.').' '._('Contact the admin to check this.').'<br/>'.
					_('File list: ').'<ul>'.implode('', array_map(function ($f) { return '<li>'.$f.'</li>'; }, $failed)).'</ul>';
		}
		
		$service = new \CompetitionManager\Services\CronService();
		if(!$service->isRunning())
		{
			$service->start();
			sleep(3);
			if (!$service->isRunning())
			{
				$errors[] = 'Cron tried to start... but failed. Check logs folder.';
			}
			else
			{
				$this->session->set('warning', _('Cron was not running, it has been restarted but current competitions may have some delay.'));
			}
		}

		if($errors)
		{
			$this->session->set('error', $errors);
		}
	}
	
	protected function onConstruct()
	{
		parent::onConstruct();
		
		$this->addFilter(new UserAgentAdapt(UserAgentAdapt::WEB_BROWSER));
	}
	
	function index()
	{
		$this->request->registerReferer();
		
		$header = \CompetitionManager\Helpers\Header::getInstance();
		$header->leftLink = null;
		
		$service = new CompetitionService();
		$this->response->currentCompetitions = $service->getCurrent();
		$this->response->upcomingCompetitions = $service->getUpcoming();
		$this->response->finishedCompetitions = $service->getArchived();
	}
	
	////////////////////////////////////////////////////////////////////////////
	// Competitions methods
	////////////////////////////////////////////////////////////////////////////
	
	function cancelCompetition($competitionId)
	{
	}
	
	////////////////////////////////////////////////////////////////////////////
	// System methods
	////////////////////////////////////////////////////////////////////////////
	
	function servers()
	{
		$service = new \CompetitionManager\Services\ServerService();
		$this->response->runningServers = $service->getLives();
		$this->response->dedicatedAccounts = $service->getAllAccounts();
	}
	
	function addServerAccount()
	{
		$login = $this->request->getPost('login');
		$password = $this->request->getPost('password');
		if(!preg_match('/^[a-z0-9\._-]{1,25}$/i', $login))
		{
			$this->session->set('error', _('Invalid login.'));
		}
		else
		{
			$service = new \CompetitionManager\Services\ServerService();
			$service->addAccount($login, $password);
		}
		$this->request->redirectArgList('../servers');
	}
	
	function removeServerAccounts()
	{
		$logins = $this->request->getPost('logins');
		if(!$logins)
		{
			$this->session->set('error', _('You have to select at least one login.'));
		}
		else
		{
			$service = new \CompetitionManager\Services\ServerService();
			$service->removeAccounts($logins);
		}
		$this->request->redirectArgList('../servers');
	}
	
	function maps($path = '')
	{
		$service = new MapService();
		$files = $service->getList($path);
		usort($files,
			function (\DedicatedManager\Services\File $a, \DedicatedManager\Services\File $b)
			{
				$order = $b->isDirectory - $a->isDirectory;
				if(!$order)
				{
					$order = strcmp($a->filename, $b->filename);
				}
				return $order;
			}
		);

		$this->response->path = $path;
		$this->response->parentPath = preg_replace('/([^\\/]*\\/)$/ixu', '', $path);
		$this->response->files = $files;
	}

	function uploadMap()
	{
		$path = $this->request->getPost('path');
		
		if($path === null)
		{
			$this->session->set('error', _('The path must be set.'));
			$this->request->redirect('../maps');
		}
		$this->request->set('path', $path);
		
		if($_FILES['map']['error'])
		{
			switch($_FILES['map']['error'])
			{
				case UPLOAD_ERR_INI_SIZE:
					$this->session->set('error', _('File is too big.'));
					break;
				case UPLOAD_ERR_PARTIAL:
					$this->session->set('error', _('File is partially uploaded.'));
					break;
				case UPLOAD_ERR_NO_FILE:
					$this->session->set('error', _('No file uploaded.'));
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$this->session->set('error', _('Can\'t write the file on the disk.'));
					break;
			}
			$this->request->redirect('../maps', 'path');
		}

		if(!preg_match('/\.map\.gbx$/ui', $_FILES['map']['name']) || !Map::check($_FILES['map']['tmp_name']))
		{
			$this->session->set('error', _('The file must be a ManiaPlanet map.'));
			$this->request->redirect('../maps', 'path');
		}
		
		$service = new \CompetitionManager\Services\MapService();
		$service->upload($_FILES['map']['tmp_name'], $_FILES['map']['name'], $path);
		$this->request->redirectArgList('../maps', 'path');
	}

	function deleteMaps()
	{
		$maps = $this->request->getPost('maps');
		if(!$maps)
		{
			$this->session->set('error',_('You have to select at least one map.'));
			$this->request->redirect('../maps', 'path');
		}
		$service = new \CompetitionManager\Services\MapService();
		$service->delete($maps);
		
		$this->request->set('path', $this->request->getPost('path', ''));
		$this->request->redirectArgList('../maps/', 'path');
	}
}

?>
