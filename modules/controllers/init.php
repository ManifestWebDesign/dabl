<?php

ModuleLoader::load('views');
ModuleLoader::load('hooks');

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

require_once $module_root . 'ControllerRoute.php';
require_once $module_root . 'BaseController.php';

require_once $module_root.'helpers/load_controller.php';

Hook::add('handle_request', 'load_controller', 100);