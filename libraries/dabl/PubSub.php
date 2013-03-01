<?php

class PubSub {

	private static $events = array();

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
		foreach (array_keys(self::$events) as $event) {
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
	 */
	public static function subscriptions($event) {
		$events = array();
		if (!isset(self::$events[$event])) {
			return array();
		}

		return array_values(self::$events[$event]);
	}

	/**
	 * Add a new event subscription
	 *
	 * @param string $event
	 * @param callback $callback
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public static function subscribe($event, $callback) {
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Callback "' . print_r($callback, true) . '" is not callable. ');
		}

		if (!isset(self::$events[$event])) {
			self::$events[$event] = array();
		}

		self::$events[$event][self::getCallbackHash($callback)] = $callback;
	}

	/**
	 * Call all subscriptions for the given event
	 *
	 * @param string $event
	 * @param mixed $arg1
	 * @return boolean
	 */
	public static function publish($event, $arg1 = null) {
		$events = self::subscriptions($event);
		$params = func_get_args();
		array_shift($params);

		$response = true;
		foreach ($events as $callback) {
			if (call_user_func_array($callback, $params) === false) {
				$response = false;
			}
		}
		return $response;
	}

	/**
	 * Removes a callback for the given $hook_name
	 * @param string $event
	 * @param callback $callback
	 */
	public static function unsubscribe($event = null, $callback = null) {
		if (null === $event) {
			self::$events = array();
			return;
		}

		if (!isset(self::$events[$event])) {
			return;
		}

		if (null === $callback) {
			unset(self::$events[$event]);
			return;
		}

		$id = self::getCallbackHash($callback);

		// remove all callbacks for specific hook at specific priority level
		unset(self::$events[$event][$id]);
	}

	/**
	 * @param callback $callback
	 * @return string
	 */
	public static function getCallbackHash($callback) {
		if (is_object($callback)) {
			return spl_object_hash($callback);
		}
		return md5(serialize($callback));
	}

}