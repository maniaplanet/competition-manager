<?php require __DIR__.'/../Header.php'; ?>
<div data-role="page">
	<?php echo CompetitionManager\Helpers\Header::save() ?>
	<?php echo DedicatedManager\Helpers\Box\Box::detect() ?>
    <div data-role="content">
		<form action="<?php echo $appURL ?>/manager/delete-maps/" method="post" data-ajax="false">
			<input type="hidden" name="path" value="<?php echo $path; ?>"/>
			<?php echo DedicatedManager\Helpers\Files::folder($files, $path, $parentPath); ?>
			<input type="submit" value="<?php echo _('Delete') ?>" data-icon="delete"/>
		</form>
		<form action="<?php echo $appURL ?>/manager/upload-map/" method="post" data-ajax="false" enctype="multipart/form-data">
			<input type="hidden" name="path" value="<?php echo $path; ?>"/>
			<ul data-role="listview" data-inset="true">
				<li data-role="list-divider"><h3><?php echo _('Upload in this folder'); ?></h3></li>
				<li><input type="file" name="map"/></li>
				<li><input type="submit" value="<?php echo _('Upload map'); ?>" data-icon="plus"/></li>
			</ul>
		</form>
	</div>
</div>
<?php require __DIR__.'/../Footer.php'; ?>