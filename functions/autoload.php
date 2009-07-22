<?php
class MODULE {
	public static $debug;
	public static $root_modules;
	public static $namespaces = array();
	public static $ns_path = array();
	public static $classes = array();

	public static function import ( $namespace ) {
		if ( ! in_array( $namespace, self::$namespaces ) ) self::$namespaces[] = $namespace;
	}
	public static function remove ( $namespace ) {
		foreach (self::$namespaces as $key=>$ns) if ($ns == $namespace) unset(self::$namespaces[$key]);
	}
	public static function addRepository ( $name, $module_path ) {
		self::$root_modules[$name] = $module_path;
	}
}

function __autoload ( $class_name ) {
	if (strpos($class_name, 'Exception') !== false) $class_name = 'Exceptions';

	foreach ( MODULE::$namespaces as $namespace ) {
		$namespace_a = explode(':', $namespace);
		// get the root module
		$root = array_shift( $namespace_a );
		// turn the namespace into an path
		$ns_path = implode('/', $namespace_a);
		$class_path = MODULE::$root_modules[$root] . '/' . $ns_path . '/' . $class_name . '.class.php';
		$new_class_path = MODULE::$root_modules[$root] . '/' . $ns_path . '/' . $class_name . '.php';

		// include path
		if ( file_exists ( $class_path )) require_once $class_path;
		if ( file_exists ( $new_class_path )) require_once $new_class_path;
	}
}
?>