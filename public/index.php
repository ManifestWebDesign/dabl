<?php

require_once '../config.php';

// string with url requested by visitor.  Usually in the form of: controller/action/arg1/arg2?param1=value1
$request = @$_GET['url'];

$request = Hook::filter('filter_request', array($request));

Hook::call('handle_request', array($request));