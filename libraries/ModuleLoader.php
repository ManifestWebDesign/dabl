<?php

class ModuleLoader {

	private static $moduleRoot;
	private static $loaded = array();

	static function setModuleRoot($dir){

		if(!is_dir($dir))
			throw new Exception($dir . ' is not a directory');


		self::$moduleRoot = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
	}

	static function loadAll() {
		if(null == self::$moduleRoot)
			return false;

		// load modules
		foreach (glob(self::$moduleRoot . '*/config.php') as $filename) {
			$module_name = basename(dirname($filename));
			if (!in_array($module_name, self::$loaded)) {
				self::$loaded[] = $module_name;
				require_once($filename);
			}
		}
	}

	static function load($module_name) {
		if(null == self::$moduleRoot)
			return false;

		$filename = self::$moduleRoot . $module_name . '/config.php';
		if (!in_array($module_name, self::$loaded)) {
			self::$loaded[] = $module_name;
			require_once($filename);
		}
	}

}