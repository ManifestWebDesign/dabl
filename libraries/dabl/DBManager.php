<?php

class DBManager{
	private static $connections = array();

	private function __construct() {
	}

	private function __clone(){
	}

	/**
	 * @return array
	 */
	static function getConnections(){
		return self::$connections;
	}

	/**
	 * @return array
	 */
	static function getConnectionNames(){
		return array_keys(self::$connections);
	}

	/**
	 * @param String $db_name
	 * @return DABLPDO
	 */
	static function getConnection($db_name=null) {
		if($db_name===null)
			return reset(self::$connections);
		return self::$connections[$db_name];
	}

	static function addConnection($connection_name, $connection_params){
		$conn = DABLPDO::factory($connection_params);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		self::$connections[$connection_name] = $conn;
	}

	static function checkInput($value){
		return self::getConnection()->checkInput($value);
	}
}
