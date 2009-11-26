<?php
require_once 'config.php';

//see DABLGenerator::construct() for default options
$options = array(
	'title_case' => true,

	//target directory for generated base table classes
	'base_model_path' => ROOT."models/base/",

	//target directory for generated table classes
	'model_path' => ROOT."models/",

	//set to true to generate views
	'view_path' => ROOT."views/",

	//directory to save controller files in
	'controller_path' => ROOT."controllers/",
);

Module::import('ROOT:libraries:dabl:generators');
$generator = new DABLGenerator("main", DB_NAME);
$generator->setOptions($options);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>DABL::Map Database</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script>
function checkAll(name, checked){
	var boxes = document.getElementsByTagName('input');
	var length = boxes.length;
	for(var x=0; x<length; x++){
		var checkbox = boxes[x];
		if(checkbox.type=='checkbox' && checkbox.name==name)
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
	<form action="" method="POST">
		<input type="hidden" name="action" value="generate" />
		<table>
			<tr>
				<th>Table Name</th>
				<th><input type="checkbox" checked="CHECKED" onclick="checkAll('Models[]', this.checked)" /> Model</th>
				<th><input type="checkbox" onclick="checkAll('Views[]', this.checked)" /> View</th>
				<th><input type="checkbox" onclick="checkAll('Controllers[]', this.checked)" /> Controller</th>
			</tr>
<?php
foreach($generator->getTableNames() as $tableName){
?>
		<tr>
			<td><?php echo $tableName ?></td>
			<td>
				<input checked="CHECKED" type="checkbox" value="<?php echo $tableName ?>" name="Models[]" />
			</td>
			<td>
				<input type="checkbox" value="<?php echo $tableName ?>" name="Views[]" />
			</td>
			<td>
				<input type="checkbox" value="<?php echo $tableName ?>" name="Controllers[]" />
			</td>
		</tr>
<?
}
?>
		</table>
		<input type="submit" value="Generate Files!" />
	</form>
<?php
if(@$_REQUEST['action']=='generate'){
?>
	<h2>Generating Files...</h2>
<?php
	$options = $generator->getOptions();
	$generator->generateModels(@$_REQUEST['Models']);
	$generator->generateViews(@$_REQUEST['Views']);
	$generator->generateControllers(@$_REQUEST['Controllers']);
?>
<div style="float:left;width:50%">
	<strong>Base<br /></strong>
<?php
	foreach (glob($options['base_model_path']."*.php") as $filename){
		echo basename($filename)."<br />";
		require_once($filename);
	}
?>
</div>
<div style="float:left;width:50%">
	<strong>Extended<br /></strong>
<?php
	foreach (glob($options['model_path']."*.php") as $filename){
		echo basename($filename)."<br />";
		require_once($filename);
	}
?>
</div>
<div style="text-align:center;color:green;font-weight:bold">Success.</div>
<?php
}
?>
</body>
</html>