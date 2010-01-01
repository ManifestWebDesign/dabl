<?php

/**
 * Modified version of DBAdapter from Propel Runtime
 * Last Modified January 1st 2010 by Dan Blaisdell
 */

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://propel.phpdb.org>.
 */

/**
 * DBAdapter</code> defines the interface for a Propel database adapter.
 *
 * <p>Support for new databases is added by subclassing
 * <code>DBAdapter</code> and implementing its abstract interface, and by
 * registering the new database adapter and corresponding Creole
 * driver in the private adapters map (array) in this class.</p>
 *
 * <p>The Propel database adapters exist to present a uniform
 * interface to database access across all available databases.  Once
 * the necessary adapters have been written and configured,
 * transparent swapping of databases is theoretically supported with
 * <i>zero code change</i> and minimal configuration file
 * modifications.</p>
 *
 * @author	 Hans Lellelid <hans@xmpl.org> (Propel)
 * @author	 Jon S. Stevens <jon@latchkey.com> (Torque)
 * @author	 Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author	 Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @version	$Revision: 1011 $
 * @package	propel.adapter
 */
abstract class DBAdapter extends PDO {

	const ID_METHOD_NONE = 0;
	const ID_METHOD_AUTOINCREMENT = 1;
	const ID_METHOD_SEQUENCE = 2;

	protected $_logged_queries = array();
	protected $_log_queries = false;

	/**
	 * Creole driver to database adapter map.
	 * @var		array
	 */
	private static $adapters = array(
		'mysql' => 'DBMySQL',
		'mssql' => 'DBMSSQL',
		'oracle' => 'DBOracle',
		'pgsql' => 'DBPostgres',
		'sqlite' => 'DBSQLite'
	);

	/**
	 * Creole driver to database adapter map.
	 * @var		array
	 */
	private static $schema_readers = array(
		'DBMSSQL' => 'MSSQLDatabaseInfo',
		'DBMySQL' => 'MySQLDatabaseInfo',
		'DBOracle' => 'OCI8DatabaseInfo',
		'DBPostgres' => 'PgSQLDatabaseInfo',
		'DBSQLite' => 'SQLiteDatabaseInfo'
	);

	/**
	 * Creates a new instance of the database adapter associated
	 * with the specified Creole driver.
	 *
	 * @param	  string $driver The name of the Propel/Creole driver to
	 * create a new adapter instance for or a shorter form adapter key.
	 * @return	 DBAdapter An instance of a database adapter.
	 * @throws	 Exception if the adapter could not be instantiated.
	 */
	public static function factory($driver, $dsn, $username, $password) {
		$adapterClass = isset(self::$adapters[$driver]) ? self::$adapters[$driver] : null;
		if ($adapterClass !== null) {
			$a = new $adapterClass($dsn, $username, $password);
			return $a;
		} else {
			throw new Exception("Unsupported database driver: " . $driver . ": Check your configuration file");
		}
	}

	function getDatabaseInfo($database_name){
		$reader = self::$schema_readers[get_class($this)];
		$reader = new $reader($this, $database_name);
		return $reader;
	}

	function getLoggedQueries(){
		return $this->_logged_queries;
	}

	function printQueryLog(){
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

	function query(){
		$args = func_get_args();
		if($this->_log_queries){
			$query = (string)$args[0];
			$this->_logged_queries[] = $query;
		}
		return call_user_func_array('parent::query', $args);
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
	function initConnection(array $settings){
		if (isset($settings['charset']['value'])) {
			$this->setCharset($settings['charset']['value']);
		}
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
	function setCharset($charset){
		$this->exec("SET NAMES '" . $charset . "'");
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  string The string to transform to upper case.
	 * @return	 string The upper case string.
	 */
	public abstract function toUpperCase($in);

	/**
	 * Returns the character used to indicate the beginning and end of
	 * a piece of text used in a SQL statement (generally a single
	 * quote).
	 *
	 * @return	 string The text delimeter.
	 */
	function getStringDelimiter(){
		return '\'';
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function checkInput($value){
		if (is_array($value)){
			foreach ($value as $k => $v) $value[$k] = $this->checkInput($v);
			return $value;
		}

		if($value===null) return "NULL";
		return $this->quote($value);
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  string $in The string whose case to ignore.
	 * @return	 string The string in a case that can be ignored.
	 */
	public abstract function ignoreCase($in);

	/**
	 * This method is used to ignore case in an ORDER BY clause.
	 * Usually it is the same as ignoreCase, but some databases
	 * (Interbase for example) does not use the same SQL in ORDER BY
	 * and other clauses.
	 *
	 * @param	  string $in The string whose case to ignore.
	 * @return	 string The string in a case that can be ignored.
	 */
	function ignoreCaseInOrderBy($in){
		return $this->ignoreCase($in);
	}

	/**
	 * Returns SQL which concatenates the second string to the first.
	 *
	 * @param	  string String to concatenate.
	 * @param	  string String to append.
	 * @return	 string
	 */
	public abstract function concatString($s1, $s2);

	/**
	 * Returns SQL which extracts a substring.
	 *
	 * @param	  string String to extract from.
	 * @param	  int Offset to start from.
	 * @param	  int Number of characters to extract.
	 * @return	 string
	 */
	public abstract function subString($s, $pos, $len);

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param	  string String to calculate length of.
	 * @return	 string
	 */
	public abstract function strLength($s);

	/**
	 * Quotes database object identifiers (table names, col names, sequences, etc.).
	 * @param	  string $text The identifier to quote.
	 * @return	 string The quoted identifier.
	 */
	function quoteIdentifier($text){
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
	protected function getIdMethod(){
		return DBAdapter::ID_METHOD_AUTOINCREMENT;
	}

	/**
	 * Whether this adapter uses an ID generation system that requires getting ID _before_ performing INSERT.
	 * @return	 boolean
	 */
	function isGetIdBeforeInsert(){
		return ($this->getIdMethod() === DBAdapter::ID_METHOD_SEQUENCE);
	}

	/**
	 * Whether this adapter uses an ID generation system that requires getting ID _before_ performing INSERT.
	 * @return	 boolean
	 */
	function isGetIdAfterInsert(){
		return ($this->getIdMethod() === DBAdapter::ID_METHOD_AUTOINCREMENT);
	}

	/**
	 * Gets the generated ID (either last ID for autoincrement or next sequence ID).
	 * @return	 mixed
	 */
	function getId($name = null){
		return $this->lastInsertId($name);
	}

	/**
	 * Returns timestamp formatter string for use in date() function.
	 * @return	 string
	 */
	function getTimestampFormatter(){
		return "Y-m-d H:i:s";
	}

	/**
	 * Returns date formatter string for use in date() function.
	 * @return	 string
	 */
	function getDateFormatter(){
		return "Y-m-d";
	}

	/**
	 * Returns time formatter string for use in date() function.
	 * @return	 string
	 */
	function getTimeFormatter(){
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
	function useQuoteIdentifier(){
		return false;
	}

	/**
	 * Modifies the passed-in SQL to add LIMIT and/or OFFSET.
	 */
	public abstract function applyLimit(&$sql, $offset, $limit);

	/**
	 * Gets the SQL string that this adapter uses for getting a random number.
	 *
	 * @param	  mixed $seed (optional) seed value for databases that support this
	 */
	public abstract function random($seed = null);

}
