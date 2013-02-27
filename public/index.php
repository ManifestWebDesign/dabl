<?php

require_once '../config.php';

// string with url requested by visitor.  Usually in the form of:
// controller/action/arg1/arg2?param1=value1
// @see public/.htaccess
$requested_route = @$_GET['_url'];

if (!empty($_REQUEST['_method'])) {
	$http_verb = $_REQUEST['_method'];
} elseif (!empty($_SERVER['REQUEST_METHOD'])) {
	$http_verb = $_SERVER['REQUEST_METHOD'];
}

unset($_GET['_url'], $_REQUEST['_url'], $_GET['_method'], $_REQUEST['_method']);

try {
	ApplicationController::load($requested_route, get_request_headers(), $http_verb);
} catch (FileNotFoundException $e) {
	error_log($e->getMessage());
	echo '<h1>File Not Found</h1>';
	die($e->getMessage());
}