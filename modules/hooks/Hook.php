<?php

class Hook {

	private static $listeners = array();

	/**
	 * Registers a callback for the given $hook_name
	 * @param string $hook_name
	 * @param callback $callback
	 * @param int $priority
	 */
	static function add($hook_name, $callback, $priority = 100) {
		if (!isset(self::$listeners[$hook_name]))
			self::$listeners[$hook_name] = array();

		$callback_id = md5(print_r($callback, true));
		self::$listeners[$hook_name][$priority][$callback_id] = $callback;
	}

	/**
	 * Removes a callback for the given $hook_name
	 * @param string $hook_name
	 * @param callback $callback
	 * @param int $priority
	 */
	static function remove($hook_name, $callback, $priority = 100) {
		if (!isset(self::$listeners[$hook_name]) || !isset(self::$listeners[$hook_name][$priority]))
			return;

		$callback_id = md5(print_r($callback, true));
		unset(self::$listeners[$hook_name][$priority][$callback_id]);
	}

	/**
	 * Loops through all registered hook callbacks for the given $hook_name
	 * and calls them using the given $arguments
	 * @param string $hook_name
	 * @param array $arguments
	 */
	static function call($hook_name, $arguments = array()) {

		if (!isset(self::$listeners[$hook_name]))
			return;

		ksort(self::$listeners[$hook_name]);

		foreach (self::$listeners[$hook_name] as $priority => $callback_array) {
			foreach ($callback_array as $callback) {
				call_user_func_array($callback, $arguments);
			}
		}
	}

	/**
	 * Just like hook, but treats the array of hook callbacks as filters, where each callback
	 * is expected to return a filtered version of the first argument passed to it
	 * @param string $hook_name
	 * @param array $arguments
	 */
	static function filter($hook_name, $arguments = array()) {
		$result = @$arguments[0];

		if (!isset(self::$listeners[$hook_name]))
			return $result;

		ksort(self::$listeners[$hook_name]);

		foreach (self::$listeners[$hook_name] as $priority => $callback_array) {
			foreach ($callback_array as $callback) {
				$result = $arguments[0] = call_user_func_array($callback, $arguments);
			}
		}
		return $result;
	}

}