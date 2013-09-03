<?php
require __DIR__.'/../Header.php';
use CompetitionManager\WebUI\HTML;
$r = ManiaLib\Application\Request::getInstance();
?>
<div data-role="page">
	<?php echo CompetitionManager\Helpers\Header::save(); ?>
	<?php echo DedicatedManager\Helpers\Box\Box::detect(); ?>
	<div data-role="content">
	<?php if($isAdmin): ?>
		<a href="<?php echo $r->createLinkArgList('/create'); ?>" data-ajax="false" data-role="button" data-icon="plus"><?php echo _('Create a new competition'); ?></a>
		<a href="<?php echo $r->createLinkArgList('/manager/servers'); ?>" data-ajax="false" data-role="button" data-icon="grid"><?php echo _('Servers'); ?></a>
		<a href="<?php echo $r->createLinkArgList('/manager/maps'); ?>" data-ajax="false" data-role="button" data-icon="grid"><?php echo _('Maps'); ?></a>
	<?php endif; ?>
		<ul data-role="listview" data-inset="true" data-x-filter="true" id="competitions-filter"></ul>
		<div data-role="collapsible-group">
			<div data-role="collapsible" data-collapsed="false" data-theme="b">
				<h3><?php echo _('Current competitions'); ?></h3>
			<?php if($currentCompetitions): ?>
				<ul data-role="listview" data-x-filter-id="competitions-filter">
				<?php foreach($currentCompetitions as $competition):
					$r->set('competition', $competition->competitionId); ?>
					<li>
						<a href="<?php echo HTML::encode($r->createLinkArgList('/edit', 'c')); ?>" data-ajax="false">
							<?php echo ManiaLib\Utils\StyleParser::toHtml($competition->name); ?>
						</a>
					</li>
				<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<p><?php echo _('No competitions are currently running'); ?></p>
			<?php endif; ?>
			</div>
			<div data-role="collapsible" data-theme="b">
				<h3><?php echo _('Upcoming competitions'); ?></h3>
			<?php if($upcomingCompetitions): ?>
				<ul data-role="listview" data-x-filter-id="competitions-filter">
				<?php foreach($upcomingCompetitions as $competition):
					$r->set('competition', $competition->competitionId); ?>
					<li>
						<a href="<?php echo HTML::encode($r->createLinkArgList('/edit', 'c')); ?>" data-ajax="false">
							<?php echo ManiaLib\Utils\StyleParser::toHtml($competition->name); ?>
						</a>
					</li>
				<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<p><?php echo _('No competitions are planned'); ?></p>
			<?php endif; ?>
			</div>
			<div data-role="collapsible" data-theme="b">
				<h3><?php echo _('Archives'); ?></h3>
			<?php if($finishedCompetitions): ?>
				<ul data-role="listview" data-x-filter-id="competitions-filter">
				<?php foreach($finishedCompetitions as $competition):
					$r->set('competition', $competition->competitionId); ?>
					<li>
						<a href="<?php echo HTML::encode($r->createLinkArgList('/edit', 'c')); ?>" data-ajax="false">
							<?php echo ManiaLib\Utils\StyleParser::toHtml($competition->name); ?>
						</a>
					</li>
				<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<p><?php echo _('No competitions are finished yet'); ?></p>
			<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php require __DIR__.'/../Footer.php' ?>