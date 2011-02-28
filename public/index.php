<?php

require_once '../config.php';

// string with url requested by visitor.  Usually in the form of:
// controller/action/arg1/arg2?param1=value1
$request = @$_GET['url'];

// modify request using any Filters that have been set for that purpose
$request = Filter::call('filter_request', $request);

// handle the request with whatever Hooks have been set for that purpose
Hook::call('handle_request', $request);