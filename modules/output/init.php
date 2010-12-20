<?php

$module_root = dirname(__FILE__).DIRECTORY_SEPARATOR;

foreach (glob($module_root . 'helpers/*.php') as $filename)
	require_once($filename);