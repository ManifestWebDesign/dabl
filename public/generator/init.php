<?php

require_once('../../config.php');

$generators = array();

foreach (DBManager::getConnectionNames() as $connection_name) {
	$generator = new DefaultGenerator($connection_name);
	$generators[$connection_name] = $generator;
}

if (!$generators) {
	die('Nothing to generate');
}