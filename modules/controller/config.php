<?php

ModuleLoader::load('view');

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

ClassLoader::addRepository('CONTROLLERS', $module_root);
ClassLoader::import('CONTROLLERS');
ClassLoader::import('CONTROLLERS:controllers');

foreach (glob($module_root . 'helpers/*.php') as $filename)
	require_once($filename);

// default controller, action, and view
define('DEFAULT_CONTROLLER', 'index');

define('CONTROLLERS_DIR', $module_root . 'controllers' . DIRECTORY_SEPARATOR);