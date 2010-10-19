<?php

ModuleLoader::load('controller');
ModuleLoader::load('view');
ModuleLoader::load('model');

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;
ClassLoader::addRepository('GENERATOR', $module_root);
ClassLoader::import('GENERATOR');