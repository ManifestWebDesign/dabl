<?php

ClassLoader::addRepository('DATABASE', LIBRARIES_DIR . 'dabl/database');

ClassLoader::import('DATABASE');
ClassLoader::import('DATABASE:query');
ClassLoader::import('DATABASE:adapter');

if (!class_exists('PDO')) {
	ClassLoader::import('DATABASE:PDO');
}

$db_connections = parse_ini_file(CONFIG_DIR . 'connections.ini', true);

// connect to database(s)
foreach ($db_connections as $connection_name => $db_params) {
	DBManager::addConnection($connection_name, $db_params);
}


// timestamp to use for Created and Updated column values
define('CURRENT_TIMESTAMP', time());
define('MODELS_DIR', APP_DIR . 'models/');
define('MODELS_BASE_DIR', MODELS_DIR . 'base/');

ClassLoader::addRepository('MODELS', MODELS_DIR);
ClassLoader::import('MODELS');
ClassLoader::import('MODELS:base');