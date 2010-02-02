<?php

class Module {
	static $root_modules;
	static $namespaces = array();
	static $classes = array();

	static function autoload ( $class_name ) {
		$ds = DIRECTORY_SEPARATOR;

		foreach ( self::$namespaces as $namespace ) {
			$namespace_a = explode(':', $namespace);
			// get the root module
			$root = array_shift( $namespace_a );

			// turn the namespace into a path
			$ns_path = implode($ds, $namespace_a);
			$class_path = MODULE::$root_modules[$root] . $ds . $ns_path . $ds . $class_name . '.class.php';
			$new_class_path = MODULE::$root_modules[$root] . $ds . $ns_path . $ds . $class_name . '.php';

			// include path
			if ( file_exists ( $class_path )) require_once $class_path;
			if ( file_exists ( $new_class_path )) require_once $new_class_path;
		}
	}

	static function import ( $namespace ) {
		if ( !in_array( $namespace, self::$namespaces ) ) self::$namespaces[] = $namespace;
	}

	static function remove ( $namespace ) {
		foreach (self::$namespaces as $key=>$ns) if ($ns == $namespace) unset(self::$namespaces[$key]);
	}

	static function addRepository ( $name, $module_path ) {
		self::$root_modules[$name] = $module_path;
	}
}

spl_autoload_register(array('Module', 'autoload'));