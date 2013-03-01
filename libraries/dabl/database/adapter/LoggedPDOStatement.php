<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

class LoggedPDOStatement extends PDOStatement{

	private $_connection;

	protected function __construct(PDO $conn) {
		$this->setConnection($conn);
    }


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
