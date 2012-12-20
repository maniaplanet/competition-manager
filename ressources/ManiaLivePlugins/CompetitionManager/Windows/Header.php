<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\CompetitionManager\Windows;

use ManiaLive\Event\Dispatcher;
use ManiaLive\DedicatedApi\Callback\Event as ServerEvent;
use ManiaLivePlugins\CompetitionManager\Constants;
use ManiaLivePlugins\CompetitionManager\Controls\HighlightedLabel;

class Header extends \ManiaLive\Gui\Window implements \ManiaLive\DedicatedApi\Callback\Listener
{
	/** @var HighlightedLabel */
	private $competition;
	/** @var HighlightedLabel */
	private $match;
	/** @var HighlightedLabel */
	private $name;
	/** @var HighlightedLabel */
	private $author;
	
	protected function onConstruct()
	{
		$this->setSize(320, 0);
		$this->setPosition(-160, 90);
		
		$matchObj = \ManiaLivePlugins\CompetitionManager\Services\Match::getInstance();
		$mapObj = \ManiaLive\Data\Storage::getInstance()->currentMap;
		
		$this->competition = new HighlightedLabel(60, 5);
		$this->competition->setPosition(0, -9);
		$this->competition->highlight->setBgcolorFocus('000a');
		$this->competition->highlight->setManialink($matchObj->stage->competition->getManialink());
		$this->competition->label->setPosX(1);
		$this->competition->label->setTextSize(3);
		$this->competition->label->setText($matchObj->stage->competition->name);
		$this->competition->setLabelMargin(1);
		$this->addComponent($this->competition);
		
		$this->match = new HighlightedLabel(45, 5);
		$this->match->setPosition(0, -(14+Constants\UI::PIXEL));
		$this->match->highlight->setBgcolorFocus('000a');
		$this->match->highlight->setManialink($matchObj->getManialink());
		$this->match->label->setPosX(1);
		$this->match->label->setTextSize(3);
		$this->match->label->setText($matchObj->name);
		$this->match->setLabelMargin(1);
		$this->addComponent($this->match);
		
		$this->name = new HighlightedLabel(40, 4);
		$this->name->setPosition(320-40);
		$this->name->label->setHalign('right');
		$this->name->label->setPosX(39);
		$this->name->label->setTextSize(3);
		$this->name->label->setText($mapObj->name);
		$this->name->setLabelMargin(1);
		$this->addComponent($this->name);
		
		$this->author = new HighlightedLabel(25, 4);
		$this->author->setPosition(320-25, -(4+Constants\UI::PIXEL));
		$this->author->label->setHalign('right');
		$this->author->label->setPosX(24);
		$this->author->label->setTextSize(3);
		$this->author->label->setText('by '.$mapObj->author);
		$this->author->setLabelMargin(1);
		$this->addComponent($this->author);
		
		Dispatcher::register(ServerEvent::getClass(), $this, ServerEvent::ON_BEGIN_MAP);
	}
	
	public function onBeginMap($map, $warmUp, $matchContinuation)
	{
		$this->name->label->setText($map['Name']);
		$this->author->label->setText('by '.$map['Author']);
		$this->redraw();
	}

	public function onBeginMatch() {}
	public function onBeginRound() {}
	public function onBillUpdated($billId, $state, $stateName, $transactionId) {}
	public function onEcho($internal, $public) {}
	public function onEndMap($rankings, $map, $wasWarmUp, $matchContinuesOnNextMap, $restartMap) {}
	public function onEndMatch($rankings, $winnerTeamOrMap) {}
	public function onEndRound() {}
	public function onManualFlowControlTransition($transition) {}
	public function onMapListModified($curMapIndex, $nextMapIndex, $isListModified) {}
	public function onModeScriptCallback($param1, $param2) {}
	public function onPlayerChat($playerUid, $login, $text, $isRegistredCmd) {}
	public function onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex) {}
	public function onPlayerConnect($login, $isSpectator) {}
	public function onPlayerDisconnect($login) {}
	public function onPlayerFinish($playerUid, $login, $timeOrScore) {}
	public function onPlayerIncoherence($playerUid, $login) {}
	public function onPlayerInfoChanged($playerInfo) {}
	public function onPlayerManialinkPageAnswer($playerUid, $login, $answer, array $entries) {}
	public function onServerStart() {}
	public function onServerStop() {}
	public function onStatusChanged($statusCode, $statusName) {}
	public function onTunnelDataReceived($playerUid, $login, $data) {}
	public function onVoteUpdated($stateName, $login, $cmdName, $cmdParam) {}
}

?>
