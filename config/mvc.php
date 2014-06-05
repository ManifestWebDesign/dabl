<?php

define('IS_MVC', true);

ClassLoader::addRepository('MVC', LIBRARIES_DIR . 'dabl/mvc');

$helpers = glob(LIBRARIES_DIR . 'dabl/mvc/helpers/*.php');
sort($helpers);
foreach ($helpers as $helper) {
	require_once $helper;
}

/** Session * */
// start the session
$sn = session_name();
$sessid = null;
//Find the session either in the cookie or the $_GET
if (isset($_COOKIE[$sn])) {
	$sessid = $_COOKIE[$sn];
} else if (isset($_GET[$sn])) {
	$sessid = $_GET[$sn];
}
//Check for valid sessionid
if ($sessid && !preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $sessid)) {
	//If invalid, delete the cookie and redirect to /
	$params = session_get_cookie_params();
	setcookie(
		session_name(),
		'',
		time() - 42000,
		$params['path'],
		$params['domain'],
		$params['secure'],
		$params['httponly']
	);

	if (!headers_sent()) {
		redirect('/');
	}
	throw new RuntimeException('Session ID was invalid and couldn\'t recover');
}
$result = @session_start();
if (!$result) {
	if (!headers_sent()) {
		redirect('/');
	}
	throw new RuntimeException('Session ID was invalid and couldn\'t recover');
}

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
