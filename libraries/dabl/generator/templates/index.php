<h1>
	<?php echo StringFormat::titleCase($plural, ' ') ?>

	<a href="<?php echo "<?php echo site_url('" . $plural_url . "/edit') ?>" ?>"
	   class="ui-state-default ui-corner-all ui-button-link"
	   title="New <?php echo str_replace('_', ' ', ucfirst($single)) ?>">
		<span class="ui-icon ui-icon-plusthick"></span>
		New <?php echo str_replace('_', ' ', ucfirst($single)) ?>

	</a>
</h1>
<?php echo '<?php echo $pager->getLinks($page_string) ?>' ?>

<div class="ui-widget-content ui-corner-all">
	<?php echo "<?php load_view('" . $plural_url . "/grid', \$params) ?>" ?>

</div>