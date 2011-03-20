<?php

class ModuleLoader {

	private static $moduleRoot;
	private static $loaded = array();

	/**
	 * Specify the directory where modules are located
	 */
	static function setModulesDir($dir) {
		if (!is_dir($dir))
			throw new Exception($dir . ' is not a directory');

		self::$moduleRoot = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
	}

	/**
	 * Returns the directory where modules are located
	 */
	static function getModulesDir() {
		if (null == self::$moduleRoot)
			throw new Exception("Module root has not been set");

		return self::$moduleRoot;
	}

	/**
	 * Includes the init.php script for every module in the modules directory
	 */
	static function loadAll() {
		foreach (glob(self::getModulesDir() . '*/init.php') as $filename)
			self::load(basename(dirname($filename)));

		return true;
	}

	/**
	 * Returns true if the given module has been loaded
	 * @param type $module_name
	 * @return bool 
	 */
	static function isLoaded($module_name) {
		return array_key_exists($module_name, self::$loaded);
	}

	/**
	 * Includes the init.php script for a module in the modules directory
	 * @param type $module_name
	 * @return bool 
	 */
	static function load($module_name) {
		if (self::isLoaded($module_name)) {
			return true;
		}

		$MODULE_INIT_SCRIPT = self::getModulesDir() . $module_name . '/init.php';
		$MODULE_DIR = dirname($MODULE_INIT_SCRIPT).DIRECTORY_SEPARATOR;
		
		self::$loaded[$module_name] = $MODULE_INIT_SCRIPT;
		require_once($MODULE_INIT_SCRIPT);
		return true;
	}

}