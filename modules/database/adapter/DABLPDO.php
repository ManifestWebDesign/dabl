<?php

abstract class DABLPDO extends PDO {
	const ID_METHOD_NONE = 0;
	const ID_METHOD_AUTOINCREMENT = 1;
	const ID_METHOD_SEQUENCE = 2;

	protected $queryLog = array();
	protected $logQueries = false;
	protected $dbName = null;

	function setDBName($db_name) {
		$this->dbName = $db_name;
	}

	function getDBName() {
		return $this->dbName;
	}

	function logQuery($query_string, $time) {
		$trace = '';
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		foreach ($backtrace as &$block)
			$trace .= @ $block['file'] . ' (line ' . @$block['line'] . ') ' . @$block['class'] . @$block['type'] . @$block['function'] . '()<br />';
		$this->queryLog[] = array(
			'query' => $query_string,
			'time' => $time,
			'trace' => $trace
		);
	}

	function __destruct() {
		if ($this->logQueries)
			$this->printQueryLog();
	}

	/**
	 * Creates a new instance of the database adapter associated
	 * with the specified Creole driver.
	 *
	 */
	static function factory($connection_params) {
		$class = '';
		$dsn = '';
		$user = null;
		$password = null;
		
		if (@$connection_params['user']) {
			$user = $connection_params['user'];
		}

		if (@$connection_params['password']) {
			$password = $connection_params['password'];
		}
		
		$options = array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);

		if (isset($connection_params['persistant'])) {
			if (
				true === @$connection_params['persistant']
				|| 1 === @$connection_params['persistant']
				|| 'true' === @$connection_params['persistant']
				|| '1' === @$connection_params['persistant']
			) {
				$options[PDO::ATTR_PERSISTENT] = true;
			}
		}

		switch ($connection_params['driver']) {
			case 'sqlite':
				$dsn = 'sqlite:' . $connection_params['dbname'];
				$class = 'DBSQLite';
				break;

			case 'mysql':
				$parts = array();
				if (@$connection_params['host'])
					$parts[] = 'host=' . $connection_params['host'];
				if (@$connection_params['port'])
					$parts[] = 'port=' . $connection_params['port'];
				if (@$connection_params['unix_socket'])
					$parts[] = 'unix_socket=' . $connection_params['unix_socket'];
				if (@$connection_params['dbname'])
					$parts[] = 'dbname=' . $connection_params['dbname'];
				foreach ($parts as &$v) {
					$v = str_replace(';', '\;', $v);
				}
				$dsn = 'mysql:' . implode(';', $parts);
				$class = 'DBMySQL';
				break;

			case 'oracle':
			case 'oci':
				$parts = array();
				if (@$connection_params['dbname'])
					$parts[] = 'dbname=' . $connection_params['dbname'];
				if (@$connection_params['charset'])
					$parts[] = 'charset=' . $connection_params['charset'];
				foreach ($parts as &$v) {
					$v = str_replace(';', '\;', $v);
				}
				$dsn = 'oci:' . implode(';', $parts);
				$class = 'DBOracle';
				break;

			case 'pgsql':
				$parts = array();
				if (@$connection_params['host'])
					$parts[] = 'host=' . $connection_params['host'];
				if (@$connection_params['port'])
					$parts[] = 'port=' . $connection_params['port'];
				if (@$connection_params['dbname'])
					$parts[] = 'dbname=' . $connection_params['dbname'];
				if (@$connection_params['user'])
					$parts[] = 'user=' . $connection_params['user'];
				if (@$connection_params['password'])
					$parts[] = 'password=' . $connection_params['password'];
				foreach ($parts as &$v) {
					$v = str_replace(' ', '\ ', $v);
				}
				$dsn = 'pgsql:' . implode(' ', $parts);
				$user = null;
				$password = null;
				$class = 'DBPostgres';
				break;

			case 'mssql':
			case 'sybase':
			case 'dblib':
				if (@$connection_params['host'])
					$parts[] = 'host=' . $connection_params['host'];
				if (@$connection_params['dbname'])
					$parts[] = 'dbname=' . $connection_params['dbname'];
				if (@$connection_params['charset'])
					$parts[] = 'charset=' . $connection_params['charset'];
				if (@$connection_params['appname'])
					$parts[] = 'appname=' . $connection_params['appname'];

				foreach ($parts as &$v) {
					$v = str_replace(';', '\;', $v);
				}
				$dsn = $connection_params['driver'] . ':' . implode(';', $parts);
				$class = 'DBMSSQL';
				break;

			default:
				throw new Exception("Unsupported database driver: " . $connection_params['driver'] . ": Check your configuration file");
				break;
		}

		try {
			$conn = new $class($dsn, $user, $password, $options);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
		$conn->setDBName(@$connection_params['dbname']);
		return $conn;
	}

	function __construct() {
		$args = func_get_args();
		$result = call_user_func_array(array('parent', '__construct'), $args);
		if ($this->logQueries)
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('LoggedPDOStatement', array($this)));
		return $result;
	}

	/**
	 *
	 * @return PDOStatement
	 */
	function prepare() {
		$args = func_get_args();
		$statement = call_user_func_array(array('parent', 'prepare'), $args);
		return $statement;
	}

	/**
	 * Override of PDO::query() to provide query logging functionality
	 * @return PDOStatement
	 */
	function query() {
		$args = func_get_args();

		if ($this->logQueries) {
			$start = microtime(true);
			$result = call_user_func_array(array('parent', 'query'), $args);
			$time = microtime(true) - $start;
			$this->logQuery((string) $args[0], $time);
			return $result;
		}

		return call_user_func_array(array('parent', 'query'), $args);
	}

	/**
	 * @return PDOStatement
	 */
	function exec() {
		$args = func_get_args();

		if ($this->logQueries) {
			$start = microtime(true);
			$result = call_user_func_array(array('parent', 'exec'), $args);
			$time = microtime(true) - $start;
			$this->logQuery((string) $args[0], $time);
			return $result;
		}

		return call_user_func_array(array('parent', 'exec'), $args);
	}

	function getLoggedQueries() {
		return $this->queryLog;
	}

	function printQueryLog() {
		$queries = $this->getLoggedQueries();
		$total_time = 0.00;
		$string = '<table border="1" style="clear:both;margin:auto;font-size:11px;font-family:monospace" cellpadding="1" cellspacing="0"><tbody>';
		$string .= '<tr><th>#</th><th>Query</th><th>Execution Time (Seconds)</th><th>Trace</th></tr>';
		foreach ($this->queryLog as $num => &$query_array) {
			$string .= '<tr><td>' . ($num + 1) . '</td><td><pre>' . $query_array['query'] . '</pre></td><td>' . round($query_array['time'], 6) . '</td><td><pre>' . $query_array['trace'] . '</pre></td></tr>';
			$total_time += $query_array['time'];
		}
		$string .= '<tr><td></td><td nowrap="nowrap">Total Time: </td><td>' . round($total_time, 6) . '</td><td>&nbsp;</td></tr>';
		$string .= '</tbody></table>';
		echo $string;
		echo '<br />' . 'Max Memory Usage: ' . memory_get_peak_usage() / (1024 * 1024) . ' MB';
	}

	/**
	 * This method is called after a connection was created to run necessary
	 * post-initialization queries or code.
	 *
	 * If a charset was specified, this will be set before any other queries
	 * are executed.
	 *
	 * This base method runs queries specified using the "query" setting.
	 *
	 * @param	  array An array of settings.
	 * @see		setCharset()
	 */
	function initConnection(array $settings) {
		if (isset($settings['charset']['value']))
			$this->setCharset($settings['charset']['value']);
		if (isset($settings['queries']) && is_array($settings['queries'])) {
			foreach ($settings['queries'] as &$queries) {
				foreach ((array) $queries as $query) {
					$this->exec($query);
				}
			}
		}
	}

	/**
	 * Sets the character encoding using SQL standard SET NAMES statement.
	 *
	 * This method is invoked from the default initConnection() method and must
	 * be overridden for an RDMBS which does _not_ support this SQL standard.
	 *
	 * @param	  string The charset encoding.
	 * @see		initConnection()
	 */
	function setCharset($charset) {
		$this->exec("SET NAMES '" . $charset . "'");
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  string The string to transform to upper case.
	 * @return	 string The upper case string.
	 */
	abstract function toUpperCase($in);

	/**
	 * Returns the character used to indicate the beginning and end of
	 * a piece of text used in a SQL statement (generally a single
	 * quote).
	 *
	 * @return	 string The text delimeter.
	 */
	function getStringDelimiter() {
		return '\'';
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function prepareInput($value) {
		if (is_array($value))
			return array_map(array($this, 'prepareInput'), $value);

		if (is_int($value))
			return $value;

		if (is_bool($value))
			return $value ? 1 : 0;

		if ($value === null)
			return 'NULL';

		return $this->quote($value);
	}

	/**
	 * Deprecated method name.  Use prepareInput()
	 * @deprecated
	 * @param mixed $value
	 * @return mixed
	 */
	function checkInput($value) {
		return $this->prepareInput($value);
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  string $in The string whose case to ignore.
	 * @return	 string The string in a case that can be ignored.
	 */
	abstract function ignoreCase($in);

	/**
	 * This method is used to ignore case in an ORDER BY clause.
	 * Usually it is the same as ignoreCase, but some databases
	 * (Interbase for example) does not use the same SQL in ORDER BY
	 * and other clauses.
	 *
	 * @param	  string $in The string whose case to ignore.
	 * @return	 string The string in a case that can be ignored.
	 */
	function ignoreCaseInOrderBy($in) {
		return $this->ignoreCase($in);
	}

	/**
	 * Returns SQL which concatenates the second string to the first.
	 *
	 * @param	  string String to concatenate.
	 * @param	  string String to append.
	 * @return	 string
	 */
	abstract function concatString($s1, $s2);

	/**
	 * Returns SQL which extracts a substring.
	 *
	 * @param	  string String to extract from.
	 * @param	  int Offset to start from.
	 * @param	  int Number of characters to extract.
	 * @return	 string
	 */
	abstract function subString($s, $pos, $len);

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param	  string String to calculate length of.
	 * @return	 string
	 */
	abstract function strLength($s);

	/**
	 * Quotes database object identifiers (table names, col names, sequences, etc.).
	 * @param	  string $text The identifier to quote.
	 * @return	 string The quoted identifier.
	 */
	function quoteIdentifier($text) {
		$quote = '"';
		
		if (is_array($text)) {
			return array_map(array($this, 'quoteIdentifier'), $text);
		}

		if (strpos($text, $quote) !== false || strpos($text, ' ') !== false || strpos($text, '(') !== false || strpos($text, '*') !== false) {
			return $text;
		}
		
		return $quote . implode("$quote.$quote", explode('.', $text)) . $quote;
	}

	/**
	 * Returns the native ID method for this RDBMS.
	 * @return	 int one of DABLPDO:ID_METHOD_SEQUENCE, DABLPDO::ID_METHOD_AUTOINCREMENT.
	 */
	protected function getIdMethod() {
		return DABLPDO::ID_METHOD_AUTOINCREMENT;
	}

	/**
	 * Whether this adapter uses an ID generation system that requires getting ID _before_ performing INSERT.
	 * @return	 boolean
	 */
	function isGetIdBeforeInsert() {
		return ($this->getIdMethod() === DABLPDO::ID_METHOD_SEQUENCE);
	}

	/**
	 * Whether this adapter uses an ID generation system that requires getting ID _before_ performing INSERT.
	 * @return	 boolean
	 */
	function isGetIdAfterInsert() {
		return ($this->getIdMethod() === DABLPDO::ID_METHOD_AUTOINCREMENT);
	}

	/**
	 * Gets the generated ID (either last ID for autoincrement or next sequence ID).
	 * @return	 mixed
	 */
	function getId($name = null) {
		return $this->lastInsertId($name);
	}

	/**
	 * Returns timestamp formatter string for use in date() function.
	 * @return	 string
	 */
	function getTimestampFormatter() {
		return "Y-m-d H:i:s";
	}

	/**
	 * Returns date formatter string for use in date() function.
	 * @return	 string
	 */
	function getDateFormatter() {
		return "Y-m-d";
	}

	/**
	 * Returns time formatter string for use in date() function.
	 * @return	 string
	 */
	function getTimeFormatter() {
		return "H:i:s";
	}

	/**
	 * Should Column-Names get identifiers for inserts or updates.
	 * By default false is returned -> backwards compability.
	 *
	 * it`s a workaround...!!!
	 *
	 * @todo	   should be abstract
	 * @return	 boolean
	 * @deprecated
	 */
	function useQuoteIdentifier() {
		return false;
	}

	/**
	 * Modifies the passed-in SQL to add LIMIT and/or OFFSET.
	 */
	abstract function applyLimit(&$sql, $offset, $limit);

	/**
	 * Gets the SQL string that this adapter uses for getting a random number.
	 *
	 * @param	  mixed $seed (optional) seed value for databases that support this
	 */
	abstract function random($seed = null);

	abstract function getDatabaseSchema();
}

