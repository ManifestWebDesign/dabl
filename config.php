<?php

if (defined('CONFIG_LOADED') && CONFIG_LOADED === true) {
	return;
}

date_default_timezone_set(@date_default_timezone_get());

// lets other scripts know that this file has been included
define('CONFIG_LOADED', true);

// directory where application lives, usually the same as ROOT
define('APP_DIR', __DIR__ . '/');

// directory of configurations files
define('CONFIG_DIR', APP_DIR . 'config/');

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

if (is_file(APP_DIR . 'vendor/autoload.php')) {
	require_once APP_DIR . 'vendor/autoload.php';
} else {
	throw new RuntimeException('Vendor directory missing.  Please run "composer install".');
}

// load all config files
$config_files = glob(CONFIG_DIR . '*.php');
sort($config_files);
foreach ($config_files as $filename) {
	require_once($filename);
}