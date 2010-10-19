<?php

// check and preserve native PDO driver, for PHP 5.0 users
if(class_exists('PDO'))
	return;
	
/**
 * Class PDO
 * 	PostgreSQL, SQLITE and MYSQL PDO support for PHP 5.0.X users, compatible with PHP 5.1.0 (RC1).
 *
 * DESCRIPTION [directly from http://us2.php.net/manual/en/ref.pdo.php]
 * 	The PHP Data Objects (PDO) extension defines a lightweight, consistent interface for accessing databases in PHP.
 *      Each database driver that implements the PDO interface can expose database-specific features as regular extension functions.
 *      Note that you cannot perform any database functions using the PDO extension by itself;
 *      you must use a database-specific PDO driver to access a database server.
 *
 * HOW TO USE
 * 	To know how to use PDO driver and all its methods visit php.net wonderful documentation.
 *      http://us2.php.net/manual/en/ref.pdo.php
 *      In this class some methods are not available and actually this porting is only for MySQL, SQLITE and PostgreSQL.
 *
 * LIMITS
 * 	For some reasons ( time and php used version with this class ) some PDO methods are not availables and
 *      someother are not totally supported.
 *
 *      PDO :: NOT TOTALLY SUPPORTED METHODS:
 *      	- getAttribute		[ accepts only PDO_ATTR_SERVER_INFO, PDO_ATTR_SERVER_VERSION,
 *              			  PDO_ATTR_CLIENT_VERSION and PDO_ATTR_PERSISTENT attributes ]
 *              - setAttribute		[ supports only PDO_ATTR_PERSISTENT modification ]
 *              - lastInsertId		[ only fo PostgreSQL , returns only pg_last_oid ]
 *
 *      - - - - - - - - - - - - - - - - - - - -
 *
 *      PDOStatement :: UNSUPPORTED METHODS:
 *      	- bindColumn 		[ is not possible to undeclare a variable and using global scope is not
 *              			  really a good idea ]
 *
 *      PDOStatement :: NOT TOTALLY SUPPORTED METHODS:
 *      	- getAttribute		[ accepts only PDO_ATTR_SERVER_INFO, PDO_ATTR_SERVER_VERSION,
 *              			  PDO_ATTR_CLIENT_VERSION and PDO_ATTR_PERSISTENT attributes ]
 *              - setAttribute		[ supports only PDO_ATTR_PERSISTENT modification ]
 *              - setFetchMode		[ supports only PDO_FETCH_NUM, PDO_FETCH_ASSOC, PDO_FETCH_OBJ and
 *              			  PDO_FETCH_BOTH database reading mode ]
 * ---------------------------------------------
 * @Author		Andrea Giammarchi
 * @Site		http://www.devpro.it/
 * @Mail		andrea [ at ] 3site [ dot ] it
 */
class PDO {
	
	const FETCH_ASSOC = 2;
	const FETCH_NUM = 3;
	const FETCH_BOTH = 4;
	const FETCH_OBJ = 5;
	const FETCH_COLUMN = 7;
	const FETCH_LAZY = 1;
	const FETCH_BOUND = 6;
	const FETCH_CLASS = 8;
	
	const ATTR_ERRMODE = 3;
	const ATTR_SERVER_VERSION = 4;
	const ATTR_CLIENT_VERSION = 5;
	const ATTR_SERVER_INFO = 6;
	const ATTR_PERSISTENT = 12;
	const ATTR_STATEMENT_CLASS = 13;
	
	const ERRMODE_EXCEPTION = 2;

	const PARAM_BOOL = 5;
	const PARAM_NULL = 0;
	const PARAM_INT = 1;
	const PARAM_STR = 2;
	const PARAM_LOB = 3;
	
	/**
	 * 'protected' variables:
	 *	__driver:PDO_*		Dedicated PDO database class
	 */
	protected $__driver;
	
	/**
	 * Public constructor
	 *	http://us2.php.net/manual/en/function.pdo-construct.php
	 */
	function __construct($string_dsn, $string_username = '', $string_password = '', $array_driver_options = null) {
		$con = &$this->__getDSN($string_dsn);
		switch($con['dbtype']){
			case 'mysql':
				if(isset($con['port']))
					$con['host'] .= ':'.$con['port'];
				$this->__driver = new PDOMySQL(
					$con['host'],
					$con['dbname'],
					$string_username,
					$string_password
				);
				break;
			case 'sqlite2':
			case 'sqlite':
				$this->__driver = new PDOSQLite($con['dbname']);
				break;
			case 'pgsql':
				$string_dsn = "host={$con['host']} dbname={$con['dbname']} user={$string_username} password={$string_password}";
				if(isset($con['port']))
					$string_dsn .= " port={$con['port']}";
				$this->__driver = new PDOPostgres($string_dsn);
				break;
		}
		$this->__driver->setContainerPDO($this);
	}

	function  __call($name,  $arguments) {
		return call_user_func_array(array($this->__driver, $name), $arguments);
	}

	function  __set($name,  $value) {
		$this->__driver->$name = $value;
	}

	function  __get($name) {
		return $this->__driver->$name;
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-begintransaction.php
	 */
	function beginTransaction() {
		$this->__driver->beginTransaction();
	}

	/**
	 * Calls database_close function.
	 * this->close( Void ):Boolean
	 * @Return	Boolean		True on success, false otherwise
	 */
	function close() {
		return $this->__driver->close();
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-commit.php
	 */
	function commit() {
		$this->__driver->commit();
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-exec.php
	 */
	function exec($query) {
		return $this->__driver->exec($query);
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-errorcode.php
	 */
	function errorCode() {
		return $this->__driver->errorCode();
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-errorinfo.php
	 */
	function errorInfo() {
		return $this->__driver->errorInfo();
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-lastinsertid.php
	 */
	function lastInsertId() {
		return $this->__driver->lastInsertId();
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-prepare.php
	 */
	function prepare($query, $array = Array()) {
		return $this->__driver->prepare($query, $array = Array());
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-query.php
	 */
	function query($query) {
		return $this->__driver->query($query);
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-quote.php
	 */
	function quote($string) {
		return $this->__driver->quote($string);
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-rollback.php
	 */
	function rollBack() {
		$this->__driver->rollBack();
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-setattribute.php
	 */
	function setAttribute($attribute, $mixed) {
		return $this->__driver->setAttribute($attribute, $mixed);
	}

	/**
	 *	http://us2.php.net/manual/en/function.pdo-getattribute.php
	 */
	function getAttribute($attribute) {
		return $this->__driver->getAttribute($attribute);
	}

	private function __getDSN(&$string) {
		$result = array();
		$pos = strpos($string, ':');
		$parameters = explode(';', substr($string, ($pos + 1)));
		$result['dbtype'] = strtolower(substr($string, 0, $pos));
		for($a = 0, $b = count($parameters); $a < $b; $a++) {
			$tmp = explode('=', $parameters[$a]);
			if(count($tmp) == 2)
				$result[$tmp[0]] = $tmp[1];
			else
				$result['dbname'] = $parameters[$a];
		}
		return $result;
	}
}
