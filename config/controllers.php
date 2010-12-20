<?php

// default controller, action, and view
define('DEFAULT_CONTROLLER', 'index');

define('CONTROLLERS_DIR', APP_DIR . 'controllers' . DIRECTORY_SEPARATOR);

ClassLoader::addRepository('CONTROLLERS', CONTROLLERS_DIR);
ClassLoader::import('CONTROLLERS');
ClassLoader::import('CONTROLLERS:controllers');