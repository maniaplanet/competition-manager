<?php use CompetitionManager\Services\Stages; ?>
<?php if(!($locStage instanceof Stages\Lobby)): ?>
	<li><span style="font-weight:normal"><?php echo _('Max Slots:'); ?></span> <?php echo $locStage->maxSlots; ?></li>
<?php endif; ?>
<?php if($locStage instanceof Stages\Championship): ?>
	<li><span style="font-weight:normal"><?php echo _('Matches Type:'); ?></span> <?php echo $locStage->parameters['isFreeForAll'] ? _('Free For All') : _('One-On-One'); ?></li>
	<?php if($locStage instanceof Stages\Groups): ?>
		<li><span style="font-weight:normal"><?php echo _('Groups Number:'); ?></span> <?php echo $locStage->parameters['numberOfGroups']; ?></li>
		<li><span style="font-weight:normal"><?php echo _('Qualified Per Group:'); ?></span> <?php echo $locStage->parameters['qualifiedPerGroups']; ?></li>
	<?php endif; ?>
<?php elseif($locStage instanceof Stages\Brackets): ?>
	<li><span style="font-weight:normal"><?php echo _('Slots Per Match:'); ?></span> <?php echo $locStage->parameters['slotsPerMatch']; ?></li>
<?php endif; ?>
<li data-role="list-divider" data-theme="d"><?php echo $locStage->rules->getName(); ?></li>
<?php foreach($locStage->rules->getSettings() as $setting => $settingInfo): ?>
<li>
	<span style="font-weight:normal"><?php echo ucfirst(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $setting)); ?>: </span>
	<?php if($settingInfo[0] == 'scoring'): ?>
		<?php echo $locStage->rules->$setting->name; ?>
	<?php elseif($settingInfo[0] == 'bool'): ?>
		<?php echo $locStage->rules->$setting->name ? _('Yes') : _('No'); ?>
	<?php else: ?>
		<?php echo $locStage->rules->$setting; ?> <?php echo $settingInfo[0]; ?>
	<?php endif; ?>
</li>
<?php endforeach; ?>