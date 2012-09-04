<h1>
	<a href="<?php echo "<?php echo site_url('" . $plural_url . "/edit') ?>" ?>"
	   class="ui-state-default ui-corner-all ui-button-link"
	   title="New <?php echo str_replace('_', ' ', ucfirst($single)) ?>">
		<span class="ui-icon ui-icon-plusthick"></span>
		New <?php echo str_replace('_', ' ', ucfirst($single)) ?>

	</a>
	<?php echo StringFormat::titleCase($plural, ' ') ?>

</h1>

<?php echo "<?php View::load('pager', compact('pager')) ?>" ?>

<div class="ui-widget-content ui-corner-all">
	<?php echo "<?php View::load('" . $plural_url . "/grid', \$params) ?>" ?>

</div>

<?php echo "<?php View::load('pager', compact('pager')) ?>" ?>