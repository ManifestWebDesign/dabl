<?php

foreach (glob($MODULE_DIR . 'helpers/*.php') as $filename) {
	require_once($filename);
}