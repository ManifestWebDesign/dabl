<?php echo '<?php' ?>

$_get_args = (array) @$_GET;

if (isset($_REQUEST['Dir'])) {
	unset($_get_args['Dir']);
} elseif (isset($_REQUEST['SortBy'])) {
	$_get_args['Dir'] = 'DESC';
}
<?php echo '?>' ?>

<table class="object-grid <?php echo $single ?>-grid" cellspacing="0">
	<thead>
		<tr>
<?php
foreach($columns as $key => $column){
	$column_name = $column->getName();
	$column_label = StringFormat::titleCase($column_name, ' ');
	if ($column->isForeignKey() && strrpos(strtolower($column_label), 'id') === strlen($column_label) - 2) {
		$column_label = str_replace(array('id', 'Id', 'ID', 'iD'), '', $column_label);
		$column_label = trim($column_label);
	}
	$column_constant = $model_name . '::' . StringFormat::constant($column->getName());
	$sort_href = "<?php echo http_build_query(array_merge(\$_get_args, array('SortBy' => $column_constant))) ?>";
?>
			<th class="ui-widget-header <?php if ($key == 0) echo 'ui-corner-tl' ?>">
				<a href="?<?php echo $sort_href ?>">
					<?php echo "<?php if( @\$_REQUEST['SortBy'] == $column_constant): ?>" ?>

						<span class="ui-icon ui-icon-carat-1-<?php echo "<?php echo isset(\$_REQUEST['Dir']) ? 's' : 'n' ?>" ?>"></span>
					<?php echo "<?php endif ?>"?>

					<?php echo $column_label ?>

				</a>
			</th>
<?php
}
if ($actions){
?>
			<th class="ui-widget-header grid-action-column ui-corner-tr">&nbsp;</th>
<?php
}
?>
		</tr>
	</thead>
	<tbody>
<?php echo '<?php foreach($'.$plural.' as $key => $'.$single.'): ?>' ?>

		<tr class="<?php echo '<?php echo' ?> ($key & 1) ? 'even' : 'odd' <?php echo '?>' ?> ui-widget-content">
<?php
foreach ($columns as $column){
	$column_name = StringFormat::titleCase($column->getName());
	switch ($column->getType()){
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
		$output = '<?php echo htmlentities($'.$single.'->'."$long_method".'()) ?>';
	} elseif ($column->getType() == Model::COLUMN_TYPE_BOOLEAN) {
		$output = '<?php if ($'.$single.'->'."get$column_name".'('.$format.') === 1) echo \'True\'; elseif ($'.$single.'->'."get$column_name".'('.$format.') === 0) echo \'False\' ?>';
	} else {
		$output = '<?php echo htmlentities($'.$single.'->'."get$column_name".'('.$format.')) ?>';
	}
?>
			<td><?php echo $output ?>&nbsp;</td>
<?php
}
if ($actions) {
?>
			<td>
<?php
	foreach ($actions as $action_label => $action_url) {
		if ($action_label == 'Index') continue;
			$icon_class = @$this->actionIcons[$action_label] ? 'ui-icon-' . $this->actionIcons[$action_label] : 'ui-icon-carat-1-e';
			$on_click = '';
			if (strtolower($action_label) === 'delete') {
				$on_click = "if (confirm('Are you sure?')) { window.location.href = '$action_url' } return false";
				$action_url = '#';
			}
?>
				<a
					class="ui-widget ui-state-default ui-corner-all ui-button-link"
<?php if (in_array($action_label, $this->standardActions)) : ?>
					title="<?php echo $action_label . ' ' . ucfirst($single) ?>"
<?php endif ?>
					href="<?php echo $action_url ?>"<?php if ('' !== $on_click): ?>

					onclick="<?php echo $on_click ?>"<?php endif ?>>
					<span class="ui-icon <?php echo $icon_class ?>"><?php echo $action_label ?></span>
					<?php echo $action_label ?>

				</a>
<?php
	}
?>
			</td>
<?
}
?>
		</tr>
<?php echo '<?php endforeach ?>' ?>

	</tbody>
</table>