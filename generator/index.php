<?php require_once('./config.php') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>DABL Generator</title>
		<link type="text/css" rel="stylesheet" href="style.css" />
		<script>
			function checkAll(name, connection, checked){
				var boxes = document.getElementsByTagName('input'),
				length = boxes.length,
				x;
				for(x = 0; x < length; ++x){
					var checkbox = boxes[x];
					if(checkbox.type == 'checkbox' && checkbox.name.toString().indexOf(connection) !== -1 && checkbox.name.toString().indexOf(name) !== -1)
						checkbox.checked = checked;
				}
			}
		</script>
	</head>
	<body>
		<div class="main-wrapper">
			<h1>DABL Generator</h1>

			<p>
				Please choose which tables to generate for.  Only the base models
				can be overwritten.  Models<?php if (defined('IS_MVC') && IS_MVC): ?>, views and controllers<?php endif ?> will not be overwritten if
				they already exist.  Generating a view or controller without generating the model is not recommended.
			</p>

			<form action="generate.php" method="POST">
				<input type="hidden" name="action" value="generate" />
				<?php foreach ($generators as $connection_name => $generator): ?>
					<h2>Database: <?php echo $generator->getDBName() ?> (<?php echo $connection_name ?>)</h2>
					<div class="ui-widget-content">
						<table class="object-grid">
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th align="left">
										<label>
											<input type="checkbox" checked="checked" onclick="checkAll('Models', '<?php echo $connection_name ?>', this.checked)" />
											Models
										</label>
									</th>
									<th align="left">
										<label>
											<input type="checkbox" onclick="checkAll('ModelQueries', '<?php echo $connection_name ?>', this.checked)" />
											Model Queries
										</label>
									</th>
									<?php if (defined('IS_MVC') && IS_MVC): ?>
										<th align="left">
											<label>
												<input type="checkbox" onclick="checkAll('Views', '<?php echo $connection_name ?>', this.checked)" />
												Views
											</label>
										</th>
										<th align="left">
											<label>
												<input type="checkbox" onclick="checkAll('Controllers', '<?php echo $connection_name ?>', this.checked)" />
												Controllers
											</label>
										</th>
									<?php endif ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($generator->getTableNames() as $table_name): ?>

									<tr>
										<td><strong><?php echo $table_name ?></strong></td>
										<td>
											<input type="checkbox" value="<?php echo $table_name ?>" name="Models[<?php echo $connection_name ?>][]" checked="checked" />
										</td>
										<td>
											<input type="checkbox" value="<?php echo $table_name ?>" name="ModelQueries[<?php echo $connection_name ?>][]" />
										</td>
										<?php if (defined('IS_MVC') && IS_MVC): ?>
											<td>
												<input type="checkbox" value="<?php echo $table_name ?>" name="Views[<?php echo $connection_name ?>][]" />
											</td>
											<td>
												<input type="checkbox" value="<?php echo $table_name ?>" name="Controllers[<?php echo $connection_name ?>][]" />
											</td>
										<?php endif ?>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>
				<?php endforeach ?>
				<input type="submit" value="Generate" />
			</form>
		</div>
	</body>
</html>