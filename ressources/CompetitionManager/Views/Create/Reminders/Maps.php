<li data-role="list-divider" data-theme="d"><?php echo _('Maps'); ?></li>
<?php foreach($locStage->maps as $map): ?>
<?php $path = explode('/', $map); ?>
	<li><span style="font-weight:normal;"><?php echo implode('/', array_slice($path, 0, -1)); ?>/</span><?php echo implode(array_slice($path, -1)); ?></li>
<?php endforeach; ?>