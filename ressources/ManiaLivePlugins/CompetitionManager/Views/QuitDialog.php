<?php
/**
 * @version $Revision$:
 * @author $Author$:
 * @date $Date$:
 */

namespace ManiaLivePlugins\CompetitionManager\Views;

use ManiaLib\Gui\Manialink;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Icons128x128_Blink;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1;

class QuitDialog
{

	protected $displayedText;

	function __construct($displayedText)
	{
		$this->displayedText = $displayedText;
	}

	public function display()
	{
		Manialink::load();

		$frame = new \ManiaLib\Gui\Elements\Frame();
		$frame->setPosition(0, 5, 0);
		{
			$label = new Label(170);
			$label->setAlign('center', 'center2');
			$label->setStyle(Label::TextRaceMessageBig);
			$label->setTextSize(5);
			$label->setTextColor('f00');
			$label->setText($this->displayedText);
			$frame->add($label);

			$iconBlink = new Icons128x128_Blink(15);
			$iconBlink->setAlign('right', 'center');
			$iconBlink->setPosition(-87, 0);
			$iconBlink->setSubStyle(Icons128x128_Blink::Hard);
			$frame->add($iconBlink);

			$iconBlink = new Icons128x128_Blink(15);
			$iconBlink->setAlign('left', 'center');
			$iconBlink->setPosition(87, 0);
			$iconBlink->setSubStyle(Icons128x128_Blink::Hard);
			$frame->add($iconBlink);
		}
		$frame->save();

		return Manialink::render(true);
	}

}

?>