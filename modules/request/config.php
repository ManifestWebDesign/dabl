<?php

$module_root = dirname(__FILE__).DIRECTORY_SEPARATOR;

foreach (glob($module_root . 'helpers/*.php') as $filename)
	require_once($filename);

// the path to your application that follows the domain name with leading and trailing slashes
define('BASE_URL', '/');

// Strip added slashes if needed
if (get_magic_quotes_gpc())
	strip_request_slashes();