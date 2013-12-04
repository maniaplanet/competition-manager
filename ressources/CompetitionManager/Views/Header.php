<?php
use CompetitionManager\WebUI\CSS;
use CompetitionManager\WebUI\JS;
use CompetitionManager\WebUI\Meta;
use CompetitionManager\WebUI\HTML;

HTML::doctype();
HTML::begin();
	HTML::beginHead();
		Meta::contentType();
		HTML::title('ManiaPlanet Competition Manager');
		CSS::jQueryMobile();
		CSS::import('jqm-datebox');
		CSS::import('jqm-huepicker');
		CSS::import('jqm-maniaplanetStyle');
		CSS::import('competition');
		JS::jQueryMobile();
		JS::import('jqm-datebox');
		JS::import('jqm-collapsibleGroup');
		JS::import('jqm-xFilter');
		JS::import('jqm-treeSelector');
		JS::import('jqm-selectorSorter');
		JS::import('jqm-huepicker');
		JS::import('mp-style-parser');
		JS::import('jqm-maniaplanetStyle');
		JS::import('competition');
	HTML::endHead();
	HTML::beginBody();
?>