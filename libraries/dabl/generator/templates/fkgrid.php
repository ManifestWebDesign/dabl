<?php
$foreign_column = array_shift($foreign_key->getForeignColumns());
$local_column = array_shift($foreign_key->getLocalColumns());
$fk_single = str_replace(array('_', '-'), '', strtolower($foreign_key->getForeignTableName()));
?>
<h1>
	<?php echo '<?php echo $page ?>' ?>

	<a href="<?php echo "<?php echo" ?> site_url('<?php echo $plural_url ?>/edit?<?php echo $local_column ?>=' . $<?php echo $fk_single ?>->get<?php echo $foreign_column ?>()) <?php echo "?>" ?>"
	   class="ui-state-default ui-corner-all ui-button-link" title="New <?php echo str_replace('_', ' ', ucfirst($single)) ?>">
		<span class="ui-icon ui-icon-plusthick"></span>New <?php echo str_replace('_', ' ', ucfirst($single)) ?>

	</a>
</h1>

<div class="ui-widget-content ui-corner-all">
	<?php echo "<?php load_view('" . $plural_url . "/grid', \$params) ?>" ?>

</div>