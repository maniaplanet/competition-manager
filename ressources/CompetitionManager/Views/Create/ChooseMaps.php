<?php
require __DIR__.'/../Header.php';
$r = ManiaLib\Application\Request::getInstance();
?>
<div data-role="page">
	<?php echo CompetitionManager\Helpers\Header::save(); ?>
    <div class="ui-bar ui-bar-b">
		<h3><?php printf(_('Select the maps that will be played during stage #%d: %s'), $stageIndex, $stage->getName()); ?></h3><br/>
    </div>
	<?php echo DedicatedManager\Helpers\Box\Box::detect(); ?>
    <div data-role="content">
		<div class="content-primary">
			<form name="maps" action="<?php echo $r->createLinkArgList('../set-maps', 's'); ?>" method="post" data-ajax="false">
				<?php echo DedicatedManager\Helpers\Files::sortableTree($files, $stage->maps, 'maps'); ?>
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
