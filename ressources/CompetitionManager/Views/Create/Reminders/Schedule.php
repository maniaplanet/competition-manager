<?php use CompetitionManager\Services\Schedules; ?>
<li data-role="list-divider" data-theme="d"><?php echo _('Schedule'); ?></li>
<?php if($locStage->schedule instanceof Schedules\Simple): ?>
	<li><span style="font-weight:normal"><?php echo _('Start:'); ?></span> <?php echo $locStage->schedule->startTime; ?></li>
<?php elseif($locStage->schedule instanceof Schedules\Range): ?>
	<li><span style="font-weight:normal"><?php echo _('From:'); ?></span> <?php echo $locStage->schedule->startTime; ?></li>
	<li><span style="font-weight:normal"><?php echo _('To:'); ?></span> <?php echo $locStage->schedule->endTime; ?></li>
<?php elseif($locStage->schedule instanceof Schedules\MultiSimple): ?>
	<?php $matchNames = $locStage->getScheduleNames(); ?>
	<?php foreach($locStage->schedule->startTimes as $i => $startTime): ?>
		<li><span style="font-weight:normal"><?php echo $matchNames[$i]; ?>:</span> <?php echo $startTime; ?></li>
	<?php endforeach; ?>
<?php else: ?>
<?php endif; ?>
