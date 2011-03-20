<?php

ModuleLoader::load('views');

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

require_once $module_root . 'ControllerRoute.php';
require_once $module_root . 'BaseController.php';