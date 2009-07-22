<?php

/**
 * Last Modified May 5th 2009
 */

$config_loaded = true;

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

define('ROOT', dirname(__FILE__)."/");

foreach (glob(ROOT."functions/*.php") as $filename) require_once($filename);

MODULE::addRepository('ROOT', substr(ROOT, 0, -1));
MODULE::import('ROOT:classes');
MODULE::import('ROOT:classes:dabl');
MODULE::import('ROOT:classes:PDO');
MODULE::import('ROOT:classes:tables');
MODULE::import('ROOT:classes:tables:base');
MODULE::import('ROOT:classes:dabl:adapter');

define('DB_HOST', 'localhost');
define('DB_NAME', "test");
define('DB_USER', "root");
define('DB_PASSWORD', '');

try{
	$conn = DBAdapter::factory("mysql", "mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
}
catch(Exception $e){
	throw new Exception($e->getMessage());
}

DB::addConnection("main", $conn);
