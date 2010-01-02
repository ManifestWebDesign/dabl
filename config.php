<?php

define('CONFIG_LOADED', true);
$root = str_replace('C:', '', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('ROOT', $root);
define('DEFAULT_CONTROLLER', 'index');
define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'test');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('BASE_URL', '/dabl/');

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);
ini_set('log_errors', true);
ini_set('error_log', ROOT.'error_log');

require_once ROOT.'libraries/Module.php';

Module::addRepository('ROOT', substr(ROOT, 0, -1));
Module::import('ROOT:libraries');
Module::import('ROOT:models');
Module::import('ROOT:controllers');
Module::import('ROOT:models:base');
Module::import('ROOT:libraries:dabl');
Module::import('ROOT:libraries:dabl:adapter');
Module::import('ROOT:libraries:dabl:query');
Module::import('ROOT:libraries:dabl:adapter:'.DB_DRIVER);
//Module::import('ROOT:library:PDO'); //Uncomment if your server doesn't have PDO enabled

try{
	//DBAdapter::factory($driver, $dsn, $username, $password)
	$conn = DBAdapter::factory(DB_DRIVER, DB_DRIVER.":host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
	DBManager::addConnection("main", $conn);
}
catch(Exception $e){
	throw new Exception($e->getMessage());
}

//Strip added slashes if needed
if (get_magic_quotes_gpc()) {
    function stripslashes_array($array) { return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array); }
    $_COOKIE = stripslashes_array($_COOKIE);
    $_GET = stripslashes_array($_GET);
    $_POST = stripslashes_array($_POST);
    $_REQUEST = stripslashes_array($_REQUEST);
}

//load functions
foreach (glob(ROOT."functions/*.php") as $filename) require_once($filename);