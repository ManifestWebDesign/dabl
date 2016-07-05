<?php

use Dabl\Generator\DefaultGenerator;
use Dabl\Query\DBManager;

require_once '../../config.php';

$generator_options = array(
	'base_model_parent_class' => 'ApplicationModel',
	'controller_parent_class' => 'ApplicationController'
);

$generators = array();

foreach (DBManager::getConnectionNames() as $connection_name) {
	$generator = new DefaultGenerator($connection_name);
	$generator->setOptions($generator_options);
	$generators[$connection_name] = $generator;
}