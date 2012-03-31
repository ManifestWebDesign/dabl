<?php

require_once '../config.php';

// string with url requested by visitor.  Usually in the form of:
// controller/action/arg1/arg2?param1=value1
// @see public/.htaccess
$requested_route = @$_GET['_url'];

unset($_GET['_url'], $_REQUEST['_url']);

// handle the request with whatever Hooks have been set for that purpose
// @see config/controllers.php
try {
	Hook::call(HOOK_LOAD_ROUTE, $requested_route);
} catch (FileNotFoundException $e) {
	error_log($e->getMessage());
	echo '<h1>File Not Found</h1>';
	die($e->getMessage());
}