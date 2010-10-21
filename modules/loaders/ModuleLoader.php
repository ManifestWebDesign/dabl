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
		return self::$moduleRoot;
	}

	static function loadAll() {
		if (null == self::$moduleRoot)
			return false;

		// load modules
		foreach (glob(self::$moduleRoot . '*/config.php') as $filename) {
			$module_name = basename(dirname($filename));

			if (self::isLoaded($module_name))
				continue;

			self::$loaded[$module_name] = $filename;
			require_once($filename);
		}
		return true;
	}

	static function isLoaded($module_name) {
		return array_key_exists($module_name, self::$loaded);
	}

	static function load($module_name) {
		if (null == self::$moduleRoot)
			return false;

		if (self::isLoaded($module_name))
			return true;

		$filename = self::$moduleRoot . $module_name . '/config.php';
		self::$loaded[$module_name] = $filename;
		require_once($filename);
		return true;
	}

}