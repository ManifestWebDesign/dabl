<?php

class ClassLoader {

	private static $repoPaths;
	private static $namespaces = array();
	static $delimiter = ':';

	/**
	 * Searches registered class directories for given class and includes it if it is found
	 * @param string $class_name
	 */
	static function autoload($class_name) {

		// check all repository roots first
		foreach (self::$repoPaths as $module_name => &$module_root) {
			$class_path = $module_root . '/' . $class_name . '.php';

			// require file if it exists
			if (is_file($class_path)) {
				require_once $class_path;
				return;
			}
		}

		foreach (self::$namespaces as $namespace => &$namespace_a) {
			$class_path = self::$repoPaths[$namespace_a[0]] . '/' .
					$namespace_a[1] . '/' .
					$class_name . '.php';

			// require file if it exists
			if (is_file($class_path)) {
				require_once $class_path;
				return;
			}
		}
	}

	/**
	 * Registers a class directory
	 * $namespace should be in this form:   REPOSITORY_NAME:PATH:TO:CLASS:DIRECTORY
	 * @param string $namespace
	 */
	static function import($namespace) {
		$namespace_a = explode(self::$delimiter, $namespace);
		$root = array_shift($namespace_a);
		$ns_path = implode('/', $namespace_a);
		self::$namespaces[$namespace] = array($root, $ns_path);
	}

	/**
	 * Removes a class directory
	 * @param string $namespace
	 */
	static function remove($namespace) {
		unset(self::$namespaces[$namespace]);
	}

	/**
	 * Registers a root directory that contains class directories
	 * @param string $name
	 * @param string $module_path
	 */
	static function addRepository($name, $module_path) {
		self::$repoPaths[$name] = rtrim($module_path, '\\/');
	}

}

spl_autoload_register(array('ClassLoader', 'autoload'));