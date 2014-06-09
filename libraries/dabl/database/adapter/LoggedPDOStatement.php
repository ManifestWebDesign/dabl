<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

class LoggedPDOStatement extends PDOStatement{

	/**
	 * @var DABLPDO
	 */
	private $_connection;

	protected function __construct(DABLPDO $conn) {
		$this->setConnection($conn);
    }

  	function setConnection(DABLPDO $conn){
		$this->_connection = $conn;
	}

	/**
	 * @return DABLPDO
	 */
	function getConnection(){
		return $this->_connection;
	}

	function execute($bound_input_params = null) {
		$conn = $this->_connection;

		if ($conn->printQueries) {
			$conn->printQuery($this->queryString);
		}

		$args = func_get_args();

		if ($conn->logQueries) {
			$start = microtime(true);
			$result = call_user_func_array(array('parent', 'execute'), $args);
			$time = microtime(true) - $start;
			$conn->logQuery($this->queryString, $time);
		} else {
			$result = call_user_func_array(array('parent', 'execute'), $args);
		}

		return $result;
	}
}
