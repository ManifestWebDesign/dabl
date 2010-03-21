<script>
function checkAll(name, connection, checked){
	var boxes = document.getElementsByTagName('input');
	var length = boxes.length;
	for(var x=0;x<length;x++){
		var checkbox = boxes[x];
		if(checkbox.type=='checkbox' && checkbox.name.toString().indexOf(connection)!==-1 && checkbox.name.toString().indexOf(name)!==-1)
			checkbox.checked = checked;
	}
}
</script>
<h1>DABL Generator</h1>
<div>
	Please choose which table to generate for.  Only the base models
	can be overwritten.  Models, views and controllers will not be overwritten if
	they already exist.  Generating a view or controller without generating the model is not recommended.
</div>
<br />
<form action="<?= site_url('generator/generate') ?>/" method="POST">
	<input type="hidden" name="action" value="generate" />
	<table>
		<tbody>
				
<?php foreach($generators as $connection_name => $generator): ?>

			<tr><th colspan="100"><h3>Database: <?php echo $generator->getDBName() ?> (<?php echo $connection_name ?>)</h3></th></tr>
			<tr>
				<th>&nbsp;</th>
				<th><input type="checkbox" checked="CHECKED" onclick="checkAll('Models', '<?php echo $connection_name ?>', this.checked)" /> Models</th>
				<th><input type="checkbox" onclick="checkAll('Views', '<?php echo $connection_name ?>', this.checked)" /> Views</th>
				<th><input type="checkbox" onclick="checkAll('Controllers', '<?php echo $connection_name ?>', this.checked)" /> Controllers</th>
			</tr>

	<?php foreach($generator->getTableNames() as $tableName): ?>

			<tr>
				<td><?php echo $tableName ?></td>
				<td>
					<input type="checkbox" value="<?php echo $tableName ?>" name="Models[<?php echo $connection_name ?>][]" checked="CHECKED" />
				</td>
				<td>
					<input type="checkbox" value="<?php echo $tableName ?>" name="Views[<?php echo $connection_name ?>][]" />
				</td>
				<td>
					<input type="checkbox" value="<?php echo $tableName ?>" name="Controllers[<?php echo $connection_name ?>][]" />
				</td>
			</tr>
	<?php endforeach ?>

			<tr><td>&nbsp;</td></tr>
				
<?php endforeach ?>

		</tbody>
	</table>
	<input type="submit" value="Generate Files!" />
</form>