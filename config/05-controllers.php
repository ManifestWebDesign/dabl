<?php

// default controller, action, and view
define('DEFAULT_CONTROLLER', 'index');

define('CONTROLLERS_DIR', APP_DIR . 'controllers/');

ClassLoader::addRepository('CONTROLLERS', CONTROLLERS_DIR);
ClassLoader::import('CONTROLLERS');

ClassLoader::import('LIBRARIES:dabl:controllers');

define('HOOK_LOAD_ROUTE', 'HOOK_LOAD_ROUTE');
Hook::add(HOOK_LOAD_ROUTE, array('ControllerRoute', 'load'), 100);