<?php
require __DIR__.'/../Header.php';
use CompetitionManager\WebUI\HTML;
$r = ManiaLib\Application\Request::getInstance();
?>
<div data-role="page">
	<?php echo CompetitionManager\Helpers\Header::save(); ?>
    <div class="ui-bar ui-bar-b">
		<h3><?php printf(_('Schedule stage #%d: %s'), $stageIndex, $stage->getName()); ?></h3><br/>
    </div>
	<?php echo DedicatedManager\Helpers\Box\Box::detect(); ?>
    <div data-role="content">
		<div class="content-primary">
			<form name="config" action="<?php echo $r->createLinkArgList('../set-schedule', 's'); ?>" method="post" data-ajax="false" data-role="collapsible-group">
				<fieldset data-role="collapsible" data-collapsed="false" data-theme="b">
				<?php if($stage->schedule instanceof \CompetitionManager\Services\Schedules\Simple): ?>
					<legend><?php echo reset($stage->getScheduleNames()); ?></legend>
					<ul data-role="listview">
						<li data-role="fieldcontain">
							<label for="startTime">
								<strong><?php echo _('Start at'); ?></strong><br/>
							</label>
							<input type="text" name="startTime" id="startTime" value="<?php echo $stage->schedule->startTime; ?>" data-role="datetime-picker" data-step-minutes="5"/>
						</li>
					</ul>
				<?php elseif($stage->schedule instanceof \CompetitionManager\Services\Schedules\Range): ?>
					<legend><?php echo reset($stage->getScheduleNames()); ?></legend>
					<ul data-role="listview">
						<li data-role="fieldcontain">
							<label for="startTime">
								<strong><?php echo _('From'); ?></strong><br/>
							</label>
							<input type="text" name="startTime" id="startTime" value="<?php echo $stage->schedule->startTime; ?>" data-role="datetime-picker" data-step-minutes="5"/>
						</li>
						<li data-role="fieldcontain">
							<label for="endTime">
								<strong><?php echo _('To'); ?></strong><br/>
							</label>
							<input type="text" name="endTime" id="endTime" value="<?php echo $stage->schedule->endTime; ?>" data-role="datetime-picker" data-step-minutes="5"/>
						</li>
					</ul>
				<?php elseif($stage->schedule instanceof \CompetitionManager\Services\Schedules\MultiSimple): ?>
					<?php $matchNames = $stage->getScheduleNames(); ?>
					<legend><?php echo _('Set start times'); ?></legend>
					<ul data-role="listview">
					<?php foreach($stage->schedule->startTimes as $i => $startTime): ?>
						<li data-role="fieldcontain">
							<label for="startTime-<?php echo $i; ?>">
								<strong><?php echo $matchNames[$i]; ?></strong><br/>
							</label>
							<input type="text" name="startTimes[]" id="startTime-<?php echo $i; ?>" value="<?php echo $startTime; ?>" data-role="datetime-picker" data-step-minutes="5"/>
						</li>
					<?php endforeach; ?>
					</ul>
				<?php endif ?>
				</fieldset>
				<div class="ui-grid-a">
					<div class="ui-block-a">
						<input type="reset" id="reset" value="<?php echo _('Restore'); ?>"/>
					</div>
					<div class="ui-block-b">
						<input type="submit" id="submit" value="<?php echo _('Next step'); ?>" data-theme="b"/>
					</div>
				</div>
			</form>
		</div>
		<?php include __DIR__.'/_Reminders.php' ?>
    </div>
</div>
<?php require __DIR__.'/../Footer.php' ?>
