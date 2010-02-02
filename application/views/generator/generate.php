<?php
foreach($generators as $generator){
	$db_name = $generator->getDBName();
?>
<h2>Generating Files for <?php echo $db_name ?>...</h2>
<?php
	$options = $generator->getOptions();
	$generator->generateModels(@$_REQUEST['Models'][$db_name]);
	$generator->generateViews(@$_REQUEST['Views'][$db_name]);
	$generator->generateControllers(@$_REQUEST['Controllers'][$db_name]);
}
?>
<h2>Including All Model Classes...</h2>
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