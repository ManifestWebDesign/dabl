<?php
require_once('../../config.php');

//see DABLGenerator::construct() for all default options
$options = array(
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

$generators = array();
foreach(DBManager::getConnectionNames() as $connection_name){
	$generator = new DABLGenerator($connection_name);
	$generator->setOptions($options);
	$generators[$connection_name] = $generator;
}

if(!$generators)
	die('Nothing to generate');