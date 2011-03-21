<?php

/**
 * Database Management class. Handles connections to multiple databases.
 *
 * {@example libraries/dabl/DBManager_description_1.php}
 *
 * @package dabl
 */
class DBManager {

	private static $connections = array();
	private static $parameters = array();

	private function __construct() {
		
	}

	private function __clone() {
		
	}

	/**
	 * Get the database connections.
	 * All database handles returned will be connected.
	 *
	 * @return DABLPDO[]
	 */
	static function getConnections() {
		foreach (self::$parameters as $conn => $params) {
			self::connect($conn);
		}
		return self::$connections;
	}

	/**
	 * Get the names of all known database connections.
	 *
	 * @return string[]
	 */
	static function getConnectionNames() {
		return array_keys(self::$parameters);
	}

	/**
	 * Get the connection for $db_name. The returned object will be
	 * connected to its database.
	 *
	 * @param String $db_name
	 * @return DABLPDO
	 * @throws PDOException If the connection fails
	 */
	static function getConnection($db_name=null) {
		if (null === $db_name) {
			$db_name = reset(array_keys(self::$parameters));
		}

		if (!@$db_name) {
			return null;
		}

		return self::connect($db_name);
	}

	/**
	 * Add connection information to the manager. This will not
	 * connect the database endpoint until it is requested from the
	 * manager.
	 *
	 * @param string $connection_name Name for the connection
	 * @param string $connection_params Parameters for the connection
	 */
	static function addConnection($connection_name, $connection_params) {
		ClassLoader::import('DATABASE:adapter:' . $connection_params['driver']);
		self::$parameters[$connection_name] = $connection_params;
	}

	/**
	 * Get the specified connection parameter from the given DB
	 * connection.
	 *
	 * @param string $db_name
	 * @param string $key
	 * @return string|null
	 * @throws Exception
	 */
	static function getParameter($db_name, $key) {
		// don't reveal passwords through this interface
		if ('password' === $key) {
			throw new Exception('DB::password is private');
		}

		if (!array_key_exists($db_name, self::$parameters)) {
			throw new Exception("Configuration for database '$db_name' not loaded");
		}

		return @self::$parameters[$db_name][$key];
	}

	/**
	 * (Re-)connect to the database connection named $key.
	 *
	 * @access private
	 * @since 2010-10-29
	 * @param string $key Connection name
	 * @return DABLPDO Database connection
	 * @throws PDOException If the connection fails
	 */
	private static function connect($key) {
		if (array_key_exists($key, self::$connections))
			return self::$connections[$key];

		$conn = DABLPDO::factory(self::$parameters[$key]);
		return (self::$connections[$key] = $conn);
	}

	/**
	 * Disconnect from the database connection named $key.
	 *
	 * @param string $key Connection name
	 * @return void
	 */
	static function disconnect($key) {
		self::$connections[$key] = null;
		unset(self::$connections[$key]);
	}

}
