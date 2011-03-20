<?php

ClassLoader::addRepository('DATABASE', $MODULE_DIR);

ClassLoader::import('DATABASE');
ClassLoader::import('DATABASE:query');
ClassLoader::import('DATABASE:adapter');

if (!class_exists('PDO')) {
	ClassLoader::import('DATABASE:PDO');
}