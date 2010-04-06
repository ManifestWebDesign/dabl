<?php

/**
* Class PDOMySQL
*     This class is used from class PDO to manage a MySQL database.
*      Look at PDO.clas.php file comments to know more about MySQL connection.
* ---------------------------------------------
* @Author        Andrea Giammarchi
* @Site        http://www.devpro.it/
* @Mail        andrea [ at ] 3site [ dot ] it
*/
class PDOMySQL {

    /**
     *    __connection:Resource        Database connection
	 *    __dbinfo:array            array with 4 elements used to manage connection
	 *    __persistent:Boolean        Connection mode, is true on persistent, false on normal (deafult) connection
	 *    __errorCode:String        Last error code
	 *    __errorInfo:array        Detailed errors
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
	 *    Checks connection and database selection
	 *           new PDO_mysql( &$host:String, &$db:String, &$user:String, &$pass:String )
	 * @Param    String        host with or without port info
	 * @Param    String        database name
	 * @Param    String        database user
	 * @Param    String        database password
	 */
    function __construct(&$host, &$db, &$user, &$pass) {
        if(!@$this->__connection = &mysql_connect($host, $user, $pass))
            $this->__setErrors('DBCON');
        else {
            if(!@mysql_select_db($db, $this->__connection))
                $this->__setErrors('DBER');
            else
                $this->__dbinfo = array($host, $user, $pass, $db);
        }
    }

	function setContainerPDO(PDO $pdo){
		$this->__container_pdo = $pdo;
	}

	/** NOT NATIVE BUT MAYBE USEFULL FOR PHP < 5.1 PDO DRIVER
	 * Calls mysql_close function.
	 *    this->close( Void ):Boolean
	 * @Return    Boolean        True on success, false otherwise
	 */
    function close() {
        $result = is_resource($this->__connection);
        if($result) {
            mysql_close($this->__connection);
        }
        return $result;
    }

	/**
	 *    Returns a code rappresentation of an error
	 *           this->errorCode( void ):String
	 * @Return    String        String rappresentation of the error
	 */
    function errorCode() {
        return $this->__errorCode;
    }

	/**
	 *    Returns an array with error informations
	 *           this->errorInfo( void ):array
	 * @Return    array        array with 3 keys:
	 *                 0 => error code
	 *                              1 => error number
	 *                              2 => error string
	 */
    function errorInfo() {
        return $this->__errorInfo;
    }

	/**
	 *    Excecutes a query and returns affected rows
	 *           this->exec( $query:String ):Mixed
	 * @Param    String        query to execute
	 * @Return    Mixed        Number of affected rows or false on bad query.
	 */
    function exec($query) {
        $result = 0;
        if(!is_null($this->__uquery($query)))
            $result = mysql_affected_rows($this->__connection);
        if(is_null($result))
            $result = false;
        return $result;
    }

	/**
	 *    Returns last inserted id
	 *           this->lastInsertId( void ):Number
	 * @Return    Number        Last inserted id
	 */
    function lastInsertId() {
        return mysql_insert_id($this->__connection);
    }

	/**
	 *    Returns a new PDOStatementMySQL
	 *           this->prepare( $query:String, $array:array ):PDOStatement
	 * @Param    String        query to prepare
	 * @Param    array        this variable is not used but respects PDO original accepted parameters
	 * @Return    PDOStatementMySQL
	 */
    function prepare($query, $array = array()) {
        return new PDOStatementMySQL($query, $this->__connection, $this->__dbinfo, $this->__container_pdo);
    }

	/**
	 *    Executes directly a query and returns an array with result or false on bad query
	 *           this->query( $query:String ):Mixed
	 * @Param    String        query to execute
	 * @Return    PDOStatementMySQL
	 */
    function query($query) {
    	$statement = new PDOStatementMySQL($query, $this->__connection, $this->__dbinfo, $this->__container_pdo);
		$statement->query();
		return $statement;
    }

	/**
	 *    Quotes correctly a string for this database
	 *           this->quote( $string:String ):String
	 * @Param    String        string to quote
	 * @Return    String        a correctly quoted string
	 */
    function quote($string) {
        return ("'".mysql_real_escape_string($string, $this->__connection)."'");
    }

	/**
	 *    Quotes correctly a string for this database
	 *           this->getAttribute( $attribute:Integer ):Mixed
	 * @Param    Integer        a constant [    PDO_ATTR_SERVER_INFO,
	 *                         PDO_ATTR_SERVER_VERSION,
	 *                                              PDO_ATTR_CLIENT_VERSION,
	 *                                              PDO_ATTR_PERSISTENT    ]
	 * @Return    Mixed        correct information or false
	 */
    function getAttribute($attribute) {
        $result = false;
        switch($attribute) {
            case PDO::ATTR_SERVER_INFO:
                $result = mysql_get_host_info($this->__connection);
                break;
            case PDO::ATTR_SERVER_VERSION:
                $result = mysql_get_server_info($this->__connection);
                break;
            case PDO::ATTR_CLIENT_VERSION:
                $result = mysql_get_client_info();
                break;
            case PDO::ATTR_PERSISTENT:
                $result = $this->__persistent;
                break;
        }
        return $result;
    }

	/**
	 *    Sets database attributes, in this version only connection mode.
	 *           this->setAttribute( $attribute:Integer, $mixed:Mixed ):Boolean
	 * @Param    Integer        PDO_* constant, in this case only PDO_ATTR_PERSISTENT
	 * @Param    Mixed        value for PDO_* constant, in this case a Boolean value
	 *                 true for permanent connection, false for default not permament connection
	 * @Return    Boolean        true on change, false otherwise
	 */
    function setAttribute($attribute, $mixed) {
        $result = false;
		if($attribute == PDO::ATTR_ERRMODE && $mixed ==PDO::ERRMODE_EXCEPTION){
			$this->__throwExceptions = true;
		}
		elseif($attribute == PDO::ATTR_STATEMENT_CLASS && $mixed == 'LoggedPDOStatement'){
			$this->logging = true;
		}
        elseif($attribute === PDO::ATTR_PERSISTENT && $mixed != $this->__persistent) {
            $result = true;
            $this->__persistent = (boolean) $mixed;
            mysql_close($this->__connection);
            if($this->__persistent === true)
                $this->__connection = &mysql_pconnect($this->__dbinfo[0], $this->__dbinfo[1], $this->__dbinfo[2]);
            else
                $this->__connection = &mysql_connect($this->__dbinfo[0], $this->__dbinfo[1], $this->__dbinfo[2]);
            mysql_select_db($this->__dbinfo[3], $this->__connection);
        }
        return $result;
    }

    function beginTransaction() {
        return $this->exec("BEGIN");
    }

    function commit() {
        return $this->exec("COMMIT");
    }

    function rollBack() {
        return $this->exec("ROLLBACK");
    }

    function __setErrors($er) {
        if(!is_resource($this->__connection)) {
            $errno = mysql_errno();
            $errst = mysql_error();
        }
        else {
            $errno = mysql_errno($this->__connection);
            $errst = mysql_error($this->__connection);
        }
		throw new PDOException("Database error ($errno): $errst");
        $this->__errorCode = &$er;
        $this->__errorInfo = array($this->__errorCode, $errno, $errst);
    }

    function __uquery(&$query) {
        if(!@$query = mysql_query($query, $this->__connection)) {
            $this->__setErrors('SQLER');
            $query = null;
        }
        return $query;
    }
}