<?php

/**
 * This is used in order to connect to a MySQL database.
 */
class DBMySQL extends DABLPDO {

	private $_transaction_count = 0;
	private $_rollback_connection = false;

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  in The string to transform to upper case.
	 * @return	 The upper case string.
	 */
	function toUpperCase($in){
		return "UPPER(" . $in . ")";
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  in The string whose case to ignore.
	 * @return	 The string in a case that can be ignored.
	 */
	function ignoreCase($in){
		return "UPPER(" . $in . ")";
	}

	/**
	 * Returns SQL which concatenates the second string to the first.
	 *
	 * @param	  string String to concatenate.
	 * @param	  string String to append.
	 * @return	 string
	 */
	function concatString($s1, $s2){
		return "CONCAT($s1, $s2)";
	}

	/**
	 * Returns SQL which extracts a substring.
	 *
	 * @param	  string String to extract from.
	 * @param	  int Offset to start from.
	 * @param	  int Number of characters to extract.
	 * @return	 string
	 */
	function subString($s, $pos, $len){
		return "SUBSTRING($s, $pos, $len)";
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param	  string String to calculate length of.
	 * @return	 string
	 */
	function strLength($s){
		return "CHAR_LENGTH($s)";
	}


	/**
	 * Locks the specified table.
	 *
	 * @param	  string $table The name of the table to lock.
	 * @throws	 PDOException No Statement could be created or
	 * executed.
	 */
	function lockTable($table){
		$this->exec("LOCK TABLE " . $table . " WRITE");
	}

	/**
	 * Unlocks the specified table.
	 *
	 * @param	  string $table The name of the table to unlock.
	 * @throws	 PDOException No Statement could be created or
	 * executed.
	 */
	function unlockTable($table){
		$statement = $this->exec("UNLOCK TABLES");
	}

	/**
	 * @see		DABLPDO::quoteIdentifier()
	 */
	function quoteIdentifier($text) {
		$quote = '`';
		
		if (is_array($text)) {
			return array_map(array($this, 'quoteIdentifier'), $text);
		}

		if (strpos($text, $quote) !== false || strpos($text, ' ') !== false || strpos($text, '(') !== false || strpos($text, '*') !== false) {
			return $text;
		}
		
		return $quote . implode("$quote.$quote", explode('.', $text)) . $quote;
	}

	/**
	 * @see		DABLPDO::useQuoteIdentifier()
	 */
	function useQuoteIdentifier(){
		return true;
	}

	/**
	 * @see		DABLPDO::applyLimit()
	 */
	function applyLimit(&$sql, $offset, $limit){
		if ( $limit > 0 ) {
			$sql .= " LIMIT " . ($offset > 0 ? $offset . ", " : "") . $limit;
		} else if ( $offset > 0 ) {
			$sql .= " LIMIT " . $offset . ", 18446744073709551615";
		}
	}

	/**
	 * @see		DABLPDO::random()
	 */
	function random($seed = null){
		return 'rand('.((int) $seed).')';
	}

	/**
	 * Convert $field to the format given in $format.
	 *
	 * @see DABLPDO::dateFormat
	 * @param string $field This will *not* be quoted
	 * @param string $format Date format
	 * @param string $alias Alias for the new field - WILL be quoted, if provided
	 * @return string
	 */
	function dateFormat($field, $format, $alias=null) {
		$alias = $alias ? " AS ".$this->quoteIdentifier($alias) : '';

		return "DATE_FORMAT({$field}, '{$format}'){$alias}";
	}

	/**
	 * Begin a (possibly nested) transaction.
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @see PDO::beginTransaction()
	 */
	function beginTransaction() {
		if ($this->_transaction_count<=0) {
			$this->_rollback_connection = false;
			$this->_transaction_count = 0;
			parent::beginTransaction();
		}
		++$this->_transaction_count;
	}

	/**
	 * Commit a (possibly nested) transaction.
	 * FIXME: Make this throw an Exception of a DABL class
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @see PDO::commit()
	 * @throws Exception
	 */
	function commit() {
		if ($this->_transaction_count<=0)
			throw new Exception('DABL: Attempting to commit outside of a transaction');

		--$this->_transaction_count;

		if ($this->_transaction_count==0) {
			if ($this->_rollback_connection) {
				parent::rollback();
				throw new Exception('DABL: attempting to commit a rolled back transaction');
			} else {
				return parent::commit();
			}
		}
	}

	/**
	 * Rollback, and prevent all further commits in this transaction.
	 * FIXME: Make this throw an Exception of a DABL class
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @see PDO::rollback()
	 * @throws Exception
	 */
	function rollback() {
		if ($this->_transaction_count<=0)
			throw new Exception('DABL: Attempting to rollback outside of a transaction');

		--$this->_transaction_count;

		$this->_rollback_connection = true;
		if ($this->_transaction_count==0) {
			return parent::rollback();
		}
	}

	/**
	 * Utility function for writing test cases.
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @return int
	 */
	function getTransactionCount() {
		return $this->_transaction_count;
	}

	/**
	 * Utility function for writing test cases.
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @return bool
	 */
	function getRollbackImminent() {
		return $this->_rollback_connection;
	}

	/**
	 * @return Database
	 */
	function getDatabaseSchema(){
		
		ClassLoader::import('DATABASE:propel:');
		ClassLoader::import('DATABASE:propel:database');
		ClassLoader::import('DATABASE:propel:database:model');
		ClassLoader::import('DATABASE:propel:database:reverse');
		ClassLoader::import('DATABASE:propel:database:reverse:mysql');
		ClassLoader::import('DATABASE:propel:database:tranform');
		ClassLoader::import('DATABASE:propel:platform');

		$parser = new MysqlSchemaParser();
		$parser->setConnection($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new MysqlPlatform());
		$parser->parse($database);
		$database->doFinalInitialization();
		return $database;
	}

}
