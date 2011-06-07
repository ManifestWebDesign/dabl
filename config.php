<?php

// lets other scripts know that this file has been included
define('CONFIG_LOADED', true);

// directory where this file lives.  Borderline deprecated, so use APP_DIR
define('ROOT', dirname(__FILE__) . '/');

// directory where application lives, usually the same as ROOT
define('APP_DIR', ROOT);

// directory of configurations files
define('CONFIG_DIR', APP_DIR . 'config/');

// directory where libraries are located
define('LIBRARIES_DIR', APP_DIR . 'libraries/');

// directory for logs
define('LOGS_DIR', APP_DIR . 'logs/');

// output errors to brower
ini_set('display_errors', true);

// level of errors to log/display
ini_set('error_reporting', E_ALL);

// log errors
ini_set('log_errors', true);

// file for error logging
ini_set('error_log', LOGS_DIR . 'error_log');

require_once(LIBRARIES_DIR . 'dabl/ClassLoader.php');
require_once(LIBRARIES_DIR . 'dabl/print_r2.php');

ClassLoader::addRepository('LIBRARIES', LIBRARIES_DIR);

ClassLoader::import('LIBRARIES:dabl');

function stripslashes_array($array) {
	return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
}

// Strip added slashes if needed
if (get_magic_quotes_gpc()) {
    $_COOKIE = stripslashes_array($_COOKIE);
    $_GET = stripslashes_array($_GET);
    $_POST = stripslashes_array($_POST);
    $_REQUEST = stripslashes_array($_REQUEST);
}

// load all config files
$config_files = glob(CONFIG_DIR . '*.php');
sort($config_files);
foreach ($config_files as $filename) {
	require_once($filename);
}