<?php

class Module {
	static $root_modules;
	static $namespaces = array();
	static $classes = array();

	static function autoload($class_name) {
		foreach(self::$namespaces as $namespace => &$namespace_a) {
			$class_path = MODULE::$root_modules[$namespace_a[0]].DIRECTORY_SEPARATOR.
				$namespace_a[1].DIRECTORY_SEPARATOR.
				$class_name.'.php';

			// require file if it exists
			if (is_file($class_path)) {
				require_once $class_path;
				return;
			}
		}
	}

	static function import($namespace) {
		$namespace_a = explode(':', $namespace);
		$root = array_shift($namespace_a);
		$ns_path = implode(DIRECTORY_SEPARATOR, $namespace_a);
		self::$namespaces[$namespace] = array($root, $ns_path);
	}

	static function remove($namespace) {
		unset(self::$namespaces[$namespace]);
	}

	static function addRepository($name, $module_path) {
		self::$root_modules[$name] = rtrim($module_path, '\\/');
	}
}

spl_autoload_register(array('Module', 'autoload'));
