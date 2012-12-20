<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9069 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-12-07 18:46:42 +0100 (ven., 07 déc. 2012) $:
 */

namespace CompetitionManager\Filters; 

use ManiaLib\Application\AdvancedFilter;
use ManiaLib\Application\Request;
use ManiaLib\Application\Session;
use ManiaLib\Application\View;
use CompetitionManager\Cards\Dialog;

class NextPageMessage extends AdvancedFilter
{
	function postFilter()
	{
		$type = $this->session->get('nextPageMessage:type', null);
		$isExternal = $this->session->get('nextPageMessage:isExternal', false);
		if($type !== null)
		{
			if($isExternal)
			{
				$this->response->resetViews();
				$this->response->disableDefaultViews();
			}
			$this->response->registerView(__NAMESPACE__.'\_NextPageMessageView');
			$this->response->nextPageMessage = (object) array(
				'type' => $type,
				'title' => $this->session->get('nextPageMessage:title'),
				'message' => $this->session->get('nextPageMessage:message'),
				'isExternal' => $isExternal,
				'redirection' => $this->session->get('nextPageMessage:redirection')
			);
		}
		$this->session->delete('nextPageMessage:type');
		$this->session->delete('nextPageMessage:title');
		$this->session->delete('nextPageMessage:message');
		$this->session->delete('nextPageMessage:isExternal');
		$this->session->delete('nextPageMessage:redirection');
	}
	
	static function setRedirection($url)
	{
		Session::getInstance()->set('nextPageMessage:redirection', $url);
	}

	static function error($message, $title=null)
	{
		self::dialog(Dialog::ERROR, $title ?: '$eee'._('Oops!'), $message);
	}

	static function warning($message, $title=null)
	{
		self::dialog(Dialog::WARNING, $title ?: '$eee'._('Warning!'), $message);
	}

	static function success($message, $title=null)
	{
		self::dialog(Dialog::SUCCESS, $title ?: '$eee'._('Success!'), $message);
	}
	
	private static function dialog($type, $title, $message)
	{
		$session = Session::getInstance();
		$session->set('nextPageMessage:type', $type);
		$session->set('nextPageMessage:title', $title);
		$session->set('nextPageMessage:message', $message);
		$session->set('nextPageMessage:isExternal', Request::getInstance()->get('external', false));
	}
}

class _NextPageMessageView extends View
{
	public function display()
	{
		$dialog = new Dialog();
		$dialog->setPosZ(15);
		$dialog->setType($this->response->nextPageMessage->type);
		$dialog->setTitle($this->response->nextPageMessage->title);
		$dialog->setContent($this->response->nextPageMessage->message);
		if($this->response->nextPageMessage->redirection)
			$dialog->addCustomAction(array(\ManiaLib\ManiaScript\Action::gotolink, $this->response->nextPageMessage->redirection));
		
		if($this->response->nextPageMessage->isExternal)
		{
			$dialog->setAsExternal();
			\ManiaLib\Gui\Manialink::load(true, 0, 1, 0);
		}
		
		$dialog->save();
		
		if($this->response->nextPageMessage->isExternal)
			\ManiaLib\Gui\Manialink::render();
	}

}

?>