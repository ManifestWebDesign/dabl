<?php
require_once 'config.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>DABL::Map Database</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<?php

$options = array(
	//set to true to generate forms
	'generate_forms' => false,

	//set to true to generate forms
	'generate_views' => false,

	//set to true to generate views
	'view_path' => ROOT."views/",

	'generate_controllers' => false,

	'controllers_extend' => 'Controller',

	'pluralize_controllers' => true,

	'controller_path' => ROOT."classes/controllers/",

	'controller_prefix' => '',

	'controller_suffix' => '',

	//target directory for generated table classes
	'form_path' => ROOT."includes/sample_forms/",

	//if attempting to set value of numeric column to empty string, convert it to a zero
	'empty_string_zero' => false,

	//add some logic to the setter methods to not allow column values to be null if the column cannot be null
	'protect_not_null' => true,

	//enforce an upper case first letter of classes
	'cap_class_names' => true,

	//enforce an upper case first letter of get and set methods
	'cap_method_names' => true,

	'class_prefix' => '',

	'class_suffix' => '',

	//target directory for generated table classes
	'extended_class_path' => ROOT."classes/tables/",

	//target directory for generated base table classes
	'base_class_path' => ROOT."classes/tables/base/"
);

try{
	//Use Creole to read database schema to xml DomDocument
	$dbXML = new DBtoXML(DBManager::getConnection("main"), DB_NAME);
	file_put_contents($options['extended_class_path']."schema.xml", $dbXML->getXMLString());
}
catch(Exception $e){
	throw new Exception("Unable to read database.");
}

//Use schema to generate files
$generator = new DABLGenerator("main", $dbXML->getXMLDom());
$generator->generate($options);
?>
</body>
</html>