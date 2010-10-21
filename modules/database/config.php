<?php

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;
ClassLoader::addRepository('DATABASE', $module_root);

ClassLoader::import('DATABASE');
ClassLoader::import('DATABASE:query');
ClassLoader::import('DATABASE:adapter');

if (!class_exists('PDO'))
	ClassLoader::import('DATABASE:PDO');