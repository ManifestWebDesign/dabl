<?php

foreach (glob($MODULE_DIR . 'helpers/*.php') as $filename) {
	require_once($filename);
}

require_once $MODULE_DIR . 'StringFormat.php';