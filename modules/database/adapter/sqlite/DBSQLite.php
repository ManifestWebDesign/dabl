<?php

/**
 * This is used in order to connect to a SQLite database.
 */
class DBSQLite extends DABLPDO {

	/**
	 * For SQLite this method has no effect, since SQLite doesn't support specifying a character
	 * set (or, another way to look at it, it doesn't require a single character set per DB).
	 *
	 * @param	  string The charset encoding.
	 * @throws	 Exception If the specified charset doesn't match sqlite_libencoding()
	 */
	function setCharset($charset) {
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  in The string to transform to upper case.
	 * @return	 The upper case string.
	 */
	function toUpperCase($in) {
		return 'UPPER(' . $in . ')';
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  in The string whose case to ignore.
	 * @return	 The string in a case that can be ignored.
	 */
	function ignoreCase($in) {
		return 'UPPER(' . $in . ')';
	}

	/**
	 * Returns SQL which concatenates the second string to the first.
	 *
	 * @param	  string String to concatenate.
	 * @param	  string String to append.
	 * @return	 string
	 */
	function concatString($s1, $s2) {
		return "($s1 || $s2)";
	}

	/**
	 * Returns SQL which extracts a substring.
	 *
	 * @param	  string String to extract from.
	 * @param	  int Offset to start from.
	 * @param	  int Number of characters to extract.
	 * @return	 string
	 */
	function subString($s, $pos, $len) {
		return "substr($s, $pos, $len)";
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param	  string String to calculate length of.
	 * @return	 string
	 */
	function strLength($s) {
		return "length($s)";
	}

	/**
	 * @see		DABLPDO::quoteIdentifier()
	 */
	function quoteIdentifier($text) {
		if (is_array($text)) {
			return array_map(array($this, 'quoteIdentifier'), $text);
		}

		if (strpos($text, '[') !== false || strpos($text, ' ') !== false || strpos($text, '(') !== false || strpos($text, '*') !== false) {
			return $text;
		}
		
		return '[' . implode('].[', explode('.', $text)) . ']';
	}

	/**
	 * @see		DABLPDO::applyLimit()
	 */
	function applyLimit(&$sql, $offset, $limit) {
		if ( $limit > 0 ) {
			$sql .= " LIMIT " . $limit . ($offset > 0 ? " OFFSET " . $offset : "");
		} elseif ( $offset > 0 ) {
			$sql .= " LIMIT -1 OFFSET " . $offset;
		}
	}

	function random($seed=NULL) {
		return 'random()';
	}

	/**
	 * @return Database
	 */
	function getDatabaseSchema(){

		ClassLoader::import('DATABASE:propel');
		ClassLoader::import('DATABASE:propel:database');
		ClassLoader::import('DATABASE:propel:database:model');
		ClassLoader::import('DATABASE:propel:database:reverse');
		ClassLoader::import('DATABASE:propel:database:reverse:sqlite');
		ClassLoader::import('DATABASE:propel:database:tranform');
		ClassLoader::import('DATABASE:propel:platform');

		$parser = new SqliteSchemaParser();
		$parser->setConnection($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new SqlitePlatform());
		$parser->parse($database);
		$database->doFinalInitialization();
		return $database;
	}

}
