<?php

// lets other scripts know that this file has been included
define('CONFIG_LOADED', true);

// application root
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// application root
define('APP_ROOT', ROOT);

// directory of configurations files
define('CONFIG_DIR', APP_ROOT . 'config' . DIRECTORY_SEPARATOR);

// directory where modules are located
define('MODULES_DIR', APP_ROOT . 'modules' . DIRECTORY_SEPARATOR);

// directory for public html files that are directly exposed to the web server
define('PUBLIC_DIR', APP_ROOT . 'public' . DIRECTORY_SEPARATOR);

// directory for logs
define('LOGS_DIR', APP_ROOT . 'log' . DIRECTORY_SEPARATOR);

// output errors to brower
ini_set('display_errors', true);

// level of errors to log/display
ini_set('error_reporting', E_ALL);

// log errors
ini_set('log_errors', true);

// file for error logging
ini_set('error_log', LOGS_DIR . 'error_log');

// load ClassLoader class for magic class loading
require_once MODULES_DIR . '/loaders/config.php';

ModuleLoader::loadAll();

foreach (glob(CONFIG_DIR . '*.php') as $filename)
	require_once($filename);