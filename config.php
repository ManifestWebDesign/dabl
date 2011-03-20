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

// directory for logs
define('LOGS_DIR', APP_DIR . 'logs' . DIRECTORY_SEPARATOR);

// output errors to brower
ini_set('display_errors', true);

// level of errors to log/display
ini_set('error_reporting', E_ALL);

// log errors
ini_set('log_errors', true);

// file for error logging
ini_set('error_log', LOGS_DIR . 'error_log');

// load helper classes for loading modules and classes
$MODULE_DIR = MODULES_DIR . 'loaders/';
$MODULE_INIT_SCRIPT = $MODULE_DIR . 'init.php';
require_once $MODULE_INIT_SCRIPT;

// load all modules
ModuleLoader::setModulesDir(MODULES_DIR);
ModuleLoader::loadAll();

// load all config files
$config_files = glob(CONFIG_DIR . '*.php');
sort($config_files);
foreach ($config_files as $filename) {
	require_once($filename);
}