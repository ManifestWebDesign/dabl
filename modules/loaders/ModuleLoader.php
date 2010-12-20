<?php

class ModuleLoader {

	private static $moduleRoot;
	private static $loaded = array();

	static function setModuleRoot($dir) {
		if (!is_dir($dir))
			throw new Exception($dir . ' is not a directory');

		self::$moduleRoot = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
	}

	static function getModuleRoot() {
		if (null == self::$moduleRoot)
			throw new Exception("Module root has not been set");

		return self::$moduleRoot;
	}

	static function loadAll() {
		foreach (glob(self::getModuleRoot() . '*/init.php') as $filename)
			self::load(basename(dirname($filename)));

		return true;
	}

	static function isLoaded($module_name) {
		return array_key_exists($module_name, self::$loaded);
	}

	static function load($module_name) {
		if (self::isLoaded($module_name))
			return true;

		$filename = self::getModuleRoot() . $module_name . '/init.php';
		self::$loaded[$module_name] = $filename;
		require_once($filename);
		return true;
	}

}