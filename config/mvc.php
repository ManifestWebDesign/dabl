<?php

define('IS_MVC', true);

ClassLoader::addRepository('MVC', LIBRARIES_DIR . 'dabl/mvc');

$helpers = glob(LIBRARIES_DIR . 'dabl/mvc/helpers/*.php');
sort($helpers);
foreach ($helpers as $helper) {
	require_once $helper;
}

// start the session
session_start();

// the browser path to this application.  it should be:
// a full url with http:// and a trailing slash OR
// a subdirectory with leading and trailing slashes
define('BASE_URL', '/');

// directory for public html files that are directly exposed to the web server
define('PUBLIC_DIR', APP_DIR . 'public/');

// default controller
define('DEFAULT_CONTROLLER', 'index');

// controllers directory
define('CONTROLLERS_DIR', APP_DIR . 'controllers/');
ClassLoader::addRepository('CONTROLLERS', CONTROLLERS_DIR);

// views directory
define('VIEWS_DIR', APP_DIR . 'views/');

// default timestamp format for views
define('VIEW_TIMESTAMP_FORMAT', 'n/j/Y g:i a');

// default date format for views
define('VIEW_DATE_FORMAT', 'n/j/Y');

// hook to handle route
define('HOOK_LOAD_ROUTE', 'HOOK_LOAD_ROUTE');
Hook::add(HOOK_LOAD_ROUTE, array('ControllerRoute', 'load'), 100);