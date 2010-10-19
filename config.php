<?php

//lets other scripts know that this file has been included
define('CONFIG_LOADED', true);

//application root
define('ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//default controller, action, and view
define('DEFAULT_CONTROLLER', 'index');

//default timestamp format for views
define('VIEW_TIMESTAMP_FORMAT', 'n/j/Y g:i a');

//timestamp to use for Created and Updated column values
define('CURRENT_TIME', time());

//default date format for views
define('VIEW_DATE_FORMAT', 'n/j/Y');

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

//load ClassLoader class for magic class loading
require_once ROOT.'libraries/ClassLoader.php';

//specify directories that contain classes
ClassLoader::addRepository('ROOT', ROOT);
ClassLoader::import('ROOT:models');
ClassLoader::import('ROOT:models:base');
ClassLoader::import('ROOT:libraries:dabl');
ClassLoader::import('ROOT:libraries:dabl:query');
ClassLoader::import('ROOT:controllers');
ClassLoader::import('ROOT:libraries');
ClassLoader::import('ROOT:libraries:dabl:adapter');
if(!class_exists('PDO')) ClassLoader::import('ROOT:libraries:PDO');

$db_connections['my_connection_name'] = array(
	'driver' => 'mysql',
	'host' => 'localhost',
	'dbname' => 'test',
	'user' => 'root',
	'password' => ''
);

//connect to database(s)
foreach($db_connections as $connection_name => $db_params){
	ClassLoader::import('ROOT:libraries:dabl:adapter:'.$db_params['driver']);
	DBManager::addConnection($connection_name, $db_params);
}

//load functions
foreach (glob(ROOT."helpers/*.php") as $filename) require_once($filename);

//Strip added slashes if needed
if (get_magic_quotes_gpc()) strip_request_slashes();

//start the session
session_start();