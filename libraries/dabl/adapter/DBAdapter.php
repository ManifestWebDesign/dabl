<?php

/**
 * Modified version of DBAdapter from Propel Runtime
 * Last Modified February 8th 2010 by Dan Blaisdell
 */
abstract class DBAdapter extends PDO {

	const ID_METHOD_NONE = 0;
	const ID_METHOD_AUTOINCREMENT = 1;
	const ID_METHOD_SEQUENCE = 2;

	protected $_logged_queries = array();
	protected $_log_queries = false;
	protected $_db_name = null;

	function setDBName($db_name){
		$this->_db_name = $db_name;
	}

	function getDBName(){
		return $this->_db_name;
	}

	/**
	 * Creates a new instance of the database adapter associated
	 * with the specified Creole driver.
	 *
	 */
	static function factory($connection_params) {
		try{
			switch($connection_params['driver']){
				case 'sqlite':
					$dsn = 'sqlite:'.$connection_params['dbname'];
					$conn = new DBSQLite($dsn);
					break;

				case 'mysql':
					$parts = array();
					if(@$connection_params['host']) $parts[] = 'host='.$connection_params['host'];
					if(@$connection_params['port']) $parts[] = 'port='.$connection_params['port'];
					if(@$connection_params['unix_socket']) $parts[] = 'unix_socket='.$connection_params['unix_socket'];
					if(@$connection_params['dbname']) $parts[] = 'dbname='.$connection_params['dbname'];
					foreach($parts as &$v) {
						$v = str_replace(';', '\;', $v);
					}
					$dsn = 'mysql:'.implode(';', $parts);
					$conn = new DBMySQL($dsn, @$connection_params['user'], @$connection_params['password']);
					break;

				case 'oracle':
				case 'oci':
					$parts = array();
					if(@$connection_params['dbname']) $parts[] = 'dbname='.$connection_params['dbname'];
					if(@$connection_params['charset']) $parts[] = 'charset='.$connection_params['charset'];
					foreach($parts as &$v) {
						$v = str_replace(';', '\;', $v);
					}
					$dsn = 'oci:'.implode(';', $parts);
					$conn = new DBOracle($dsn, @$connection_params['user'], @$connection_params['password']);
					break;

				case 'pgsql':
					$parts = array();
					if(@$connection_params['host']) $parts[] = 'host='.$connection_params['host'];
					if(@$connection_params['port']) $parts[] = 'port='.$connection_params['port'];
					if(@$connection_params['dbname']) $parts[] = 'dbname='.$connection_params['dbname'];
					if(@$connection_params['user']) $parts[] = 'user='.$connection_params['user'];
					if(@$connection_params['password']) $parts[] = 'password='.$connection_params['password'];
					foreach($parts as &$v) {
						$v = str_replace(' ', '\ ', $v);
					}
					$dsn = 'pgsql:'.implode(' ', $parts);
					$conn = new DBPostgres($dsn);
					break;

				case 'mssql':
				case 'sybase':
				case 'dblib':
					if(@$connection_params['host']) $parts[] = 'host='.$connection_params['host'];
					if(@$connection_params['dbname']) $parts[] = 'dbname='.$connection_params['dbname'];
					if(@$connection_params['charset']) $parts[] = 'charset='.$connection_params['charset'];
					if(@$connection_params['appname']) $parts[] = 'appname='.$connection_params['appname'];
					foreach($parts as &$v) {
						$v = str_replace(';', '\;', $v);
					}
					$dsn = $connection_params['driver'].':'.implode(';', $parts);
					$conn = new DBMSSQL($dsn, @$connection_params['user'], @$connection_params['password']);
					break;
				
				default:
					throw new Exception("Unsupported database driver: " . $connection_params['driver'] . ": Check your configuration file");
					break;
			}
		}
		catch(Exception $e){
			throw new Exception($e->getMessage());
		}
		$conn->setDBName(@$connection_params['dbname']);
		return $conn;
	}

	function getLoggedQueries() {
		return $this->_logged_queries;
	}

	function printQueryLog() {
		?>
<pre>
<?
		$queries = $this->getLoggedQueries();
		echo count($queries)." queries executed\n";
		echo implode("\n\n", $queries);
?>
</pre>
		<?
	}

	/**
	 * Override of PDO::query() to provide query logging functionality
	 * @param string $arg1
	 * @param int $arg2
	 * @param mixed $arg3
	 * @param mixed $arg4
	 * @return PDOStatement
	 */
	function query() {
		$args = func_get_args();

		if($this->_log_queries)
			$this->_logged_queries[] = (string)$args[0];

		return call_user_func_array(array('parent', 'query'), $args);
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
			foreach ($settings['queries'] as $queries) {
				foreach ((array)$queries as $query) {
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
	function checkInput($value) {
		if (is_array($value)){
			foreach ($value as $k => $v) $value[$k] = $this->checkInput($v);
			return $value;
		}

		if(is_int($value))
			return $value;

		if(is_bool($value))
			return $value ? 1 : 0;

		if($value===null) return "NULL";
		return $this->quote($value);
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
		return '"' . $text . '"';
	}

	/**
	 * Quotes a database table which could have space seperating it from an alias, both should be identified seperately
	 * @param	  string $table The table name to quo
	 * @return	 string The quoted table name
	 **/
	function quoteIdentifierTable($table) {
		return implode(" ", array_map(array($this, "quoteIdentifier"), explode(" ", $table) ) );
	}

	/**
	 * Returns the native ID method for this RDBMS.
	 * @return	 int one of DBAdapter:ID_METHOD_SEQUENCE, DBAdapter::ID_METHOD_AUTOINCREMENT.
	 */
	protected function getIdMethod() {
		return DBAdapter::ID_METHOD_AUTOINCREMENT;
	}

	/**
	 * Whether this adapter uses an ID generation system that requires getting ID _before_ performing INSERT.
	 * @return	 boolean
	 */
	function isGetIdBeforeInsert() {
		return ($this->getIdMethod() === DBAdapter::ID_METHOD_SEQUENCE);
	}

	/**
	 * Whether this adapter uses an ID generation system that requires getting ID _before_ performing INSERT.
	 * @return	 boolean
	 */
	function isGetIdAfterInsert() {
		return ($this->getIdMethod() === DBAdapter::ID_METHOD_AUTOINCREMENT);
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
