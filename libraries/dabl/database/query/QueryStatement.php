<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

class QueryStatement {

	/**
	 * character to use as a placeholder for a quoted identifier
	 */
	const IDENTIFIER = '[?]';

	/**
	 * character to use as a placeholder for an escaped parameter
	 */
	const PARAM = '?';

	/**
	 * @var string
	 */
	public $string = '';
	/**
	 * @var array
	 */
	public $params = array();
	/**
	 * @var DABLPDO
	 */
	private $connection;
	/**
	 * @var array
	 */
	public $identifiers = array();

	/**
	 * @param DABLPDO $conn
	 */
	function __construct(DABLPDO $conn = null) {
		if (null !== $conn) {
			$this->setConnection($conn);
		}
	}

	/**
	 * Sets the PDO connection to be used for preparing and
	 * executing the query
	 * @param DABLPDO $conn
	 */
	function setConnection(DABLPDO $conn) {
		$this->connection = $conn;
	}

	/**
	 * @return DABLPDO
	 */
	function getConnection() {
		return $this->connection;
	}

	/**
	 * Sets the SQL string to be used in a query
	 * @param string $string
	 */
	function setString($string) {
		$this->string = $string;
	}

	/**
	 * @return string
	 */
	function getString() {
		return $this->string;
	}

	/**
	 * Merges given array into params
	 * @param array $params
	 */
	function addParams($params) {
		foreach ($params as &$v) {
			$this->params[] = $v;
		}
	}

	/**
	 * Replaces params with given array
	 * @param array $params
	 */
	function setParams($params) {
		$this->params = $params;
	}

	/**
	 * Adds given param to param array
	 * @param mixed $param
	 */
	function addParam($param) {
		$this->params[] = $param;
	}

	/**
	 * @return array
	 */
	function getParams() {
		return $this->params;
	}

	/**
	 * Merges given array into idents
	 * @param array $identifiers
	 */
	function addIdentifiers($identifiers) {
		foreach ($identifiers as &$v) {
			$this->identifiers[] = $v;
		}
	}

	/**
	 * Replaces idents with given array
	 * @param array $identifiers
	 */
	function setIdentifiers($identifiers) {
		$this->identifiers = $identifiers;
	}

	/**
	 * Adds given param to param array
	 * @param mixed $identifier
	 */
	function addIdentifier($identifier) {
		$this->identifiers[] = $identifier;
	}

	/**
	 * @return array
	 */
	function getIdentifiers() {
		return $this->identifiers;
	}

	/**
	 * @return string
	 */
	function __toString() {
		$conn = $this->connection;

		// if a connection is available, use it
		if (null === $conn && class_exists('DBManager')) {
			$conn = DBManager::getConnection();
		}

		$string = self::embedIdentifiers($this->string, array_values($this->identifiers), $conn);
		return self::embedParams($string, array_values($this->params), $conn);
	}

	static function embedIdentifiers($string, $identifiers, DABLPDO $conn = null) {
		if (null != $conn) {
			$identifiers = $conn->quoteIdentifier($identifiers);
		}

		// escape % by making it %%
		$string = str_replace('%', '%%', $string);

		// replace ?ident? with %s
		$string = str_replace(self::IDENTIFIER, '%s', $string);

		//add $query to the beginning of the array
		array_unshift($identifiers, $string);

		if (!($string = @call_user_func_array('sprintf', $identifiers))) {
			throw new Exception('Could not insert identifiers into query string. The number of occurances of ' . self::IDENTIFIER . ' might not match the number of identifiers.');
		}
		return $string;
	}

	/**
	 * Emulates a prepared statement.  Should only be used as a last resort.
	 * @param string $string
	 * @param array $params
	 * @param DABLPDO $conn
	 * @return string
	 */
	static function embedParams($string, $params, DABLPDO $conn = null) {
		if (null != $conn) {
			$params = $conn->prepareInput($params);
		} else {
			foreach($params as &$value) {
				if (is_int($value)) {
					continue;
				} elseif (is_bool($value)) {
					$value = $value ? 1 : 0;
				} elseif (is_null($value)) {
					$value = 'NULL';
				} else {
					$value = "'{$value}'";
				}
			}
		}

		// escape % by making it %%
		$string = str_replace('%', '%%', $string);

		// replace ? with %s
		$string = str_replace(self::PARAM, '%s', $string);

		// add $query to the beginning of the array
		array_unshift($params, $string);

		if (!($string = @call_user_func_array('sprintf', $params))) {
			throw new Exception('Could not insert parameters into query string. The number of ?s might not match the number of parameters.');
		}
		return $string;
	}

	/**
	 * Creates a PDOStatment using the string. Loops through param array, and binds each value.
	 * Executes and returns the prepared statement.
	 * @return PDOStatement
	 */
	function bindAndExecute() {
		$conn = $this->connection;

		if ($conn === null) {
			throw new RuntimeException('bindAndExecute cannot be called on a QueryStatement without a connection.');
		}
		if ($conn instanceof DBMSSQL && $conn->getDriver() === 'dblib' && function_exists('mb_detect_encoding')) {
			return $conn->query($this->__toString());
		}

		$string = self::embedIdentifiers($this->getString(), array_values($this->identifiers), $conn);

		$result = $conn->prepare($string);
		foreach ($this->getParams() as $key => $value) {
			$pdo_type = PDO::PARAM_STR;
			if (is_int($value)) {
				$pdo_type = PDO::PARAM_INT;
			} elseif (is_null($value)) {
				$pdo_type = PDO::PARAM_NULL;
			} elseif (is_bool($value)) {
				$value = $value ? 1 : 0;
				$pdo_type = PDO::PARAM_INT;
			}
			$result->bindValue($key + 1, $value, $pdo_type);
		}
		$result->execute();
		return $result;
	}

}