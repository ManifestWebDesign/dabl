<?php

/**
 * Class PDOSQLite
 * 	This class is used from class PDO to manage a SQLITE version 2 database.
 *      Look at PDO.clas.php file comments to know more about SQLITE connection.
 * ---------------------------------------------
 * @Author		Andrea Giammarchi
 * @Site		http://www.devpro.it/
 * @Mail		andrea [ at ] 3site [ dot ] it
 */ 
class PDOSQLite {
	
	/**
	 *	__connection:Resource		Database connection
	 *	__dbinfo:String			Database filename
	 *  __persistent:Boolean		Connection mode, is true on persistent, false on normal (deafult) connection
	 *  __errorCode:String		Last error code
	 *  __errorInfo:array		Detailed errors
	 */
	protected $__connection;
	protected $__dbinfo;
	protected $__persistent = false;
	protected $__errorCode = '';
	protected $__errorInfo = array('');
	protected $__throwExceptions = false;
	protected $__container_pdo;
	public $logging = false;
	
	/**
	 * @Param	String		host with or without port info
	 * @Param	String		database name
	 * @Param	String		database user
	 * @Param	String		database password
	 */
	function __construct(&$string_dsn) {
		if(!@$this->__connection = &sqlite_open($string_dsn))
			$this->__setErrors('DBCON', true);
		else
			$this->__dbinfo = &$string_dsn;
	}

	function setContainerPDO(PDO $pdo){
		$this->__container_pdo = $pdo;
	}

	/**
	 * Calls sqlite_close function.
	 *	this->close( Void ):Boolean
	 * @Return	Boolean		True on success, false otherwise
	 */
	function close() {
		$result = is_resource($this->__connection);
		if($result) {
			sqlite_close($this->__connection);
		}
		return $result;
	}
	
	/**
	 *	Returns a code rappresentation of an error
	 *   this->errorCode( void ):String
	 * @Return	String		String rappresentation of the error
	 */
	function errorCode() {
		return $this->__errorCode;
	}
	
	/**
	 *	Returns an array with error informations
	 *       	this->errorInfo( void ):array
	 * @Return	array		array with 3 keys:
	 * 				0 => error code
	 *                              1 => error number
	 *                              2 => error string
	 */
	function errorInfo() {
		return $this->__errorInfo;
	}
	
	/**
	 *	Excecutes a query and returns affected rows
	 *       	this->exec( $query:String ):Mixed
	 * @Param	String		query to execute
	 * @Return	Mixed		Number of affected rows or false on bad query.
	 */
	function exec($query) {
		$result = 0;
		if(!is_null($this->__uquery($query)))
			$result = sqlite_changes($this->__connection);
		if(is_null($result))
			$result = false;
		return $result;
	}
	
	/**
	 *	Returns last inserted id
	 *       	this->lastInsertId( void ):Number
	 * @Return	Number		Last inserted id
	 */
	function lastInsertId() {
		return sqlite_last_insert_rowid($this->__connection);
	}
	
	/**
	 *	Returns a new PDOStatementSQLite
	 *       	this->prepare( $query:String, $array:array ):PDOStatement
	 * @Param	String		query to prepare
	 * @Param	array		this variable is not used but respects PDO original accepted parameters
	 * @Return	PDOStatementSQLite
	 */
	function prepare($query, $array = array()) {
		return new PDOStatementSQLite($query, $this->__connection, $this->__dbinfo, $this->__container_pdo);
	}
	
	/**
	 *	Executes directly a query and returns an array with result or false on bad query
	 *       	this->query( $query:String ):Mixed
	 * @Param	String		query to execute
	 * @Return	PDOStatementSQLite
	 */
	function query($query) {
    	$statement = new PDOStatementSQLite($query, $this->__connection, $this->__dbinfo, $this->__container_pdo);
		$statement->query();
		return $statement;
	}
	
	/**
	 *	Quotes correctly a string for this database
	 *       	this->quote( $string:String ):String
	 * @Param	String		string to quote
	 * @Return	String		a correctly quoted string
	 */
	function quote($string) {
		return ("'".sqlite_escape_string($string)."'");
	}

	/**
	 *	Quotes correctly a string for this database
	 *       	this->getAttribute( $attribute:Integer ):Mixed
	 * @Param	Integer		a constant [	PDO_ATTR_SERVER_INFO,
	 * 						PDO_ATTR_SERVER_VERSION,
	 *                                              PDO_ATTR_CLIENT_VERSION,
	 *                                              PDO_ATTR_PERSISTENT	]
	 * @Return	Mixed		correct information or null
	 */
	function getAttribute($attribute) {
		$result = null;
		switch($attribute) {
			case PDO::ATTR_SERVER_INFO:
				$result = sqlite_libencoding();
				break;
			case PDO::ATTR_SERVER_VERSION:
			case PDO::ATTR_CLIENT_VERSION:
				$result = sqlite_libversion();
				break;
			case PDO::ATTR_PERSISTENT:
				$result = $this->__persistent;
				break;
		}
		return $result;
	}
	
	/**
	 *	Sets database attributes, in this version only connection mode.
	 *       	this->setAttribute( $attribute:Integer, $mixed:Mixed ):Boolean
	 * @Param	Integer		PDO_* constant, in this case only PDO_ATTR_PERSISTENT
	 * @Param	Mixed		value for PDO_* constant, in this case a Boolean value
	 * 				true for permanent connection, false for default not permament connection
	 * @Return	Boolean		true on change, false otherwise
	 */
	function setAttribute($attribute, $mixed) {
		$result = false;
		if($attribute === PDO::ATTR_ERRMODE && $mixed ===PDO::ERRMODE_EXCEPTION){
			$this->__throwExceptions = true;
		}
		elseif($attribute == PDO::ATTR_STATEMENT_CLASS && @$mixed[0] == 'LoggedPDOStatement'){
			$this->logging = true;
		}
		if($attribute === PDO::ATTR_PERSISTENT && $mixed != $this->__persistent) {
			$result = true;
			$this->__persistent = (boolean) $mixed;
			sqlite_close($this->__connection);
			if($this->__persistent === true)
				$this->__connection = &sqlite_popen($this->__dbinfo);
			else
				$this->__connection = &sqlite_open($this->__dbinfo);
		}
		return $result;
	}
	
	function beginTransaction() {
		return false;
	}
	
	function commit() {
		return false;
	}
	
	function rollBack() {
		return false;
	}
	
	function __setErrors($er, $connection = false) {
		if(!is_resource($this->__connection)) {
			$errno = 1;
			$errst = 'Unable to open or find database.';
		}
		else {
			$errno = sqlite_last_error($this->__connection);
			$errst = sqlite_error_string($errno);
		}
		throw new PDOException("Database error ($errno): $errst");
		$this->__errorCode = &$er;
		$this->__errorInfo = array($this->__errorCode, $errno, $errst);
	}
	
	function __uquery(&$query) {
		if(!@$query = sqlite_query($query, $this->__connection)) {
			$this->__setErrors('SQLER');
			$query = null;
		}
		return $query;
	}
}