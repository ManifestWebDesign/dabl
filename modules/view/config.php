<?php

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

foreach (glob($module_root . 'helpers/*.php') as $filename)
	require_once($filename);

// default timestamp format for views
define('VIEW_TIMESTAMP_FORMAT', 'n/j/Y g:i a');

// default date format for views
define('VIEW_DATE_FORMAT', 'n/j/Y');

define('VIEWS_DIR', $module_root . 'views' . DIRECTORY_SEPARATOR);