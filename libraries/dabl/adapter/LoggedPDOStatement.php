<?php

class LoggedPDOStatement extends PDOStatement{

	private $_connection;

	function setConnection(PDO $conn){
		$this->_connection = $conn;
	}

	/**
	 * @return PDO
	 */
	function getConnection(){
		return $this->_connection;
	}

	function execute() {
		$args = func_get_args();

		$conn = $this->getConnection();
		$start = microtime(true);
		$result = call_user_func_array(array('parent', 'execute'), $args);
		$time = microtime(true) - $start;
		$conn->logQuery($this->queryString, $time);
		return $result;
	}
}
