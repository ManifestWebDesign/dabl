<?php

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

require_once $module_root.'ClassLoader.php';
require_once $module_root.'ModuleLoader.php';

ModuleLoader::setModuleRoot(MODULES_DIR);