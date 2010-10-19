<?php

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

ClassLoader::addRepository('MODELS', $module_root);
ClassLoader::import('MODELS');
ClassLoader::import('MODELS:models');
ClassLoader::import('MODELS:models:base');

// timestamp to use for Created and Updated column values
define('CURRENT_TIME', time());

define('MODELS_DIR', $module_root . 'models' . DIRECTORY_SEPARATOR);

define('MODELS_BASE_DIR', $module_root . 'models' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR);