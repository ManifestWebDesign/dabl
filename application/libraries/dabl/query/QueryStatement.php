<?php

class QueryStatement {

	private $string = "";
	private $params = array();
	private $connection;

	function setConnection($conn){
		$this->connection = $conn;
	}

	function getConnection(){
		return $this->connection;
	}

	function setString($string){
		$this->string = $string;
	}

	function getString(){
		return $this->string;
	}

	function addParams($params){
		$this->params = array_merge($this->params, $params);
	}

	function setParams($params){
		$this->params = $params;
	}

	function addParam($param){
		$this->params[] = $param;
	}

	function getParams(){
		return $this->params;
	}

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