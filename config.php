<?php

// lets other scripts know that this file has been included
define('CONFIG_LOADED', true);

// application root
define('ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

// output errors to brower
ini_set('display_errors', true);

// level of errors to log/display
ini_set('error_reporting', E_ALL);

// log errors
ini_set('log_errors', true);

// file for error logging
ini_set('error_log', ROOT.'logs/error_log');

// load ClassLoader class for magic class loading
require_once ROOT.'libraries/ClassLoader.php';

// specify directories that contain classes
ClassLoader::addRepository('ROOT', ROOT);
ClassLoader::import('ROOT:libraries');

ModuleLoader::setModuleRoot(ROOT . 'modules');
ModuleLoader::loadAll();