<?php require_once('./config.php') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>DABL Generator</title>
		<link type="text/css" rel="stylesheet" href="../css/style.css" />
	</head>
	<body>
<?php
foreach ($generators as $connection_name => $generator) {
?>
	<h2>Generating Files for connection: <?php echo $connection_name ?>...</h2>
	<?php
	$options = $generator->getOptions();
	$generator->generateModels(@$_REQUEST['Models'][$connection_name]);
	$generator->generateViews(@$_REQUEST['Views'][$connection_name]);
	$generator->generateControllers(@$_REQUEST['Controllers'][$connection_name]);
	if (isset($generator->warnings)) {
		foreach ($generator->warnings as $warning)
			echo "$warning<br/>";
	}
}
?>
		<h2>Including All Model Classes...</h2>
		<div style="float:left;width:50%">
			<strong>Base<br /></strong>
<?php
foreach (glob($options['base_model_path'] . "*.php") as $filename) {
	echo basename($filename) . "<br />";
	require_once($filename);
}
?>
		</div>
		<div style="float:left;width:50%">
			<strong>Extended<br /></strong>
<?php
foreach (glob($options['model_path'] . "*.php") as $filename) {
	echo basename($filename) . "<br />";
	require_once($filename);
}
?>
		</div>
		<div style="text-align:center;color:green;font-weight:bold">Success.</div>
	</body>
</html>