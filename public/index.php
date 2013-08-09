<?php

require_once '../config.php';

// string with url requested by visitor.  Usually in the form of:
// controller/action/arg1/arg2?param1=value1
// @see public/.htaccess
$requested_route = @$_GET['_url'];

// clear params used for routing
unset($_GET['_url'], $_REQUEST['_url']);

$headers = get_request_headers();

// transfer posted json data to global request data arrays
if (stripos(@$headers['Content-Type'], 'application/json') !== false) {
	$data = file_get_contents('php://input');
	$json_data = json_decode($data, true);
	if (is_array($json_data)) {
		$_REQUEST = array_merge($_REQUEST, $json_data);
		$_POST = $json_data;
	}
}

try {
	ApplicationController::load($requested_route, $headers, $_REQUEST);
} catch (FileNotFoundException $e) {
	error_log($e->getMessage());
	echo '<h1>File Not Found</h1>';
	die($e->getMessage());
}