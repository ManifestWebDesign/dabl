<?php
unset($actions['Show']);
?>
<h1>View <?php echo StringFormat::titleCase($table_name, ' ') ?></h1>
<div class="action-buttons ui-helper-clearfix">
<?php
foreach($actions as $action_label => $action_url):
	$icon_class = @$this->actionIcons[$action_label] ? $this->actionIcons[$action_label] : 'carat-1-e';
?>
	<a href="<?php echo $action_url ?>"
		class="button" data-icon="<?php echo $icon_class ?>" title="<?php echo $action_label . ' ' . ucfirst($single) ?>"<?php if(strtolower($action_label) == 'delete'): ?>

		onclick="return confirm('Are you sure?');"<?php endif ?>>
		<?php echo $action_label ?>
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
	if (
		$column->isForeignKey()
		&& strrpos(strtolower($column_label), ' id') === strlen($column_label) - 3
	) {
		$column_label = substr($column_label, 0, -3);
		$column_label = trim($column_label);
	}
	switch ($column->getType()) {
		case Model::COLUMN_TYPE_TIMESTAMP:
		case Model::COLUMN_TYPE_INTEGER_TIMESTAMP:
			$format = 'VIEW_TIMESTAMP_FORMAT';
			break;
		case Model::COLUMN_TYPE_DATE:
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
		$output = '<?php echo h($' . $single . '->' . "$long_method" . '()) ?>';
	} elseif ($column->getType() == Model::COLUMN_TYPE_BOOLEAN) {
		$output = '<?php if ($'.$single.'->'."get$column_name".'('.$format.') === 1) echo \'Yes\'; elseif ($'.$single.'->'."get$column_name".'('.$format.') === 0) echo \'No\' ?>';
	} else {
		$output = '<?php echo h($' . $single . '->' . "get$column_name" . '(' . $format . ')) ?>';
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