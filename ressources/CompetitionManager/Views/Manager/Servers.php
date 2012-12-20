<?php
require __DIR__.'/../Header.php';
$r = ManiaLib\Application\Request::getInstance();
?>
<div data-role="page">
	<?php echo CompetitionManager\Helpers\Header::save() ?>
	<?php echo DedicatedManager\Helpers\Box\Box::detect() ?>
    <div data-role="content">
		<div data-role="collapsible" data-collapsed="false" data-theme="b">
			<h3><?php echo _('Running servers'); ?></h3>
			<ul data-role="listview" data-inset="true">
			<?php if(!$runningServers): ?>
				<li><?php echo _('There is no running servers at the moment'); ?></li>
			<?php else: ?>
				<?php foreach($runningServers as $server): ?>
					<li><?php echo $server->name; ?></li>
				<?php endforeach; ?>
			<?php endif; ?>
			</ul>
		</div>
		<div data-role="collapsible" data-collapsed="false" data-theme="b">
			<h3><?php echo _('Dedicated accounts'); ?></h3>
		<?php if(!$dedicatedAccounts): ?>
			<ul data-role="listview" data-inset="true">
				<li><?php echo _('You did not register any dedicated account yet'); ?></li>
			</ul>
		<?php else: ?>
			<form action="<?php echo $r->createLinkArgList('../remove-server-accounts'); ?>" method="post" data-ajax="false">
				<ul data-role="listview" data-inset="true">
					<li>
						<fieldset data-role="controlgroup">
						<?php foreach($dedicatedAccounts as $account): ?>
							<input type="checkbox" id="account-<?php echo $account->login; ?>" name="logins[]" value="<?php echo $account->login; ?>"/>
							<label for="account-<?php echo $account->login; ?>"><?php echo $account->login; ?></label>
						<?php endforeach; ?>
						</fieldset>
					</li>
					<li><input type="submit" value="<?php echo _('Remove'); ?>" data-icon="delete"/></li>
				</ul>
			</form>
		<?php endif; ?>
			<form action="<?php echo $r->createLinkArgList('../add-server-account'); ?>" method="post" data-ajax="false">
				<ul data-role="listview" data-inset="true">
					<li data-role="list-divider"><?php echo _('Add a dedicated account'); ?></li>
					<li>
						<a href="http://player.maniaplanet.com/advanced/dedicated-servers/" target="blank">
							<?php echo _('Go to the player page to create dedicated accounts'); ?>
						</a>
					</li>
					<li data-role="fieldcontain">
						<label for="login">
							<strong><?php echo _('Server login'); ?></strong>
						</label>
						<input type="text" id="login" name="login"/>
					</li>
					<li data-role="fieldcontain">
						<label for="password">
							<strong><?php echo _('Server password'); ?></strong>
						</label>
						<input type="password" id="password" name="password"/>
					</li>
					<li><input type="submit" value="<?php echo _('Register'); ?>" data-icon="plus"/></li>
				</ul>
			</form>
		</div>
	</div>
</div>
<?php require __DIR__.'/../Footer.php'; ?>