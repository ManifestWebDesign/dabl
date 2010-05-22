<?php

/**
 * Class PDOStatementMySQL
 * 	This class is used from class PDO_mysql to manage a MySQL database.
 *      Look at PDO.clas.php file comments to know more about MySQL connection.
 * ---------------------------------------------
 * @Author		Andrea Giammarchi
 * @Site		http://www.devpro.it/
 * @Mail		andrea [ at ] 3site [ dot ] it
 */

class PDOStatementMySQL extends PDOStatement{

	/**
	 * Connection mode, is true on persistent, false on normal (deafult) connection
	 * @var Boolean
	 */
	protected $__persistent = false;

	/**
	 * Public method:
	 * Returns, if present, next row of executed query or false.
	 * this->fetch( $mode:Integer, $cursor:Integer, $offset:Integer ):Mixed
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
					$result = mysql_fetch_row($this->__result);
					break;
				case PDO::FETCH_ASSOC:
					$result = mysql_fetch_assoc($this->__result);
					break;
				case PDO::FETCH_OBJ:
					$result = mysql_fetch_object($this->__result);
					break;
				case PDO::FETCH_CLASS:
					$result = mysql_fetch_object($this->__result, $this->__fetchClass);
					break;
				case PDO::FETCH_BOTH:
				default:
					$result = mysql_fetch_array($this->__result);
					break;
			}
		}
		if(!$result)
			$this->__result = null;
		return $result;
	}

	/**
	 * Public method:
	 *	Returns an array with all rows of executed query.
	 * this->fetchAll( $mode:Integer ):array
	 * @Param	Integer		PDO_FETCH_* constant to know how to read all rows, default PDO_FETCH_BOTH
	 * 				NOTE: this doesn't work as fetch method, then it will use always PDO_FETCH_BOTH
	 *                                    if this param is omitted
	 * @Return	array		An array with all fetched rows
	 */
	function fetchAll($mode = PDO::FETCH_BOTH, $column_index = 0) {
		$result = array();
		if(!is_null($this->__result)) {
			switch($mode) {
				case PDO::FETCH_NUM:
					while($r = mysql_fetch_row($this->__result))
						array_push($result, $r);
					break;
				case PDO::FETCH_ASSOC:
					while($r = mysql_fetch_assoc($this->__result))
						array_push($result, $r);
					break;
				case PDO::FETCH_COLUMN:
					for($x = 0; $x < mysql_num_rows($this->__result); $x++)
						array_push($result, mysql_result($this->__result, $x, $column_index));
					break;
				case PDO::FETCH_OBJ:
					while($r = mysql_fetch_object($this->__result))
						array_push($result, $r);
					break;
				case PDO::FETCH_CLASS:
					while($r = mysql_fetch_object($this->__result, $this->__fetchClass))
						array_push($result, $r);
					break;
				case PDO::FETCH_BOTH:
				default:
					while($r = mysql_fetch_array($this->__result))
						array_push($result, $r);
					break;
			}
		}
		$this->__result = null;
		return $result;
	}

	function fetchObject($class_name){
		return  mysql_fetch_object($this->__result, $class_name);
	}

	/**
	 * @Return	Mixed
	 */
	function fetchColumn($column_number = 0) {
		$result = null;
		if(!is_null($this->__result)) {
			$result = @mysql_fetch_row($this->__result);
			if($result)
				$result = $result[$column_number];
			else
				$this->__result = null;
		}
		return $result;
	}

	/**
	 * Public method:
	 * Checks if query was valid and returns how may fields returns
	 * this->columnCount( void ):Void
	 */
	function columnCount() {
		$result = 0;
		if(!is_null($this->__result))
			$result = mysql_num_fields($this->__result);
		return $result;
	}

	/**
	 * Public method:
	 * Returns number of last affected database rows
	 * this->rowCount( void ):Integer
	 * number of last affected rows
	 * NOTE: works with INSERT, UPDATE and DELETE query type
	 */
	function rowCount() {
		return mysql_affected_rows($this->__connection);
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
		$this->__result = null;
	}

	function __uquery(&$query) {
		if(!$result = mysql_query($query, $this->__connection)) {
			$this->__setErrors('SQLER');
			$result = null;
		}
		$this->__position = 0;
		$this->__num_rows = (int)@mysql_num_rows($result);
		return $result;
	}

}