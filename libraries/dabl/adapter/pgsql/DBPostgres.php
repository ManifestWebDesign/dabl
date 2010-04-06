<?php

/**
 * This is used to connect to PostgresQL databases.
 *
 * <a href="http://www.pgsql.org">http://www.pgsql.org</a>
 *
 * @author	 Hans Lellelid <hans@xmpl.org> (Propel)
 * @author	 Hakan Tandogan <hakan42@gmx.de> (Torque)
 * @version	$Revision: 1011 $
 * @package	propel.adapter
 */
class DBPostgres extends DABLPDO {

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  string $in The string to transform to upper case.
	 * @return	 string The upper case string.
	 */
	function toUpperCase($in) {
		return "UPPER(" . $in . ")";
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  in The string whose case to ignore.
	 * @return	 The string in a case that can be ignored.
	 */
	function ignoreCase($in) {
		return "UPPER(" . $in . ")";
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
		return "substring($s from $pos" . ($len > -1 ? "for $len" : "") . ")";
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param	  string String to calculate length of.
	 * @return	 string
	 */
	function strLength($s) {
		return "char_length($s)";
	}

	/**
	 * @see		DABLPDO::getIdMethod()
	 */
	protected function getIdMethod() {
		return DABLPDO::ID_METHOD_SEQUENCE;
	}

	/**
	 * Gets ID for specified sequence name.
	 */
	function getId($table_name, $column_name) {
		return $this->query("SELECT currval(pg_get_serial_sequence({$this->quote($table_name)}, {$this->quote($column_name)}))")->fetchColumn(0);
	}

	/**
	 * Returns timestamp formatter string for use in date() function.
	 * @return	 string
	 */
	function getTimestampFormatter() {
		return "Y-m-d H:i:s O";
	}

	/**
	 * Returns timestamp formatter string for use in date() function.
	 * @return	 string
	 */
	function getTimeFormatter() {
		return "H:i:s O";
	}

	/**
	 * @see		DABLPDO::applyLimit()
	 */
	function applyLimit(&$sql, $offset, $limit) {
		if ( $limit > 0 ) {
			$sql .= " LIMIT ".$limit;
		}
		if ( $offset > 0 ) {
			$sql .= " OFFSET ".$offset;
		}
	}

	/**
	 * @see		DABLPDO::random()
	 */
	function random($seed=NULL) {
		return 'random()';
	}

	/**
	 * @return Database
	 */
	function getDatabaseSchema(){

		Module::import('ROOT:libraries:propel');
		Module::import('ROOT:libraries:propel:database');
		Module::import('ROOT:libraries:propel:database:model');
		Module::import('ROOT:libraries:propel:database:reverse');
		Module::import('ROOT:libraries:propel:database:reverse:pgsql');
		Module::import('ROOT:libraries:propel:database:tranform');
		Module::import('ROOT:libraries:propel:platform');

		$parser = new PgsqlSchemaParser();
		$parser->setConnection($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new PgsqlPlatform());
		$parser->parse($database);
		return $database;
	}
}
