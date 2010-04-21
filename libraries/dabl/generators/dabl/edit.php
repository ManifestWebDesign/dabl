<h1><?php echo '<?php echo $'.$single.'->isNew() ? "New" : "Edit" ?>' ?> <?php echo ucfirst($table_name) ?></h1>
<div class="ui-widget-content ui-corner-all">
<form method="POST" action="<?php echo "<?php echo site_url('".$plural."/save') ?>" ?>">
<?php
		if($pk){
?>
	<input type="hidden" name="<?php echo $pk ?>" value="<?php echo '<?php echo htmlentities($'.$single.'->'."get$pk".'()) ?>' ?>" />
<?php
		}
		foreach($this->getColumns($table_name) as $column){
			$column_name = $column->getName();
			if($column_name==$pk)continue;
			$method = "get$column_name";
			$output = '<?php echo htmlentities($'.$single.'->'.$method.'()) ?>';
			$label = $column_name;
			if($column->isForeignKey()){
				$fk = reset($column->getForeignKeys());
				$foreign_table_name = $fk->getForeignTableName();
				$label = ucfirst($foreign_table_name);
				$fk_single = strtolower($foreign_table_name);
				$foreign_method = 'get'.$foreign_table_name.'s';
				$foreign_column_name = reset($fk->getForeignColumns());
				$foreign_column_method = 'get'.$foreign_column_name;
				$foreign_open_foreach = '<?php foreach('.$this->getModelName($foreign_table_name).'::getAll() as $'.$fk_single.'): ?>';
				$foreign_option = '<option <?php if($'.$single.'->get'.$column_name.'() === $'.$fk_single.'->'.$foreign_column_method.'()) echo \'"selected="SELECTED"\' ?> value="<?php echo $'.$fk_single.'->'.$foreign_column_method.'() ?>"><?php echo $'.$fk_single.'->'.$foreign_column_method.'() ?></option>';
				$foreign_close_foreach = '<?php endforeach ?>';
			}
			$input_id = strtolower($single.'_'.$column_name);
?>
	<p>
		<label for="<?php echo $input_id ?>"><?php echo $label ?></label>
<?php
switch($column->getType()){
	case PropelTypes::LONGVARCHAR:
?>
		<textarea id="<?php echo $input_id ?>" name="<?php echo $column_name ?>"><?php echo $output ?></textarea>
<?php
		break;
	default:
		if($column->isForeignKey()){
?>
		<select id="<?php echo $input_id ?>" name="<?php echo $column_name ?>">
		<?php echo $foreign_open_foreach ?>

			<?php echo $foreign_option ?>

		<?php echo $foreign_close_foreach ?>

		</select>
<?php
		}
		else{
?>
		<input id="<?php echo $input_id ?>" type="text" name="<?php echo $column_name ?>" value="<?php echo $output ?>" />
<?php
		}
		break;
}
?>
	</p>
<?php
		}
?>
	<p>
		<span class="ui-state-default ui-corner-all ui-button-link">
			<span class="ui-icon ui-icon-disk"></span>
			<input type="submit" value="<?php echo '<?php echo $'.$single.'->isNew() ? "Save" : "Save Changes" ?>' ?>" />
		</span>
	</p>
</form>
</div>