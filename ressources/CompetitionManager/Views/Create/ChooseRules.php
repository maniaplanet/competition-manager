<?php
require __DIR__.'/../Header.php';
use CompetitionManager\WebUI\HTML;
use CompetitionManager\Services\Stages;
$r = ManiaLib\Application\Request::getInstance();
?>
<div data-role="page">
	<?php echo CompetitionManager\Helpers\Header::save(); ?>
    <div class="ui-bar ui-bar-b">
		<h3><?php printf(_('Set the rules that will apply to matches in stage #%d: %s'), $stageIndex, $stage->getName()); ?></h3><br/>
    </div>
	<?php echo DedicatedManager\Helpers\Box\Box::detect(); ?>
    <div data-role="content">
		<div class="content-primary">
			<form name="config" action="<?php echo $r->createLinkArgList('../set-rules', 's'); ?>" method="post" data-ajax="false" data-role="collapsible-group">
				<fieldset data-role="collapsible" data-collapsed="false" data-theme="b">
					<legend><?php echo _('Stage configuration'); ?></legend>
					<ul data-role="listview">
					<?php if(!($stage instanceof Stages\Lobby)): ?>
						<?php if($stage === $competition->getFirstPlayStage()): ?>
							<li data-role="fieldcontain">
								<label for="minSlots">
									<strong><?php echo _('Min Slots'); ?></strong><br/>
									<i><?php printf(_('Minimum number of %s to start the competition'), $competition->isTeam ? _('teams') : _('players')); ?></i>
								</label>
								<input type="text" name="minSlots" id="minSlots" value="<?php echo $stage->minSlots; ?>"/>
							</li>
						<?php endif; ?>
						<li data-role="fieldcontain">
							<label for="maxSlots">
								<strong><?php echo _('Max Slots'); ?></strong><br/>
								<i><?php printf(_('Maximum number of %s for this stage'), $competition->isTeam ? _('teams') : _('players')); ?></i>
							</label>
						<?php if($stage instanceof Stages\Championship): ?>
							<input type="text" name="maxSlots" id="maxSlots" value="<?php echo $stage->maxSlots; ?>"/>
						<?php elseif($stage instanceof Stages\Groups || $stage instanceof Stages\Brackets): ?>
							<input type="text" name="maxSlots" id="maxSlots" value="<?php echo $stage->maxSlots; ?>" readonly="readonly"/>
						<?php else: ?>
							<input type="text" name="maxSlots" id="maxSlots" value="<?php echo $stage->maxSlots; ?>" class="fixable"/>
						<?php endif; ?>
						</li>
					<?php endif; ?>
					<?php if($stage instanceof Stages\Groups): ?>
						<li data-role="fieldcontain">
							<label for="isFreeForAll">
								<strong><?php echo _('Matches type'); ?></strong><br/>
							</label>
							<select id="isFreeForAll" name="isFreeForAll" data-role="slider">
								<option value="0" <?php echo !$stage->parameters['isFreeForAll'] ? 'selected="selected"' : ''; ?>><?php echo _('One-On-One'); ?></option>
								<option value="1" <?php echo $stage->parameters['isFreeForAll'] ? 'selected="selected"' : ''; ?>><?php echo _('Free For All'); ?></option>
							</select>
						</li>
						<li data-role="fieldcontain">
							<label for="numberOfRounds">
								<strong><?php echo _('Number of rounds'); ?></strong><br/>
								<i class="ffa-compliant"><?php echo _('How many matches will be played'); ?></i>
								<i class="versus-compliant"><?php echo sprintf(_('How many matches between each %s'), $competition->isTeam ? _('teams') : _('players')); ?></i>
							</label>
							<input type="text" name="numberOfRounds" id="numberOfRounds" value="<?php echo $stage->parameters['numberOfRounds']; ?>"/>
						</li>
						<li data-role="fieldcontain" class="versus-compliant">
							<label for="pointsForWin">
								<strong><?php echo _('Points for victory'); ?></strong><br/>
							</label>
							<input type="text" name="pointsForWin" id="pointsForWin" value="<?php echo $stage->parameters['pointsForWin']; ?>"/>
						</li>
						<li data-role="fieldcontain" class="versus-compliant">
							<label for="pointsForLoss">
								<strong><?php echo _('Points for defeat'); ?></strong><br/>
							</label>
							<input type="text" name="pointsForLoss" id="pointsForLoss" value="<?php echo $stage->parameters['pointsForLoss']; ?>"/>
						</li>
						<li data-role="fieldcontain" class="versus-compliant">
							<label for="pointsForForfeit">
								<strong><?php echo _('Points for forfeit'); ?></strong><br/>
								<i><?php echo sprintf(_('For a %s who didn\'t show up or leaved'), $competition->isTeam ? _('team') : _('player')); ?></i>
							</label>
							<input type="text" name="pointsForForfeit" id="pointsForForfeit" value="<?php echo $stage->parameters['pointsForForfeit']; ?>"/>
						</li>
						<li data-role="fieldcontain" class="ffa-compliant">
							<fieldset data-role="controlgroup">
								<legend>
									<strong><?php echo _('Scoring system'); ?></strong><br/>
									<i><?php echo _('Points awarded for each match'); ?></i>
								</legend>
<!--								<input type="radio" name="scoringSystem" id="scoringSystem-none"
									   value="" <?php echo !$stage->parameters['scoringSystem'] ? 'checked="checked"' : ''; ?>/>
								<label for="scoringSystem-none"><?php echo _('* Use match results'); ?></label>-->
							<?php foreach($scoringSystems as $name => $system): ?>
								<input type="radio" name="scoringSystem" id="scoringSystem-<?php echo md5($name); ?>"
									   value="<?php echo $name; ?>" <?php echo $system == $stage->parameters['scoringSystem'] ? 'checked="checked"' : ''; ?>/>
								<label for="scoringSystem-<?php echo md5($name); ?>"><?php echo $name; ?></label>
							<?php endforeach; ?>
							</fieldset>
						</li>
						<script>
							$(document).bind('pageinit', function() {
								$('select#isFreeForAll').change(function() {
									if($(this).val() == 1) {
										$('.ffa-compliant').show().find('input').prop('disabled', false);
										$('.versus-compliant').hide().find('input').prop('disabled', true);
									}
									else {
										$('.ffa-compliant').hide().find('input').prop('disabled', true);
										$('.versus-compliant').show().find('input').prop('disabled', false);
									}
								}).trigger('change');
								
								<?php $fieldId = $stage instanceof Stages\Championship ? 'maxSlots' : 'slotsPerGroup'; ?>
								$('select#gamemode').change(function() {
									var selected = $(this).children(':selected');
									if(selected.jqmData('fixed-slots') == 2) {
										$('select#isFreeForAll').slider('disable').val(0).trigger('change');
										$('input#<?php echo $fieldId; ?>').prop('readonly', false).trigger('change');
									}
									else if(selected.jqmData('fixed-slots')) {
										$('select#isFreeForAll').slider('disable').val(1).trigger('change');
										$('input#<?php echo $fieldId; ?>').prop('readonly', true).val(selected.jqmData('fixed-slots')).trigger('change');
									}
									else {
										$('select#isFreeForAll').slider('enable').trigger('change');
										$('input#<?php echo $fieldId; ?>').prop('readonly', false).trigger('change');
									}
								}).trigger('change');
							});
						</script>
						<?php if(!($stage instanceof Stages\Championship)): ?>
							<li data-role="fieldcontain">
								<label for="numberOfGroups">
									<strong><?php echo _('Number of groups'); ?></strong><br/>
								</label>
								<input type="text" name="numberOfGroups" id="numberOfGroups" value="<?php echo $stage->parameters['numberOfGroups']; ?>"/>
							</li>
							<li data-role="fieldcontain">
								<label for="slotsPerGroup">
									<strong><?php echo _('Slots per group'); ?></strong><br/>
								</label>
								<input type="text" id="slotsPerGroup" value="<?php echo $stage->maxSlots / ($stage->parameters['nbGroups'] ?: 4) ?: 4; ?>"/>
							</li>
							<script>
								$(document).bind('pageinit', function() {
									$('#numberOfGroups, #slotsPerGroup').change(function() {
										$('#maxSlots').val(parseInt($('#numberOfGroups').val()) * parseInt($('#slotsPerGroup').val()))
									}).trigger('change');
								});
							</script>
						<?php endif; ?>
					<?php elseif($stage instanceof Stages\Brackets): ?>
						<li data-role="fieldcontain">
							<label for="numberOfRounds">
								<strong><?php echo _('Number of rounds'); ?></strong><br/>
								<i><?php echo _('How many matches to final (included)'); ?></i>
							</label>
							<input type="text" id="numberOfRounds" value="<?php echo $stage->getWBRoundsCount(); ?>"/>
						</li>
						<li data-role="fieldcontain">
							<label for="slotsPerMatch">
								<strong><?php echo _('Slots per match'); ?></strong><br/>
							</label>
							<input type="text" name="slotsPerMatch" id="slotsPerMatch" value="<?php echo $stage->parameters['slotsPerMatch']; ?>" class="fixable"/>
						</li>
						<script>
							$(document).bind('pageinit', function() {
								$('#numberOfRounds, #slotsPerMatch').change(function() {
									$('#maxSlots').val(parseInt($('#slotsPerMatch').val()) * Math.pow(2, parseInt($('#numberOfRounds').val()) - 1))
								}).trigger('change');
							});
						</script>
					<?php endif; ?>
						<li data-role="fieldcontain">
							<label for="gamemode">
								<strong><?php echo _('Game mode'); ?></strong><br/>
							</label>
							<select name="gamemode" id="gamemode" data-native-menu="false">
							<?php foreach($availableModes as $mode): ?>
								<option value="<?php echo get_class($mode); ?>" <?php echo $stage->rules && $stage->rules->getId() == $mode->getId() ? 'selected="selected"' : ''; ?>
										data-mode-id="<?php echo $mode->getId(); ?>" data-fixed-slots="<?php echo $mode->fixedSlots; ?>"><?php echo $mode->getName(); ?></option>
							<?php endforeach; ?>
							</select>
						</li>
					</ul>
				</fieldset>
			<?php foreach($availableModes as $mode): ?>
				<?php $mode = $stage->rules && $stage->rules->getId() == $mode->getId() ? $stage->rules : $mode; ?>
				<fieldset id="settings-<?php echo $mode->getId(); ?>" data-role="collapsible" data-collapsed="false" data-theme="b">
					<legend><?php echo $mode->getName(); ?></legend>
					<ul data-role="listview">
					<?php if($mode->getSettings()): ?>
						<?php foreach($mode->getSettings() as $setting => $details): ?>
							<?php $id = 'setting-'.$mode->getId().'-'.$setting; ?>
							<li data-role="fieldcontain">
							<?php if($details[0] == 'scoring'): ?>
								<fieldset data-role="controlgroup">
									<legend>
										<strong><?php echo ucfirst(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $setting)); ?></strong><br/>
										<i><?php echo $details[1]; ?></i>
									</legend>
									<input type="radio" name="<?php echo $setting; ?>[<?php echo $mode->getId(); ?>]" id="<?php echo $id; ?>-none"
										   value="" <?php echo !$mode->$setting ? 'checked="checked"' : ''; ?>/>
									<label for="<?php echo $id; ?>-none"><?php echo _('* Default'); ?></label>
								<?php foreach($scoringSystems as $name => $system): ?>
									<input type="radio" name="<?php echo $setting; ?>[<?php echo $mode->getId(); ?>]" id="<?php echo $id; ?>-<?php echo md5($name); ?>"
										   value="<?php echo $name; ?>" <?php echo $system == $mode->$setting ? 'checked="checked"' : ''; ?>/>
									<label for="<?php echo $id; ?>-<?php echo md5($name); ?>"><?php echo $name; ?></label>
								<?php endforeach; ?>
								</fieldset>
							<?php elseif($details[0] == 'bool'): ?>
								<label for="<?php echo $id; ?>">
									<strong><?php echo ucfirst(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $setting)); ?></strong><br/>
									<i><?php echo $details[1]; ?></i>
								</label>
								<select id="<?php echo $id; ?>" name="<?php echo $setting; ?>[<?php echo $mode->getId(); ?>]" data-role="slider">
									<option value="0" <?php echo !$mode->$setting ? 'selected="selected"' : ''; ?>><?php echo _('No'); ?></option>
									<option value="1" <?php echo $mode->$setting ? 'selected="selected"' : ''; ?>><?php echo _('Yes'); ?></option>
								</select>
							<?php else: ?>
								<label for="setting-<?php echo $mode->getId(); ?>-<?php echo $setting; ?>">
									<strong><?php echo ucfirst(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $setting)).($details[0] ? ' ('.$details[0].')' : ''); ?></strong><br/>
									<i><?php echo $details[1]; ?></i>
								</label>
								<input type="text" name="<?php echo $setting; ?>[<?php echo $mode->getId(); ?>]" id="<?php echo $id; ?>" value="<?php echo $mode->$setting; ?>"/>
							<?php endif; ?>
							</li>
						<?php endforeach; ?>
					<?php else: ?>
						<li>No rules to set in this mode</li>
					<?php endif; ?>
					</ul>
				</fieldset>
			<?php endforeach; ?>
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
