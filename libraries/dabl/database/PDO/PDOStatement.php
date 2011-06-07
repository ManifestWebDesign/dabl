<?php

abstract class PDOStatement implements Iterator {

	protected $__connection;
	protected $__dbinfo;
	protected $__query = '';
	protected $__position = 0;
	protected $__result = null;
	protected $__boundParams = array();
	protected $__errorCode = '';
	protected $__errorInfo = array('');
	protected $__fetchmode = PDO::FETCH_BOTH;
	protected $__fetchClass;

	/**
	 * @var PDO
	 */
	protected $__pdo;

	/**
	 * Public constructor:
	 * Called from PDO to create a PDOStatement for this database
	 * new PDOStatement_sqlite( &$__query:String, &$__connection:Resource, $__dbinfo:array )
	 * @Param	String		query to prepare
	 * @Param	Resource	database connection
	 * @Param	array		4 elements array to manage connection
	 */
	function __construct(&$__query, &$__connection, &$__dbinfo, &$__pdo_instance) {
		$this->__query = &$__query;
		$this->__connection = &$__connection;
		$this->__dbinfo = &$__dbinfo;
		$this->__pdo = &$__pdo_instance;
		$this->__position = 0;
	}

	abstract function __uquery(&$query);

	abstract function fetch();

	abstract function fetchAll();

	abstract function fetchColumn();

	abstract function columnCount();

	abstract function rowCount();

	function query(){
		if(is_null($this->__result = &$this->__uquery($this->__query)))
			return false;
		else
			return true;
	}

	function rewind() {}

	function next() {
		++$this->__position;
	}

	function current($mode = PDO::FETCH_BOTH) {
		return $this->fetch();
	}

	function key() {
		return $this->__position;
	}

	function valid() {
		if($this->__num_rows===null)
			throw new PDOException("Row count not specified");
		return ($this->__position < $this->__num_rows);
	}

	/**
	 * Public method:
	 * Replace ? or :named values to execute prepared query
	 * this->bindParam( $mixed:Mixed, &$variable:Mixed, $type:Integer, $length:Integer ):Void
	 * @Param	Mixed		Integer or String to replace prepared value
	 * @Param	Mixed		variable to replace
	 * @Param	Integer		this variable is not used but respects PDO original accepted parameters
	 * @Param	Integer		this variable is not used but respects PDO original accepted parameters
	 */
	function bindParam($mixed, &$variable, $type = null, $length = null) {
		if(is_string($mixed))
			$this->__boundParams[$mixed] = $variable;
		else
			array_push($this->__boundParams, $variable);
	}

	/**
	 * Public method:
	 * Replace ? or :named values to execute prepared query
	 * this->bindParam( $mixed:Mixed, $variable:Mixed, $type:Integer, $length:Integer ):Void
	 * @Param	Mixed		Integer or String to replace prepared value
	 * @Param	Mixed		variable to replace
	 * @Param	Integer		this variable is not used but respects PDO original accepted parameters
	 * @Param	Integer		this variable is not used but respects PDO original accepted parameters
	 */
	function bindValue($mixed, $variable, $type = null, $length = null) {
		if(is_string($mixed))
			$this->__boundParams[$mixed] = $variable;
		else
			array_push($this->__boundParams, $variable);
	}

	/**
	 * Not supported
	 */
	function bindColumn($mixewd, &$param, $type = null, $max_length = null, $driver_option = null) {
		return false;
	}

	/**
	 * Public method:
	 * Excecutes a query and returns true on success or false.
	 * this->exec( $array:array ):Boolean
	 * @Param	array		If present, it should contain all replacements for prepared query
	 * @Return	Boolean		true if query has been done without errors, false otherwise
	 */
	function execute($array = array()) {
		if(count($this->__boundParams) > 0)
			$array = &$this->__boundParams;
		$__query = $this->__query;

		if(count($array) > 0) {
			foreach($array as $k => $v) {
				if(!is_int($k) || substr($k, 0, 1) === ':') {
					if(!isset($tempf))
						$tempf = $tempr = array();
					array_push($tempf, $k);
					array_push($tempr, $this->__pdo->quote($v));
				}
				else {
					$params = $this->prepareInput($array);

					//escape % by making it %%
					$__query = str_replace('%', '%%', $__query);

					//replace ? with %s
					$__query = str_replace('?', '%s', $__query);

					//add $query to the beginning of the array
					array_unshift($params, $__query);

					if(!($__query = @call_user_func_array('sprintf', $params)))
						throw new Exception('Could not insert parameters into query string. The number of ?s might not match the number of parameters.');

//					$__query = preg_replace("/(\?)/e", '$array[$k++];', $__query);
					break;
				}
			}
			if(isset($tempf))
				$__query = str_replace($tempf, $tempr, $__query);
		}

		$log = ($this->__pdo->logging);
		if($log){
			$start = microtime(true);
		}

		$this->__result = &$this->__uquery($__query);

		if($log){
			$time = microtime(true) - $start;
			$this->__pdo->logQuery($__query, $time);
		}
		
		if(is_null($this->__result))
			$keyvars = false;
		else
			$keyvars = true;
		$this->__boundParams = array();
		return $keyvars;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function prepareInput($value) {
		if(is_array($value))
			return array_map(array($this, 'prepareInput'), $value);

		if(is_int($value))
			return $value;

		if(is_bool($value))
			return $value ? 1 : 0;

		if($value===null)
			return 'NULL';

		return $this->__pdo->quote($value);
	}

	/**
	 * Public method:
	 * Sets default fetch mode to use with this->fetch() method.
	 * this->setFetchMode( $mode:Integer ):Boolean
	 * @Param	Integer		PDO_FETCH_* constant to use while reading an execute query with fetch() method.
	 * NOTE: PDO_FETCH_LAZY and PDO_FETCH_BOUND are not supported
	 * @Return	Boolean		true on change, false otherwise
	 */
	function setFetchMode($mode, $class=null) {
		$result = false;
		switch($mode) {
			case PDO::FETCH_CLASS:
				$this->__fetchClass = $class;
			case PDO::FETCH_NUM:
			case PDO::FETCH_ASSOC:
			case PDO::FETCH_OBJ:
			case PDO::FETCH_BOTH:
				$result = true;
				$this->__fetchmode = &$mode;
				break;
		}
		return $result;
	}

	/**
	 * @Return	Mixed		correct information or false
	 */
	function getAttribute($attribute) {
		return $this->__pdo->getAttribute($attribute);
	}

	/**
	 * Sets database attributes, in this version only connection mode.
	 * @Return	Boolean		true on change, false otherwise
	 */
	function setAttribute($attribute, $mixed) {
		return $this->__pdo->setAttribute($attribute, $mixed);
	}

	/**
	 * Public method:
	 * Returns a code rappresentation of an error
	 * this->errorCode( void ):String
	 * @Return	String		String rappresentation of the error
	 */
	function errorCode() {
		return $this->__errorCode;
	}

	/**
	 * Public method:
	 * Returns an array with error informations
	 * this->errorInfo( void ):array
	 * @Return	array		array with 3 keys:
	 * 				0 => error code
	 *              1 => error number
	 *              2 => error string
	 */
	function errorInfo() {
		return $this->__errorInfo;
	}

}