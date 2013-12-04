<?php
require __DIR__.'/../Header.php';
use CompetitionManager\Services\Stages;
use CompetitionManager\Services\Schedules;
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
				<?php if($stage->schedule instanceof Schedules\Simple): ?>
					<legend><?php echo reset($stage->getScheduleNames()); ?></legend>
					<ul data-role="listview">
						<li data-role="fieldcontain">
							<label for="startDate">
								<strong><?php echo _('Start at'); ?></strong><br/>
							</label>
							<input type="text" name="startDate" id="startDate" value="<?php echo \CompetitionManager\Utils\Date::getDate($stage->schedule->startTime); ?>" data-role="datebox" data-options='{"mode":"calbox", "useFocus": true, "closeCallback":"autoOpenTime", "closeCallbackArgs":["startDate"], "afterToday": true}'/>
						</li>
						<li data-role="fieldcontain">
							<label for="startTime">
								<strong><?php echo _('Start at (time)'); ?></strong><br/>
							</label>
							<input type="text" name="startTime" id="startTime" value="<?php echo \CompetitionManager\Utils\Date::getTime($stage->schedule->startTime); ?>" data-role="datebox" data-options='{"mode":"timebox", "useFocus": true}'/>
						</li>
					</ul>
				<?php elseif($stage->schedule instanceof Schedules\Range): ?>
					<legend><?php echo reset($stage->getScheduleNames()); ?></legend>
					<ul data-role="listview">
						<li data-role="fieldcontain">
							<label for="startDate">
								<strong><?php echo _('From'); ?></strong><br/>
							</label>
							<input type="text" name="startDate" id="startDate" value="<?php echo \CompetitionManager\Utils\Date::getDate($stage->schedule->startTime); ?>" data-role="datebox" data-options='{"mode":"calbox", "useFocus": true, "closeCallback":"autoOpenTime", "closeCallbackArgs":["startDate"], "afterToday": true}'/>
						</li>
						<li data-role="fieldcontain">
							<label for="startTime">
								<strong><?php echo _('From (time)'); ?></strong><br/>
							</label>
							<input type="text" name="startTime" id="startTime" value="<?php echo \CompetitionManager\Utils\Date::getTime($stage->schedule->startTime); ?>" data-role="datebox" data-options='{"mode":"timebox", "useFocus": true}'/>
						</li>
						<li data-role="fieldcontain">
							<label for="endDate">
								<strong><?php echo _('End at'); ?></strong><br/>
							</label>
							<input type="text" name="endDate" id="endDate" value="<?php echo \CompetitionManager\Utils\Date::getDate($stage->schedule->endTime); ?>" data-role="datebox" data-options='{"mode":"calbox", "useFocus": true, "closeCallback":"autoOpenTime", "closeCallbackArgs":["endDate"], "afterToday": true}'/>
						</li>
						<li data-role="fieldcontain">
							<label for="endTime">
								<strong><?php echo _('End at (time)'); ?></strong><br/>
							</label>
							<input type="text" name="endTime" id="endTime" value="<?php echo \CompetitionManager\Utils\Date::getTime($stage->schedule->endTime); ?>" data-role="datebox" data-options='{"mode":"timebox", "useFocus": true}'/>
						</li>
					<?php if($stage instanceof Stages\Registrations): ?>
						<li data-role="fieldcontain">
							<label for="unregisterEndDate">
								<strong><?php echo _('Unregistration limit date'); ?></strong><br/>
								<i><?php echo _("It's allowed to unregister until the end of this time"); ?></i>
							</label>
							<input type="text" name="unregisterEndDate" id="unregisterEndDate" value="<?php echo \CompetitionManager\Utils\Date::getDate($stage->parameters['unregisterEndTime']); ?>" data-role="datebox" data-options='{"mode":"calbox", "useFocus": true, "closeCallback":"autoOpenTime", "closeCallbackArgs":["unregisterEndDate"], "afterToday": true}' placeholder="optional"/>
						</li>
						<li data-role="fieldcontain">
							<label for="unregisterEndTime">
								<strong><?php echo _('Unregistration limit time'); ?></strong><br/>
							</label>
							<input type="text" name="unregisterEndTime" id="unregisterEndTime" value="<?php echo \CompetitionManager\Utils\Date::getTime($stage->parameters['unregisterEndTime']); ?>" data-role="datebox" data-options='{"mode":"timebox", "useFocus": true}' placeholder="optional"/>
						</li>
					<?php endif; ?>
					</ul>
				<?php elseif($stage->schedule instanceof Schedules\MultiSimple): ?>
					<?php $matchNames = $stage->getScheduleNames(); ?>
					<legend><?php echo _('Set start times'); ?></legend>
					<ul data-role="listview">
						<?php foreach($stage->schedule->startTimes as $i => $startTime): ?>
							<li data-role="fieldcontain">
								<label for="startDate-<?php echo $i; ?>">
									<strong><?php echo $matchNames[$i]; ?> <?= _('date')  ?></strong><br/>
								</label>
								<input type="text" name="startDates[]" id="startDate-<?php echo $i; ?>" value="<?php echo \CompetitionManager\Utils\Date::getDate($startTime); ?>" data-role="datebox" data-options='{"mode":"calbox", "useFocus": true, "closeCallback":"autoOpenTime", "closeCallbackArgs":["startDate-<?php echo $i; ?>"], "afterToday": true}'/>
							</li>
							<li data-role="fieldcontain">
								<label for="startTime-<?php echo $i; ?>">
									<strong><?php echo $matchNames[$i]; ?> <?= _('time') ?></strong><br/>
								</label>
								<input type="text" name="startTimes[]" id="startTime-<?php echo $i; ?>" value="<?php echo \CompetitionManager\Utils\Date::getTime($startTime); ?>" data-role="datebox" data-options='{"mode":"timebox", "useFocus": true}'/>
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
