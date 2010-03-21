<?php

class QueryStatement {

	private $string = "";
	private $params = array();
	private $connection;

	/**
	 * @param PDO $conn
	 */
	function  __construct(PDO $conn = null) {
		if($conn !==null)
			$this->setConnection($conn);
	}

	/**
	 * Sets the PDO connection to be used for preparing and
	 * executing the query
	 * @param PDO $conn
	 */
	function setConnection(PDO $conn){
		$this->connection = $conn;
	}

	/**
	 * @return PDO
	 */
	function getConnection(){
		return $this->connection;
	}

	/**
	 * Sets the SQL string to be used in a query
	 * @param string $string
	 */
	function setString($string){
		$this->string = $string;
	}

	/**
	 * @return string
	 */
	function getString(){
		return $this->string;
	}

	/**
	 * Merges given array into params
	 * @param array $params
	 */
	function addParams($params){
		$this->params = array_merge($this->params, $params);
	}

	/**
	 * Replaces params with given array
	 * @param array $params
	 */
	function setParams($params){
		$this->params = $params;
	}

	/**
	 * Adds given param to param array
	 * @param mixed $param
	 */
	function addParam($param){
		$this->params[] = $param;
	}

	/**
	 * @return array
	 */
	function getParams(){
		return $this->params;
	}

	/**
	 * @return string
	 */
	function __toString(){
		$string = $this->string;
		$params = $this->params;
		$conn = $this->connection ? $this->connection : DBManager::getConnection();
		foreach($params as $value){
		   $pos = strpos($string, '?');
		   if ($pos === false) break;
		   $string = substr_replace($string, $conn->checkInput($value), $pos, 1);
		}
		return $string;
	}

	/**
	 * Creates a PDOStatment using the string. Loops through param array, and binds each value.
	 * Executes and returns the prepared statement.
	 * @return PDOStatement
	 */
	function bindAndExecute(){
		$conn = $this->getConnection();
		$result = $conn->prepare($this->getString());
		foreach($this->getParams() as $key => $value){
			$pdo_type = PDO::PARAM_STR;
			if(is_int($value))
				$pdo_type = PDO::PARAM_INT;
			elseif(is_null($value))
				$pdo_type = PDO::PARAM_NULL;
			elseif(is_bool($value)){
				$value = $value ? 1 : 0;
				$pdo_type = PDO::PARAM_INT;
			}
			$result->bindValue($key + 1, $value, $pdo_type);
		}
		$result->execute();
		return $result;
	}

}