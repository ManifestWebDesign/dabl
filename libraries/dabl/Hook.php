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
		if (!isset(self::$hooks[$hook_name])) {
			self::$hooks[$hook_name] = array();
		}

		self::$hooks[$hook_name][$priority][self::getCallbackHash($callback)] = $callback;
	}

	/**
	 * @param string $hook_name
	 * @return array of callbacks, in order of execution priority
	 */
	static function get($hook_name = null, $priority = null) {
		$hooks = array();

		// return all hooks for all hook names
		if (null === $hook_name) {
			foreach (array_keys(self::$hooks) as $hook_name) {
				foreach (self::get($hook_name) as $callback) {
					$hooks[] = $callback;
				}
			}
			return $hooks;
		}

		if (!isset(self::$hooks[$hook_name])) {
			return array();
		}

		// return all hooks for a given name
		if (null === $priority) {
			ksort(self::$hooks[$hook_name]);
			foreach (self::$hooks[$hook_name] as &$callbacks) {
				foreach ($callbacks as $callback) {
					$hooks[] = $callback;
				}
			}
			return $hooks;
		}

		// return only hooks for given name and priority
		if (!isset(self::$hooks[$hook_name][$priority])) {
			return array();
		}
		return array_values(self::$hooks[$hook_name][$priority]);
	}

	/**
	 * @param callback $callback
	 * @return string
	 */
	static function getCallbackHash($callback) {
		if (is_object($callback)) {
			return spl_object_hash($callback);
		}
		return md5(print_r($callback, true));
	}

	/**
	 * Removes a callback for the given $hook_name
	 * @param string $hook_name
	 * @param callback $callback
	 * @param int $priority
	 */
	static function remove($hook_name = null, $callback = null, $priority = null) {
		if (null === $hook_name) {
			self::$hooks = array();
			return;
		}

		if (!isset(self::$hooks[$hook_name])) {
			return;
		}

		if (null === $callback) {
			// remove all callbacks for this hook name
			if (null === $priority) {
				unset(self::$hooks[$hook_name]);
				return;
			}
			// remove all callbacks for this hook name for specific priority
			unset(self::$hooks[$hook_name][$priority]);
			return;
		}

		$id = self::getCallbackHash($callback);

		// remove all callbacks for specific hook at specific priority level
		if (null !== $callback && null !== $priority) {
			unset(self::$hooks[$hook_name][$priority][$id]);
			return;
		}

		// remove all callbacks for specific hook at all priority levels
		foreach (self::$hooks[$hook_name] as $priority => &$callbacks) {
			unset($callbacks[$id]);
		}
	}

	/**
	 * Loops through all registered hook callbacks for the given $hook_name
	 * and calls them using the given $arguments
	 * @param string $hook_name
	 * @param array $arguments Can be an array of arguments, or a single argument
	 */
	static function call($hook_name, $arguments = array()) {
		if (!isset(self::$hooks[$hook_name])) {
			return;
		}

		if (!is_array($arguments) && !($arguments instanceof ArrayObject)) {
			$arguments = array($arguments);
		}

		foreach (self::get($hook_name) as $callback) {
			if (call_user_func_array($callback, $arguments) === false) {
				return false;
			}
		}
	}

}