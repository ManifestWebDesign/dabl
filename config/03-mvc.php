<?php

ClassLoader::addRepository('MVC', LIBRARIES_DIR . 'dabl/mvc');


define('IS_MVC', true);


/** Session **/

// start the session
session_start();

require_once LIBRARIES_DIR . 'dabl/mvc/session/persist.php';



/** Hooks **/
ClassLoader::import('MVC:hooks');



/** Request **/

// the browser path to this application.  it should be:
// a full url with http:// and a trailing slash OR
// a subdirectory with leading and trailing slashes
define('BASE_URL', '/');
// directory for public html files that are directly exposed to the web server
define('PUBLIC_DIR', APP_DIR . 'public/');

$helpers = glob(LIBRARIES_DIR . 'dabl/mvc/request/*.php');
sort($helpers);
foreach ($helpers as $helper) {
	require_once $helper;
}



/** Controllers **/

// default controller, action, and view
define('DEFAULT_CONTROLLER', 'index');
define('CONTROLLERS_DIR', APP_DIR . 'controllers/');

ClassLoader::addRepository('CONTROLLERS', CONTROLLERS_DIR);
ClassLoader::import('CONTROLLERS');
ClassLoader::import('MVC:controllers');

define('HOOK_LOAD_ROUTE', 'HOOK_LOAD_ROUTE');
Hook::add(HOOK_LOAD_ROUTE, array('ControllerRoute', 'load'), 100);



/** Views **/

require_once LIBRARIES_DIR . 'dabl/mvc/views/load_view.php';

$helpers = glob(LIBRARIES_DIR . 'dabl/mvc/format/*.php');
sort($helpers);
foreach ($helpers as $helper) {
	require_once $helper;
}

// default timestamp format for views
define('VIEW_TIMESTAMP_FORMAT', 'n/j/Y g:i a');
// default date format for views
define('VIEW_DATE_FORMAT', 'n/j/Y');
define('VIEWS_DIR', APP_DIR . 'views/');



