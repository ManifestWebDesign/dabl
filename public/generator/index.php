<?php require_once './init.php' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>DABL Generator</title>
		<link type="text/css" rel="stylesheet" href="../css/style.css" />
	</head>
	<body>
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
	Please choose which tables to generate for.  Only the base models
	can be overwritten.  <?php if(ModuleLoader::isLoaded('views') && ModuleLoader::isLoaded('controllers')): ?>Models, views and controllers will not be overwritten if
	they already exist.  Generating a view or controller without generating the model is not recommended.
	<?php endif ?>
</div>

<br />

<form action="generate.php" method="POST">
	<input type="hidden" name="action" value="generate" />
	<table>
		<tbody>

<?php foreach($generators as $connection_name => $generator): ?>

			<tr>
				<th colspan="100">
					<h3>Database: <?php echo $generator->getDBName() ?> (<?php echo $connection_name ?>)</h3>
				</th>
			</tr>
	<?php if(ModuleLoader::isLoaded('views') || ModuleLoader::isLoaded('controllers')): ?>
			<tr>
				<th>&nbsp;</th>
				<th>
					<input type="checkbox" checked="checked" onclick="checkAll('Models', '<?php echo $connection_name ?>', this.checked)" />
					Models
				</th>
		<?php if(ModuleLoader::isLoaded('views')): ?>
				<th>
					<input type="checkbox" onclick="checkAll('Views', '<?php echo $connection_name ?>', this.checked)" />
					Views
				</th>
		<?php endif ?>
		<?php if(ModuleLoader::isLoaded('controllers')): ?>
				<th>
					<input type="checkbox" onclick="checkAll('Controllers', '<?php echo $connection_name ?>', this.checked)" />
					Controllers
				</th>
			</tr>
		<?php endif ?>
	<?php endif ?>

	<?php foreach($generator->getTableNames() as $tableName): ?>

			<tr>
				<td><?php echo $tableName ?></td>
				<td>
					<input type="checkbox" value="<?php echo $tableName ?>" name="Models[<?php echo $connection_name ?>][]" checked="checked" />
				</td>
		<?php if(ModuleLoader::isLoaded('views')): ?>
				<td>
					<input type="checkbox" value="<?php echo $tableName ?>" name="Views[<?php echo $connection_name ?>][]" />
				</td>
		<?php endif ?>
		<?php if(ModuleLoader::isLoaded('controllers')): ?>
				<td>
					<input type="checkbox" value="<?php echo $tableName ?>" name="Controllers[<?php echo $connection_name ?>][]" />
				</td>
		<?php endif ?>
			</tr>
	<?php endforeach ?>

			<tr><td>&nbsp;</td></tr>

<?php endforeach ?>

		</tbody>
	</table>
	<input type="submit" value="Generate" />
</form>
	</body>
</html>