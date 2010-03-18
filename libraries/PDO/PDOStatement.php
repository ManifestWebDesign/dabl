<?php

abstract class PDOStatement implements Iterator {
	
	protected $__connection;
	protected $__dbinfo;
	protected $__query = '';
	protected $__position = 0;
	protected $__result = null;
	protected $__boundParams = Array();
	
	/**
	 * Public constructor:
	 * Called from PDO to create a PDOStatement for this database
	 * new PDOStatement_sqlite( &$__query:String, &$__connection:Resource, $__dbinfo:Array )
	 * @Param	String		query to prepare
	 * @Param	Resource	database connection
	 * @Param	Array		4 elements array to manage connection
	 */
	function __construct(&$__query, &$__connection, &$__dbinfo) {
		$this->__query = &$__query;
		$this->__connection = &$__connection;
		$this->__dbinfo = &$__dbinfo;
		$this->__position = 0;
	}

	abstract function __uquery(&$query);
	
	abstract function fetch();

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

}