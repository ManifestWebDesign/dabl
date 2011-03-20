<?php

ModuleLoader::load('strings');
ModuleLoader::load('controllers');
ModuleLoader::load('views');
ModuleLoader::load('models');

ClassLoader::addRepository('GENERATOR', $MODULE_DIR);
ClassLoader::import('GENERATOR');