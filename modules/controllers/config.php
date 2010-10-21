<?php

ModuleLoader::load('views');

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

ClassLoader::addRepository('CONTROLLERS', $module_root);
ClassLoader::import('CONTROLLERS');
ClassLoader::import('CONTROLLERS:controllers');

require_once $module_root.'helpers/load_controller.php';