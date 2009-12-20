<?php

require_once 'config.php';

$uri = @$_SERVER['REQUEST_URI'];
if(BASE_URL!='/')
	$uri = str_replace(BASE_URL, '', $uri);
$uri = trim($uri, '/');
$query_string_parts = explode('?', $uri);
$route = @$query_string_parts[0] ? $query_string_parts[0] : DEFAULT_CONTROLLER;

load_controller($route);