<?php

/**
 * Class PDOStatementPostgres
 * 	This class is used from class PDO_pgsql to manage a PostgreSQL database.
 *	  Look at PDO.clas.php file comments to know more about PostgreSQL connection.
 * ---------------------------------------------
 * @Author		Andrea Giammarchi
 * @Site		http://www.devpro.it/
 * @Mail		andrea [ at ] 3site [ dot ] it
 */ 
class PDOStatementPostgres extends PDOStatement{
	
	/**
	 *	  __persistent:Boolean		Connection mode, is true on persistent, false on normal (deafult) connection
	 */
	protected $__persistent = false;
	
	/**
	 *	Returns, if present, next row of executed query or false.
	 *	   	this->fetch( $mode:Integer, $cursor:Integer, $offset:Integer ):Mixed
	 * @Param	Integer		PDO_FETCH_* constant to know how to read next row, default PDO_FETCH_BOTH
	 * 				NOTE: if $mode is omitted is used default setted mode, PDO_FETCH_BOTH
	 * @Param	Integer		this variable is not used but respects PDO original accepted parameters
	 * @Param	Integer		this variable is not used but respects PDO original accepted parameters
	 * @Return	Mixed		Next row of executed query or false if there is nomore.
	 */
	function fetch($mode = PDO::FETCH_BOTH, $cursor = null, $offset = null) {
		if(func_num_args() == 0)
			$mode = &$this->__fetchmode;
		$result = false;
		if(!is_null($this->__result)) {
			switch($mode) {
				case PDO::FETCH_NUM:
					$result = pg_fetch_row($this->__result);
					break;
				case PDO::FETCH_ASSOC:
					$result = pg_fetch_assoc($this->__result);
					break;
				case PDO::FETCH_OBJ:
					$result = pg_fetch_object($this->__result);
					break;
				case PDO::FETCH_BOTH:
				default:
					$result = pg_fetch_array($this->__result);
					break;
			}
		}
		if(!$result)
			$this->__result = null;
		return $result;
	}
	
	/**
	 *	Returns an array with all rows of executed query.
	 *	   	this->fetchAll( $mode:Integer ):array
	 * @Param	Integer		PDO_FETCH_* constant to know how to read all rows, default PDO_FETCH_BOTH
	 * 				NOTE: this doesn't work as fetch method, then it will use always PDO_FETCH_BOTH
	 *									if this param is omitted
	 * @Return	array		An array with all fetched rows
	 */
	function fetchAll($mode = PDO_FETCH_BOTH, $column_index = 0) {
		$result = array();
		if(!is_null($this->__result)) {
			switch($mode) {
				case PDO::FETCH_NUM:
					while($r = pg_fetch_row($this->__result))
						array_push($result, $r);
					break;
				case PDO::FETCH_ASSOC:
					while($r = pg_fetch_assoc($this->__result))
						array_push($result, $r);
					break;
				case PDO::FETCH_COLUMN:
					while ($r = pg_fetch_row($result))
						array_push($result, $r[$column_index]);
					break;
				case PDO::FETCH_OBJ:
					while($r = pg_fetch_object($this->__result))
						array_push($result, $r);
					break;
				case PDO::FETCH_BOTH:
				default:
					while($r = pg_fetch_array($this->__result))
						array_push($result, $r);
					break;
			}
		}
		$this->__result = null;
		return $result;
	}
	
	/**
	 * @Return	Mixed
	 */
	function fetchColumn($column_number = 0) {
		$result = null;
		if(!is_null($this->__result)) {
			$result = @pg_fetch_row($this->__result);
			if($result)
				$result = $result[$column_number];
			else
				$this->__result = null;
		}
		return $result;
	}

	/**
	 *	Checks if query was valid and returns how may fields returns
	 *	   	this->columnCount( void ):Void
	 */
	function columnCount() {
		$result = 0;
		if(!is_null($this->__result))
			$result = pg_num_fields($this->__result);
		return $result;
	}

	/**
	 *	Returns number of last affected database rows
	 *	   	this->rowCount( void ):Integer
	 * @Return	Integer		number of last affected rows
	 * 				NOTE: works with INSERT, UPDATE and DELETE query type
	 */
	function rowCount() {
		$result = 0;
		if(!is_null($this->__result))
			$result = pg_affected_rows($this->__result);
		return $result;
	}
	
	function __setErrors($er) {
		if(!is_string($this->__errorCode))
			$errno = $this->__errorCode;
		if(!is_resource($this->__connection)) {
			$errno = 1;
			$errst = pg_last_error();
		}
		else {
			$errno = 1;
			$errst = pg_last_error($this->__connection);
		}
		throw new PDOException("Database error ($errno): $errst");
		$this->__errorCode = &$er;
		$this->__errorInfo = array($this->__errorCode, $errno, $errst);
	}
	
	function __uquery(&$query) {
		if(!@$query = pg_query($this->__connection, $query)) {
			$this->__setErrors('SQLER');
			$query = null;
		}
		$this->__position = 0;
		$this->__num_rows = (int)@pg_num_rows($result);
		return $result;
	}
	
}