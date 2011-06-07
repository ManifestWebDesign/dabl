<?php

require_once LIBRARIES_DIR . 'dabl/views/load_view.php';

$helpers = glob(LIBRARIES_DIR . 'dabl/format/*.php');
sort($helpers);
foreach ($helpers as $helper) {
	require_once $helper;
}

// default timestamp format for views
define('VIEW_TIMESTAMP_FORMAT', 'n/j/Y g:i a');

// default date format for views
define('VIEW_DATE_FORMAT', 'n/j/Y');

define('VIEWS_DIR', APP_DIR . 'views/');