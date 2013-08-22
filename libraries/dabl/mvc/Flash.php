<?php

/**
 * Description of Flash
 */
class Flash {

	/**
	 * @param string $key
	 * @param mixed $data
	 */
	static function set($key, $data) {
		$_SESSION['__flash'][$key] = $data;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	static function get($key) {
		return isset($_SESSION['__flash'][$key]) ? $_SESSION['__flash'][$key] : null;
	}

	/**
	 * @param mixed $values
	 */
	static function setAll($values) {
		$_SESSION['__flash'] = $values;
	}

	/**
	 *
	 * @return array
	 */
	static function getAll() {
		return isset($_SESSION['__flash']) ? (array) $_SESSION['__flash'] : array();
	}

	/**
	 *
	 * @return array
	 */
	static function getCleanAll() {
		$values = self::getAll();
		self::clean();
		return $values;
	}

	/**
	 *
	 */
	static function clean() {
		if (isset($_SESSION['__flash']))
			unset($_SESSION['__flash']);
	}

}