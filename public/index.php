<?php

require_once '../config.php';

// string with url requested by visitor.  Usually in the form of:
// controller/action/arg1/arg2?param1=value1
$requested_route = @$_GET['url'];

// handle the request with whatever Hooks have been set for that purpose
Hook::call(HOOK_LOAD_ROUTE, $requested_route);