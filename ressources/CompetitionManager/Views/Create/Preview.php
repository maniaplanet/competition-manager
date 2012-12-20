<?php
require __DIR__.'/../Header.php';
use CompetitionManager\Services\Stages;
$r = ManiaLib\Application\Request::getInstance();
?>
<div data-role="page" id="content">
	<?php echo CompetitionManager\Helpers\Header::save(); ?>
    <div class="ui-bar ui-bar-b">
		<h3><?php echo _('Check that everything is correct'); ?></h3><br/>
    </div>
	<?php echo DedicatedManager\Helpers\Box\Box::detect(); ?>
    <div data-role="content">
		<ul data-role="listview" data-theme="c" data-divider-theme="a" data-inset="true">
			<li data-role="list-divider"><?php echo _('General'); ?></li>
			<li><span style="font-weight:normal"><?php echo _('Name:'); ?></span> <?php echo \ManiaLib\Utils\StyleParser::toHtml($competition->name); ?></li>
			<li><span style="font-weight:normal"><?php echo _('Title:'); ?></span> <?php echo $competition->title; ?></li>
			<li data-role="list-divider" data-theme="d"><?php echo _('Restrictions'); ?></li>
			<li><span style="font-weight:normal"><?php echo _('Registration cost:'); ?></span> <?php printf(_('%d Planets'), $competition->registrationCost); ?></li>
		<?php foreach($competition->stages as $locStage): ?>
			<li data-role="list-divider"><?php echo $locStage->getName(); ?></li>
			<?php
				if(!($locStage instanceof Stages\Registrations))
				{
					include __DIR__.'/Reminders/Rules.php';
					include __DIR__.'/Reminders/Maps.php';
				}
				if($competition->isScheduled())
					include __DIR__.'/Reminders/Schedule.php';
			?>
		<?php endforeach; ?>
		</ul>
		<a href="<?php echo $r->createLinkArgList('../do-create'); ?>" data-role="button" data-theme="b" data-ajax="false"><?php echo _('Create'); ?></a>
    </div>
</div>
<?php require __DIR__.'/../Footer.php' ?>
