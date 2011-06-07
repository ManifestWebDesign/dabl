<?php

ClassLoader::import('LIBRARIES:dabl:generator');

$generators = array();

$options = array(
	//target directory for generated table classes
	'model_path' => MODELS_DIR,

	//target directory for generated base table classes
	'base_model_path' => MODELS_BASE_DIR,

	//set to true to generate views
	'view_path' => VIEWS_DIR,

	//directory to save controller files in
	'controller_path' => CONTROLLERS_DIR
);

foreach (DBManager::getConnectionNames() as $connection_name) {
	$generator = new DefaultGenerator($connection_name);
	$generator->setOptions($options);
	$generators[$connection_name] = $generator;
}