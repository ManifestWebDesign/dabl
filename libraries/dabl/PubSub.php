<?php

class PubSub {

	private static $callbacks = array();

	/**
	 * Get all events that have been subscribed to
	 *
	 * @param string $event
	 * @return array of callbacks, indexed by event name, in order of execution priority
	 * @author Baylor Rae'
	 */
	public static function events() {
		$events = array();

		// return all hooks for all hook names
		foreach (array_keys(self::$callbacks) as $event) {
			foreach (self::subscriptions($event) as $callback) {
				$events[$event][] = $callback;
			}
		}
		return $events;
	}

	/**
	 * Get all callbacks for a given event, in order of execution priority
	 *
	 * @param string $event
	 * @param int $priority
	 * @return array of callbacks, in order of execution priority
	 * @author Baylor Rae'
	 */
	public static function subscriptions($event, $priority = null) {
		$events = array();
		if (!isset(self::$callbacks[$event])) {
			return array();
		}

		// return all hooks for a given name
		if (null === $priority) {
			ksort(self::$callbacks[$event]);
			foreach (self::$callbacks[$event] as &$callbacks) {
				foreach ($callbacks as $callback) {
					$events[] = $callback;
				}
			}
			return $events;
		}

		// return only hooks for given name and priority
		if (!isset(self::$callbacks[$event][$priority])) {
			return array();
		}
		return array_values(self::$callbacks[$event][$priority]);
	}

	/**
	 * Add a new event subscription
	 *
	 * @param string $event
	 * @param callback $callback
	 * @param int $priority
	 * @throws InvalidArgumentException
	 * @return void
	 * @author Baylor Rae'
	 */
	public static function subscribe($event, $callback, $priority = 100) {
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Callback "' . print_r($callback, true) . '" is not callable. ');
		}

		if (!isset(self::$callbacks[$event])) {
			self::$callbacks[$event] = array();
		}

		self::$callbacks[$event][$priority][self::getCallbackHash($callback)] = $callback;
	}

	/**
	 * Call all subscriptions for the given event
	 *
	 * @param string $event
	 * @param mixed $arg1
	 * @return boolean
	 * @author Baylor Rae'
	 */
	public static function publish($event, $arg1 = null) {
		$events = self::subscriptions($event);
		$params = func_get_args();
		array_shift($params);

		foreach ($events as $callback) {
			if (call_user_func_array($callback, $params) === false) {
				return false;
			}
		}
	}

	/**
	 * Removes a callback for the given $hook_name
	 * @param string $event
	 * @param callback $callback
	 * @param int $priority
	 */
	public static function unsubscribe($event = null, $callback = null, $priority = null) {
		if (null === $event) {
			self::$callbacks = array();
			return;
		}

		if (!isset(self::$callbacks[$event])) {
			return;
		}

		if (null === $callback) {
			// remove all callbacks for this hook name
			if (null === $priority) {
				unset(self::$callbacks[$event]);
				return;
			}
			// remove all callbacks for this hook name for specific priority
			unset(self::$callbacks[$event][$priority]);
			return;
		}

		$id = self::getCallbackHash($callback);

		// remove all callbacks for specific hook at specific priority level
		if (null !== $callback && null !== $priority) {
			unset(self::$callbacks[$event][$priority][$id]);
			return;
		}

		// remove all callbacks for specific hook at all priority levels
		foreach (self::$callbacks[$event] as $priority => &$callbacks) {
			unset($callbacks[$id]);
		}
	}

	/**
	 * @param callback $callback
	 * @return string
	 */
	public static function getCallbackHash($callback) {
		if (is_object($callback)) {
			return spl_object_hash($callback);
		}
		return md5(print_r($callback, true));
	}

}