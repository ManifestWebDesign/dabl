<table class="object-grid <?php echo $single ?>-grid">
	<thead>
		<tr>
<?php
		foreach($columns as $key => $column){
			$column_name = $column->getName();
			$column_label = StringFormat::titleCase($column_name, ' ');
?>
			<th class="ui-widget-header <?php if($key==0)echo 'ui-corner-tl' ?>">
				<a href="<?php echo "<?php echo" ?> '?SortBy=<?php echo $column_name ?>' . (!isset($_REQUEST['Dir']) && @$_REQUEST['SortBy'] == '<?php echo $column_name ?>' ? '&Dir=DESC' : '') <?php echo "?>" ?>">
					<?php echo "<?php" ?> if( @$_REQUEST['SortBy'] == '<?php echo $column_name ?>'):<?php echo "?>" ?>

						<span class="ui-icon ui-icon-carat-1-<?php echo "<?php" ?> echo isset($_REQUEST['Dir']) ? 's' : 'n' <?php echo "?>" ?>"></span>
					<?php echo "<?php endif ?>"?>

					<?php echo $column_label ?>

				</a>
			</th>
<?php
		}
		$key = 1;
		foreach($actions as $action){
?>
			<th class="ui-widget-header grid-action-column <?php if($key == count($actions))echo 'ui-corner-tr' ?>">&nbsp;</th>
<?php
			++$key;
		}
?>
		</tr>
	</thead>
	<tbody>
<?php echo '<?php foreach($'.$plural.' as $key => $'.$single.'): ?>' ?>

		<tr class="<?php echo '<?php echo' ?> ($key & 1) ? 'even' : 'odd' <?php echo '?>' ?> ui-widget-content">
<?php
		foreach($columns as $column){
			$column_name = $column->getName();
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
			$output = '<?php echo htmlentities($'.$single.'->'."get$column_name".'('.$format.')) ?>';
?>
			<td><?php echo $output ?>&nbsp;</td>
<?php
		}
		foreach($actions as $action_label => $action_url){
			if($action_label == 'Index') continue;
?>
			<td>
				<a class="ui-widget ui-state-default ui-corner-all ui-button-link"<?php if(in_array($action_label, $this->standardActions)) : ?> title="<?php echo $action_label . ' ' . ucfirst($single) ?>"<?php endif ?>

				   href="<?php echo $action_url ?>"<?php if(strtolower($action_label) == 'delete'): ?>

				   onclick="return confirm('Are you sure?');"<?php endif ?>>

					<span class="ui-icon ui-icon<?php if(@$this->actionIcons[$action_label]): ?>-<?php echo $this->actionIcons[$action_label]; ?><?php endif ?>"><?php echo $action_label ?></span>

					<?php echo $action_label ?>

				</a>
			</td>
<?php
		}
?>
		</tr>
<?php echo '<?php endforeach ?>' ?>

	</tbody>
</table>