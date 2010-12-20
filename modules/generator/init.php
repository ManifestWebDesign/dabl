<?php

ModuleLoader::load('controllers');
ModuleLoader::load('views');
ModuleLoader::load('models');

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;
ClassLoader::addRepository('GENERATOR', $module_root);
ClassLoader::import('GENERATOR');