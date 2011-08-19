<?php

require_once '../config.php';

// string with url requested by visitor.  Usually in the form of:
// controller/action/arg1/arg2?param1=value1
// @see public/.htaccess
$requested_route = array_shift(explode('?', $_SERVER['REQUEST_URI'], 2));

// handle the request with whatever Hooks have been set for that purpose
// @see config/controllers.php
Hook::call(HOOK_LOAD_ROUTE, $requested_route);