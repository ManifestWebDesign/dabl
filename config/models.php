<?php

// timestamp to use for Created and Updated column values
define('CURRENT_TIMESTAMP', time());

define('MODELS_DIR', APP_DIR . 'models/');

define('MODELS_BASE_DIR', MODELS_DIR . 'base/');

ClassLoader::addRepository('MODELS', MODELS_DIR);
ClassLoader::import('MODELS');
ClassLoader::import('MODELS:base');