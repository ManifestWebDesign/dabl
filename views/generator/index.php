<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>DABL::Map Database</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script>
function checkAll(name, checked){
	var boxes = document.getElementsByTagName('input');
	var length = boxes.length;
	for(var x=0;x<length;x++){
		var checkbox = boxes[x];
		if(checkbox.type=='checkbox' && checkbox.name.toString().indexOf(name)!==-1)
			checkbox.checked = checked;
	}
}
</script>
</head>
<body>
	<h1>DABL Generator</h1>
	<div>
		Please choose which table to generate for.  Only the base models
		can be overwritten.  Models, views and controllers will not be overwritten if
		they already exist.  Generating a view or controller without generating the model is not recommended.
	</div>
	<br />
	<form action="<?= site_url('generator/generate') ?>" method="POST">
		<input type="hidden" name="action" value="generate" />
		<table>
			<thead>
				<tr>
					<th>Table Name</th>
					<th><input type="checkbox" checked="CHECKED" onclick="checkAll('Models', this.checked)" /> Model</th>
					<th><input type="checkbox" onclick="checkAll('Views', this.checked)" /> View</th>
					<th><input type="checkbox" onclick="checkAll('Controllers', this.checked)" /> Controller</th>
				</tr>
			</thead>
			<tbody>
<?php
foreach($generators as $generator){
	$db_name = $generator->getDBName();
?>
				<tr><th colspan="100"><?php echo $generator->getDBName() ?></th></tr>
<?php
	foreach($generator->getTableNames() as $tableName){
?>
				<tr>
					<td><?php echo $tableName ?></td>
					<td>
						<input checked="CHECKED" type="checkbox" value="<?php echo $tableName ?>" name="Models[<?php echo $db_name ?>][]" />
					</td>
					<td>
						<input type="checkbox" value="<?php echo $tableName ?>" name="Views[<?php echo $db_name ?>][]" />
					</td>
					<td>
						<input type="checkbox" value="<?php echo $tableName ?>" name="Controllers[<?php echo $db_name ?>][]" />
					</td>
				</tr>
<?
	}
}
?>
			</tbody>
		</table>
		<input type="submit" value="Generate Files!" />
	</form>
</body>
</html>