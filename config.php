<?php

// lets other scripts know that this file has been included
define('CONFIG_LOADED', true);

// directory where this file lives.  Borderline deprecated, so use APP_DIR
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// directory where application lives, usually the same as ROOT
define('APP_DIR', ROOT);

// directory of configurations files
define('CONFIG_DIR', APP_DIR . 'config' . DIRECTORY_SEPARATOR);

// directory where modules are located
define('MODULES_DIR', APP_DIR . 'modules' . DIRECTORY_SEPARATOR);

// directory for public html files that are directly exposed to the web server
define('PUBLIC_DIR', APP_DIR . 'public' . DIRECTORY_SEPARATOR);

// directory for logs
define('LOGS_DIR', APP_DIR . 'log' . DIRECTORY_SEPARATOR);

// output errors to brower
ini_set('display_errors', true);

// level of errors to log/display
ini_set('error_reporting', E_ALL);

// log errors
ini_set('log_errors', true);

// file for error logging
ini_set('error_log', LOGS_DIR . 'error_log');

// load ClassLoader class for magic class loading
require_once MODULES_DIR . '/loaders/init.php';

ModuleLoader::loadAll();

Hook::call('after_modules_loaded');

$config_files = glob(CONFIG_DIR . '*.php');

sort($config_files);

foreach ($config_files as $filename)
	require_once($filename);

//print_r2($config_files);

Hook::call('after_config_loaded');