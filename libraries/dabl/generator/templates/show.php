<?php
unset($actions['Show']);
?>
<h1>View <?php echo StringFormat::titleCase($table_name, ' ') ?></h1>
<p>
<?php foreach($actions as $action_label => $action_url): ?>
	<a href="<?php echo $action_url ?>"
	   class="ui-state-default ui-corner-all ui-button-link" title="<?php echo $action_label . ' ' . ucfirst($single) ?>"<?php if(strtolower($action_label) == 'delete'): ?>

	   onclick="return confirm('Are you sure?');"<?php endif ?>>
		<span class="ui-icon <?php if(array_key_exists($action_label, $this->actionIcons)) echo 'ui-icon-'.$this->actionIcons[$action_label]; ?>"></span><?php echo $action_label ?>

	</a>
<?php endforeach ?>
</p>
<div class="ui-widget-content ui-corner-all">
<?php
foreach($this->getColumns($table_name) as $column){
	$column_name = $column->getName();
	if($column_name==$pk) {
		continue;
	}
	$column_label = StringFormat::titleCase($column_name, ' ');
	if ($column->isForeignKey() && strrpos(strtolower($column_label), 'id') === strlen($column_label) - 2) {
		$column_label = str_replace(array('id', 'Id', 'ID', 'iD'), '', $column_label);
		$column_label = trim($column_label);
	}
	$method = "get$column_name";
	switch($column->getType()){
		case PropelTypes::TIMESTAMP:
			$format = 'VIEW_TIMESTAMP_FORMAT';
			break;
		case PropelTypes::DATE:
			$format = 'VIEW_DATE_FORMAT';
			break;
		default:
			$format = null;
			break;
	}
	if ($column->isForeignKey()) {
		$fk = array_shift($column->getForeignKeys());
		$foreign_table = $fk->getForeignTableName();
		$local_column = $fk->getLocalColumnName();
		$long_method = 'get' . StringFormat::titleCase("{$foreign_table}_related_by_{$local_column}", '');
		$output = '<?php echo htmlentities($'.$single.'->'."$long_method".'()) ?>';
	} else {
		$output = '<?php echo htmlentities($'.$single.'->'."get$column_name".'('.$format.')) ?>';
	}
?>
	<p>
		<strong><?php echo $column_label ?>:</strong>
		<?php echo $output ?>

	</p>
<?php
}
?>
</div>