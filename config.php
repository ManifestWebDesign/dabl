<?php

//These aren't required, but I find them useful
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);
$config_loaded = true;

//Strip added slashes if needed
if (get_magic_quotes_gpc()) {
    function stripslashes_array($array) { return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array); }
    $_COOKIE = stripslashes_array($_COOKIE);
    $_GET = stripslashes_array($_GET);
    $_POST = stripslashes_array($_POST);
    $_REQUEST = stripslashes_array($_REQUEST);
}

define('ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

set_include_path(ROOT."classes".PATH_SEPARATOR.get_include_path());

require_once 'Module.php';

Module::addRepository('ROOT', substr(ROOT, 0, -1));
Module::import('ROOT:classes');
Module::import('ROOT:classes:dabl');
//Module::import('ROOT:classes:PDO'); //Uncomment if your server doesn't have PDO enabled
Module::import('ROOT:classes:tables');
Module::import('ROOT:classes:tables:base');
Module::import('ROOT:classes:dabl:adapter');

define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'example');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
Module::import('ROOT:classes:dabl:adapter:'.DB_DRIVER);

try{
	//DBAdapter::factory($driver, $dsn, $username, $password)
	$conn = DBAdapter::factory(DB_DRIVER, DB_DRIVER.":host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
	DBManager::addConnection("main", $conn);
}
catch(Exception $e){
	throw new Exception($e->getMessage());
}