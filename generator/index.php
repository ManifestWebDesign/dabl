<?php require_once('./config.php') ?>
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
			can be overwritten.  Models<? if (defined('IS_MVC') && IS_MVC): ?>, views and controllers<? endif ?> will not be overwritten if
			they already exist.  Generating a view or controller without generating the model is not recommended.
		</div>

		<br />

		<form action="generate.php" method="POST">
			<input type="hidden" name="action" value="generate" />
			<table>
				<tbody>

				<?php foreach ($generators as $connection_name => $generator): ?>

					<tr>
						<th colspan="100">
							<h3>Database: <?php echo $generator->getDBName() ?> (<?php echo $connection_name ?>)</h3>
						</th>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<th align="left">
							<input type="checkbox" checked="checked" onclick="checkAll('Models', '<?php echo $connection_name ?>', this.checked)" />
							Models
						</th>
						<? if (defined('IS_MVC') && IS_MVC): ?>
						<th align="left">
							<input type="checkbox" onclick="checkAll('Views', '<?php echo $connection_name ?>', this.checked)" />
							Views
						</th>
						<th align="left">
							<input type="checkbox" onclick="checkAll('Controllers', '<?php echo $connection_name ?>', this.checked)" />
							Controllers
						</th>
						<? endif ?>
					</tr>

					<?php foreach ($generator->getTableNames() as $tableName): ?>

						<tr>
							<td><?php echo $tableName ?></td>
							<td>
								<input type="checkbox" value="<?php echo $tableName ?>" name="Models[<?php echo $connection_name ?>][]" checked="checked" />
							</td>
							<? if (defined('IS_MVC') && IS_MVC): ?>
							<td>
								<input type="checkbox" value="<?php echo $tableName ?>" name="Views[<?php echo $connection_name ?>][]" />
							</td>
							<td>
								<input type="checkbox" value="<?php echo $tableName ?>" name="Controllers[<?php echo $connection_name ?>][]" />
							</td>
							<? endif ?>
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