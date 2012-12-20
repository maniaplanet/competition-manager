<?php
use CompetitionManager\Services\Stages;
\CompetitionManager\WebUI\CSS::import('reminders');
?>
<div class="content-secondary">
	<div data-role="collapsible" data-collapsed="true" data-theme="e" data-content-theme="d">
		<h3><?php echo _('Reminders'); ?></h3>
		<ul data-role="listview" data-theme="c" data-divider-theme="a">
			<li data-role="list-divider"><?php echo _('General'); ?></li>
			<li><span style="font-weight:normal"><?php echo _('Name:'); ?></span> <?php echo \ManiaLib\Utils\StyleParser::toHtml($competition->name); ?></li>
			<li><span style="font-weight:normal"><?php echo _('Title:'); ?></span> <?php echo $competition->title; ?></li>
			<li data-role="list-divider" data-theme="d"><?php echo _('Restrictions'); ?></li>
			<li><span style="font-weight:normal"><?php echo _('Registration cost:'); ?></span> <?php printf(_('%d Planets'), $competition->registrationCost); ?></li>
		<?php foreach(array_slice($competition->stages, 0, $stageIndex) as $locStage): ?>
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
		<?php $currentAction = \ManiaLib\Application\Dispatcher::getInstance()->getAction(); ?>
		<?php if(!($stage instanceof Stages\Registrations) && $currentAction != 'chooseRules'): ?>
				<li data-role="list-divider"><?php echo $stage->getName(); ?></li>
			<?php
				$locStage = $stage;
				if($currentAction == 'chooseMaps')
					include __DIR__.'/Reminders/Rules.php';
				elseif($currentAction == 'chooseSchedule')
				{
					include __DIR__.'/Reminders/Rules.php';
					include __DIR__.'/Reminders/Maps.php';
				}
			?>
		<?php endif; ?>
		</ul>
	</div>
</div>