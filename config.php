<?php

//lets other scripts know that this file has been included
define('CONFIG_LOADED', true);

//application root
define('ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//default controller, action, and view
define('DEFAULT_CONTROLLER', 'index');

//the path to your application that follows the domain name with leading and trailing slashes
define('BASE_URL', '/');

//output errors to brower
ini_set('display_errors', true);

//level of errors to log/display
ini_set('error_reporting', E_ALL);

//log errors
ini_set('log_errors', true);

//file for error logging
ini_set('error_log', ROOT.'logs/error_log');

//load Module class for magic class loading
require_once ROOT.'libraries/Module.php';

//specify directories that contain classes
Module::addRepository('ROOT', ROOT);
Module::import('ROOT:models');
Module::import('ROOT:models:base');
Module::import('ROOT:libraries:dabl:query');
Module::import('ROOT:controllers');
Module::import('ROOT:libraries');
Module::import('ROOT:libraries:dabl');
Module::import('ROOT:libraries:dabl:adapter');
if(!class_exists('PDO')) Module::import('ROOT:libraries:PDO');

$db_connections['my_connection_name'] = array(
	'driver' => 'mysql',
	'host' => 'localhost',
	'dbname' => 'test',
	'user' => 'root',
	'password' => ''
);

//connect to database(s)
foreach($db_connections as $connection_name => $db_params){
	Module::import('ROOT:libraries:dabl:adapter:'.$db_params['driver']);
	DBManager::addConnection($connection_name, $db_params);
}

//load functions
foreach (glob(ROOT."helpers/*.php") as $filename) require_once($filename);

//Strip added slashes if needed
if (get_magic_quotes_gpc()) strip_request_slashes();

//start the session
session_start();