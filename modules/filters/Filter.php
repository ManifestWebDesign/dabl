<?php

/**
 * This class is basically a clone of Hook, but each callback
 * will alter the original value passed to it.  A chained sequence of
 * filter callbacks will pass their results to each other as they run.
 * @see Hook
 */
class Filter {

	private static $filters = array();

	/**
	 * Registers a callback for the given $hook_name
	 * @param string $filter_name
	 * @param callback $callback
	 * @param int $priority
	 */
	static function add($filter_name, $callback, $priority = 100) {
		if (!isset(self::$filters[$filter_name]))
			self::$filters[$filter_name] = array();

		$callback_id = md5(print_r($callback, true));
		self::$filters[$filter_name][$priority][$callback_id] = $callback;
	}

	/**
	 * Removes a callback for the given $hook_name
	 * @param string $filter_name
	 * @param callback $callback
	 * @param int $priority
	 */
	static function remove($filter_name, $callback, $priority = 100) {
		if (!isset(self::$filters[$filter_name]) || !isset(self::$filters[$filter_name][$priority]))
			return;

		$callback_id = md5(print_r($callback, true));
		unset(self::$filters[$filter_name][$priority][$callback_id]);
	}

	/**
	 * Loops through all registered hook callbacks for the given $hook_name.
	 * Each callback is expected to return a filtered version of the first
	 * argument passed to it.  The resulting value is returned.
	 * @param string $filter_name
	 * @param mixed $arguments Can be an array of arguments, or a single argument
	 * @return mixed
	 */
	static function call($filter_name, $arguments = array()) {

		if (!is_array($arguments) && !($arguments instanceof ArrayObject)) {
			$arguments = array($arguments);
		}

		$result = @$arguments[0];

		if (!isset(self::$filters[$filter_name]))
			return $result;

		ksort(self::$filters[$filter_name]);

		foreach (self::$filters[$filter_name] as $priority => $callback_array) {
			foreach ($callback_array as $callback) {
				$result = $arguments[0] = call_user_func_array($callback, $arguments);
			}
		}
		return $result;
	}

}