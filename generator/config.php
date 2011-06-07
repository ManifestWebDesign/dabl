<?php

require_once '../config.php';

ClassLoader::import('LIBRARIES:dabl:generator');

$generator_options = array(
	//target directory for generated table classes
	'model_path' => MODELS_DIR,

	//target directory for generated base table classes
	'base_model_path' => MODELS_BASE_DIR,

	//set to true to generate views
	'view_path' => defined('VIEWS_DIR') ? VIEWS_DIR : null,

	//directory to save controller files in
	'controller_path' => defined('CONTROLLERS_DIR') ? CONTROLLERS_DIR : null
);

$generators = array();

foreach (DBManager::getConnectionNames() as $connection_name) {
	$generator = new DefaultGenerator($connection_name);
	$generator->setOptions($generator_options);
	$generators[$connection_name] = $generator;
}