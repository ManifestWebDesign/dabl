<?php

require_once '../config.php';

// string with url requested by visitor.  Usually in the form of:
// controller/action/arg1/arg2?param1=value1
// @see public/.htaccess
$requested_route = @$_GET['_url'];

$http_verb = strtolower($_SERVER['REQUEST_METHOD']);

unset($_GET['_url'], $_REQUEST['_url']);

// handle the request with whatever Hooks have been set for that purpose
// @see config/controllers.php
try {
	Controller::load($requested_route, $http_verb);
} catch (FileNotFoundException $e) {
	error_log($e->getMessage());
	echo '<h1>File Not Found</h1>';
	die($e->getMessage());
}