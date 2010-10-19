<?php

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;
ClassLoader::addRepository('DATABASE', $module_root);

ClassLoader::import('DATABASE');
ClassLoader::import('DATABASE:query');
ClassLoader::import('DATABASE:adapter');

if (!class_exists('PDO'))
	ClassLoader::import('DATABASE:PDO');

$db_connections = parse_ini_file($module_root . 'connections.ini', true);

// connect to database(s)
foreach ($db_connections as $connection_name => $db_params) {
	ClassLoader::import('DATABASE:adapter:' . $db_params['driver']);
	DBManager::addConnection($connection_name, $db_params);
}