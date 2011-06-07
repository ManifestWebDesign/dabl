<?php

/**
 * This class manages Hooks.  A Hook is a named callback that is like a JavaScript event.
 */
class Hook {

	private static $hooks = array();

	/**
	 * Registers a callback for the given $hook_name
	 * @param string $hook_name
	 * @param callback $callback
	 * @param int $priority
	 */
	static function add($hook_name, $callback, $priority = 100) {
		if (!isset(self::$hooks[$hook_name]))
			self::$hooks[$hook_name] = array();

		$callback_id = md5(print_r($callback, true));
		self::$hooks[$hook_name][$priority][$callback_id] = $callback;
	}

	/**
	 * Removes a callback for the given $hook_name
	 * @param string $hook_name
	 * @param callback $callback
	 * @param int $priority
	 */
	static function remove($hook_name, $callback, $priority = 100) {
		if (!isset(self::$hooks[$hook_name]) || !isset(self::$hooks[$hook_name][$priority]))
			return;

		$callback_id = md5(print_r($callback, true));
		unset(self::$hooks[$hook_name][$priority][$callback_id]);
	}

	/**
	 * Loops through all registered hook callbacks for the given $hook_name
	 * and calls them using the given $arguments
	 * @param string $hook_name
	 * @param array $arguments Can be an array of arguments, or a single argument
	 */
	static function call($hook_name, $arguments = array()) {
		if (!isset(self::$hooks[$hook_name]))
			return;

		if (!is_array($arguments) && !($arguments instanceof ArrayObject)) {
			$arguments = array($arguments);
		}

		ksort(self::$hooks[$hook_name]);

		foreach (self::$hooks[$hook_name] as $priority => $callback_array) {
			foreach ($callback_array as $callback) {
				call_user_func_array($callback, $arguments);
			}
		}
	}

}