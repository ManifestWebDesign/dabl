<?php

ModuleLoader::load('views');
ModuleLoader::load('session');

require_once $MODULE_DIR . 'ControllerRoute.php';
require_once $MODULE_DIR . 'BaseController.php';