<?php
/**
 * Last Modified July 14th 2009
 */

class DBManager{
	private static $connections = null;

	private function __construct() {
	}

	private function __clone(){
	}

	/**
	 * @param String $name
	 * @return DBAdapter
	 */
	public static function getConnection($name=null) {
		if($name===null){
			foreach(self::$connections as $conn)
				return $conn;
		}
		return self::$connections[$name];
	}

	public static function addConnection($name, DBAdapter $conn){
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		self::$connections[$name] = $conn;
	}

	public static function checkInput($value){
		return self::getConnection()->checkInput($value);
	}
}
