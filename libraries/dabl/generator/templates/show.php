<?php
unset($actions['Show']);
?>
<h1>View <?php echo StringFormat::titleCase($table_name, ' ') ?></h1>
<div class="action-buttons ui-helper-clearfix">
<?php
foreach($actions as $action_label => $action_url):
	$icon_class = @$this->actionIcons[$action_label] ? 'ui-icon-' . $this->actionIcons[$action_label] : 'ui-icon-carat-1-e';
?>
	<a href="<?php echo $action_url ?>"
	   class="ui-state-default ui-corner-all ui-button-link" title="<?php echo $action_label . ' ' . ucfirst($single) ?>"<?php if(strtolower($action_label) == 'delete'): ?>

	   onclick="return confirm('Are you sure?');"<?php endif ?>>
		<span class="ui-icon <?php echo $icon_class ?>"></span><?php echo $action_label ?>

	</a>
<?php endforeach ?>
</div>
<div class="ui-widget-content ui-corner-all ui-helper-clearfix">
<?php
foreach ($this->getColumns($table_name) as $column) {
	$column_name = $column->getName();
	if ($column_name == $pk) {
		continue;
	}
	$column_name = StringFormat::titleCase($column_name);
	$column_label = StringFormat::titleCase($column_name, ' ');
	if ($column->isForeignKey() && strrpos(strtolower($column_label), 'id') === strlen($column_label) - 2) {
		$column_label = str_replace(array('id', 'Id', 'ID', 'iD'), '', $column_label);
		$column_label = trim($column_label);
	}
	switch ($column->getType()) {
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
		$col_fks = $column->getForeignKeys();
		$fk = array_shift($col_fks);
		$foreign_table = $fk->getForeignTableName();
		$local_column = $fk->getLocalColumnName();
		$long_method = 'get' . StringFormat::titleCase("{$foreign_table}_related_by_{$local_column}", '');
		$output = '<?php echo htmlentities($' . $single . '->' . "$long_method" . '()) ?>';
	} elseif ($column->getType() == Model::COLUMN_TYPE_BOOLEAN) {
		$output = '<?php if ($'.$single.'->'."get$column_name".'('.$format.') === 1) echo \'True\'; elseif ($'.$single.'->'."get$column_name".'('.$format.') === 0) echo \'False\' ?>';
	} else {
		$output = '<?php echo htmlentities($' . $single . '->' . "get$column_name" . '(' . $format . ')) ?>';
	}
?>
	<div class="field-wrapper">
		<span class="field-label"><?php echo $column_label ?></span>
		<?php echo $output ?>

	</div>
<?php
}
?>
</div>