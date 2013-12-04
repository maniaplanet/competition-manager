<?php
require __DIR__.'/../Header.php';
use CompetitionManager\Services\Stages;
use CompetitionManager\WebUI\HTML;
$r = ManiaLib\Application\Request::getInstance();
?>
<div data-role="page" id="content">
	<?php echo CompetitionManager\Helpers\Header::save(); ?>
    <div class="ui-bar ui-bar-b">
		<h3><?php echo _('General information about your competition'); ?></h3><br/>
    </div>
	<?php echo DedicatedManager\Helpers\Box\Box::detect(); ?>
    <div data-role="content">
		<form name="config" action="<?php echo $r->createLinkArgList('../do-prepare'); ?>" method="post" data-ajax="false" data-role="collapsible-group">
			<fieldset data-role="collapsible" data-collapsed="false" data-theme="b">
				<legend><?php echo _('Basic information'); ?></legend>
				<ul data-role="listview">
					<li data-role="fieldcontain">
						<label for="name">
							<strong><?php echo _('Competition name'); ?></strong><br/>
							<i><?php echo _('Set the public name of your competition (you can use ManiaPlanet styles if you like)'); ?></i>
						</label>
						<input type="text" id="name" name="name" value="<?php echo HTML::encode($competition->name); ?>" data-role="maniaplanet-style"/>
					</li>
<!--					<li data-role="fieldcontain">
						<label for="description">
							<strong><?php echo _('Description'); ?></strong><br/>
							<i><?php echo _('This will be shown on the main page of your competition'); ?></i>
						</label>
						<textarea id="description" name="description" data-role="maniaplanet-style"><?php echo HTML::encode($competition->description); ?></textarea>
					</li>-->
					<li data-role="fieldcontain">
						<label for="isTeam">
							<strong><?php echo _('Team or solo'); ?></strong><br/>
							<i><?php echo _('Whether it is a team competition or a solo one'); ?></i>
						</label>
						<select id="isTeam" name="isTeam" data-role="slider">
							<option value="0" <?php echo !$competition->isTeam ? 'selected="selected"' : ''; ?>><?php echo _('Solo'); ?></option>
							<option value="1" <?php echo $competition->isTeam ? 'selected="selected"' : ''; ?>><?php echo _('Team'); ?></option>
						</select>
					</li>
					<li data-role="fieldcontain">
						<label for="title">
							<strong><?php echo _('Game title'); ?></strong><br/>
							<i><?php echo _('Select the ManiaPlanet title on which your competition will be played'); ?></i>
						</label>
						<select id="title" name="title" data-native-menu="false">
							<optgroup label="<?php echo _('Games'); ?>">
								<option value="TMCanyon" <?php echo $competition->title == 'TMCanyon' ? 'selected="selected"' : ''; ?>
										class="solo-compliant team-compliant openqualifiers-not-compliant remote-compliant">TrackMania² Canyon</option>
								<option value="TMValley" <?php echo $competition->title == 'TMValley' ? 'selected="selected"' : ''; ?>
										class="solo-compliant team-compliant openqualifiers-not-compliant remote-compliant">TrackMania² Valley</option>
								<option value="TMStadium" <?php echo $competition->title == 'TMStadium' ? 'selected="selected"' : ''; ?>
										class="solo-compliant team-compliant openqualifiers-not-compliant remote-compliant">TrackMania² Stadium</option>
								<option value="SMStorm" <?php echo $competition->title == 'SMStorm' ? 'selected="selected"' : ''; ?>
										class="solo-compliant team-compliant remote-compliant">ShootMania Storm</option>
							</optgroup>
							<optgroup label="<?php echo _('Titles'); ?>">
								<option value="SMStormRoyal@nadeolabs" <?php echo $competition->title == 'SMStormRoyal@nadeolabs' ? 'selected="selected"' : ''; ?>
										class="solo-compliant">ShootMania Storm Royal</option>
								<option value="SMStormJoust@nadeolabs" <?php echo $competition->title == 'SMStormJoust@nadeolabs' ? 'selected="selected"' : ''; ?>
										class="solo-compliant">ShootMania Storm Joust</option>
								<option value="SMStormElite@nadeolabs" <?php echo $competition->title == 'SMStormElite@nadeolabs' ? 'selected="selected"' : ''; ?>
										class="team-compliant remote-compliant">ShootMania Storm Elite</option>
<!--							<option value="SMStormHeroes@nadeolabs" <?php echo $competition->title == 'SMStormHeroes@nadeolabs' ? 'selected="selected"' : ''; ?>
										class="team-compliant">ShootMania Storm Heroes</option>-->
							</optgroup>
						</select>
					</li>
					<li data-role="fieldcontain">
						<label for="isLan">
							<strong><?php echo _('LAN or online'); ?></strong><br/>
							<i><?php echo _('Whether servers will be accessible on LAN only'); ?></i>
						</label>
						<select id="isLan" name="isLan" data-role="slider">
							<option value="0" <?php echo !$competition->isLan ? 'selected="selected"' : ''; ?>><?php echo _('Online'); ?></option>
							<option value="1" <?php echo $competition->isLan ? 'selected="selected"' : ''; ?>><?php echo _('LAN'); ?></option>
						</select>
					</li>
					<!--
					<li data-role="fieldcontain">
						<label for="useRemote">
							<strong><?php echo _('Interface with ManiaPlanet'); ?></strong><br/>
							<i><?php echo _('Your competition will be listed in ManiaPlanet competition list'); ?></i>
						</label>
						<select id="useRemote" name="useRemote" data-role="slider">
							<option value="0" <?php echo !$competition->remoteId ? 'selected="selected"' : ''; ?>><?php echo _('No'); ?></option>
							<option value="1" <?php echo $competition->remoteId ? 'selected="selected"' : ''; ?>><?php echo _('Yes'); ?></option>
						</select>
					</li>
					-->
				</ul>
			</fieldset>
			<fieldset data-role="collapsible" data-collapsed="false" data-theme="b">
				<legend><?php echo _('Planets'); ?></legend>
				<ul data-role="listview">
				<?php if(!$planetsUsable): ?>
					<li><?php echo _('You cannot use planets: check your app.ini and complete fields required for payments'); ?></li>
				<?php endif; ?>
					<li data-role="fieldcontain">
						<label for="registrationCost">
							<strong><?php echo _('Registration cost'); ?></strong><br/>
							<i><?php echo _('How many Planets should players pay to enter'); ?></i>
						</label>
						<input type="number" id="registrationCost" name="registrationCost" value="<?php echo $planetsUsable ? HTML::encode($competition->registrationCost) : 0; ?>"
							   min="0" <?php echo $planetsUsable ? '' : 'disabled="disabled"'; ?>/>
					</li>
					<li data-role="fieldcontain">
						<fieldset data-role="controlgroup">
							<legend>
								<strong><?php echo _('Rewards'); ?></strong><br/>
								<i><?php echo _('How the Planets will be redistributed (including sponsors)'); ?></i>
							</legend>
							<input type="radio" name="rewards" id="rewards-none" value="" <?php echo !$planetsUsable || !$competition->rewards ? 'checked="checked"' : ''; ?>/>
							<label for="rewards-none"><?php echo _('* None'); ?></label>
						<?php foreach($rewards as $name => $rewardsTpl): ?>
							<input type="radio" name="rewards" id="rewards-<?php echo md5($name); ?>" <?php echo $planetsUsable ? '' : 'disabled="disabled"'; ?>
								   value="<?php echo $name; ?>" <?php echo $planetsUsable && $rewardsTpl == $competition->rewards ? 'checked="checked"' : ''; ?>/>
							<label for="rewards-<?php echo md5($name); ?>"><?php echo $name; ?></label>
						<?php endforeach; ?>
						</fieldset>
					</li>
				</ul>
			</fieldset>
			<fieldset data-role="collapsible" data-collapsed="false" data-theme="b">
				<legend><?php echo _('Format'); ?></legend>
				<ul data-role="listview">
					<li data-role="fieldcontain">
						<label for="isScheduled">
							<strong><?php echo _('Scheduling'); ?></strong><br/>
							<i><?php echo _('Fixed scheduled or pick-up style'); ?></i>
						</label>
						<select id="isScheduled" name="isScheduled" data-role="slider">
							<option value="0" <?php echo reset($competition->stages) instanceof Stages\Lobby ? 'selected="selected"' : ''; ?>><?php echo _('Pick-up'); ?></option>
							<option value="1" <?php echo !(reset($competition->stages) instanceof Stages\Lobby) ? 'selected="selected"' : ''; ?>><?php echo _('Scheduled'); ?></option>
						</select>
					</li>
<!--					<li data-role="fieldcontain">
						<label for="hasOpenQualifiers">
							<strong><?php echo _('Open qualifiers'); ?></strong><br/>
							<i><?php echo _('Merge registrations with Time-Attack qualifiers'); ?></i>
						</label>
						<select id="hasOpenQualifiers" name="hasOpenQualifiers" data-role="slider">
							<option value="0" <?php echo !(reset($competition->stages) instanceof Stages\OpenStage) ? 'selected="selected"' : ''; ?>><?php echo _('No'); ?></option>
							<option value="1" <?php echo reset($competition->stages) instanceof Stages\OpenStage ? 'selected="selected"' : ''; ?>><?php echo _('Yes'); ?></option>
						</select>
					</li>-->
				<?php if(!$competition->format): ?>
					<li data-role="fieldcontain">
						<fieldset data-role="controlgroup">
							<legend>
								<strong><?php echo _('Type'); ?></strong><br/>
								<i><?php echo _('Stages composing the competition'); ?></i>
							</legend>
						<?php foreach($formats as $name => $formatTpl): ?>
							<input type="radio" name="format" id="format-<?php echo md5($name); ?>" value="<?php echo $name; ?>"/>
							<label for="format-<?php echo md5($name); ?>"><?php echo $name; ?></label>
						<?php endforeach; ?>
						</fieldset>
					</li>
				<?php endif; ?>
				</ul>
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
</div>
<?php require __DIR__.'/../Footer.php' ?>
