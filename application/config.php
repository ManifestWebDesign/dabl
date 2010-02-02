<?php

//useful if other scripts need to know whether this file
//has been included
define('CONFIG_LOADED', true);

//application root
define('ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//default controller, action, and view
define('DEFAULT_CONTROLLER', 'index');

//database connection information
define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'test1');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

//the path to your application that follows the domain name with leading and trailing slashes
//default to /index.php/ if your server doesn't support .htaccess with apache mod_rewrite
define('BASE_URL', '/index.php/');

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);
ini_set('log_errors', true);
ini_set('error_log', ROOT.'logs/error_log');

require_once ROOT.'libraries/Module.php';

//specify directories with classes
Module::addRepository('ROOT', substr(ROOT, 0, -1));
Module::import('ROOT:libraries');
Module::import('ROOT:models');
Module::import('ROOT:controllers');
Module::import('ROOT:models:base');
Module::import('ROOT:libraries:dabl');
Module::import('ROOT:libraries:dabl:adapter');
Module::import('ROOT:libraries:dabl:query');
Module::import('ROOT:libraries:dabl:adapter:'.DB_DRIVER);
//Module::import('ROOT:libraries:PDO'); //Uncomment if your server doesn't have PDO enabled

//attempt to connect to the database
try{
	//DBAdapter::factory($driver, $dsn, $username, $password)
	$conn = DBAdapter::factory(DB_DRIVER, DB_DRIVER.":host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
	DBManager::addConnection(DB_NAME, $conn);
}
catch(Exception $e){
	throw new Exception($e->getMessage());
}

//load functions
foreach (glob(ROOT."functions/*.php") as $filename) require_once($filename);

//Strip added slashes if needed
if (get_magic_quotes_gpc()) strip_request_slashes();