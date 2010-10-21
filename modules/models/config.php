<?php

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

ClassLoader::addRepository('MODELS', $module_root);
ClassLoader::import('MODELS');
ClassLoader::import('MODELS:models');
ClassLoader::import('MODELS:models:base');