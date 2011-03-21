<?php

ModuleLoader::load('format');
ModuleLoader::load('models');

ClassLoader::addRepository('GENERATOR', $MODULE_DIR);
ClassLoader::import('GENERATOR');