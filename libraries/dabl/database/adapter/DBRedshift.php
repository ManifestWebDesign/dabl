<?php

class DBRedshift extends DABLPDO {

	/**
	 * Returns SQL that converts a date value to the start of the hour
	 *
	 * @param string $date
	 * @return string
	 */
	function hourStart($date) {
		return "DATE_TRUNC('hour', $date::TIMESTAMP)::TIMESTAMP";
	}

	/**
	 * Returns SQL that converts a date value to the start of the day
	 *
	 * @param string $date
	 * @return string
	 */
	function dayStart($date) {
		return "DATE_TRUNC('day', $date::DATE)::DATE";
	}

	/**
	 * Returns SQL that converts a date value to the first day of the week
	 *
	 * @param string $date
	 * @return string
	 */
	function weekStart($date) {
		return "(DATE_TRUNC('week', $date::DATE) - '1 days'::INTERVAL)::DATE";
	}

	/**
	 * Returns SQL that converts a date value to the first day of the month
	 *
	 * @param string $date
	 * @return string
	 */
	function monthStart($date) {
		return "DATE_TRUNC('month', $date::DATE)::DATE";
	}

	/**
	 * Returns SQL which converts the date value to its value in the target timezone
	 *
	 * @param string $date SQL column expression
	 * @param string|DateTimeZone $to_tz DateTimeZone or timezone id
	 * @param string|DateTimeZone $from_tz DateTimeZone or timezone id
	 * @return string
	 */
	function convertTimeZone($date, $to_tz, $from_tz = null) {
		if ($to_tz instanceof DateTimeZone) {
			$to_tz = $to_tz->getName();
		}
		if ($from_tz) {
			if ($from_tz instanceof DateTimeZone) {
				$from_tz = $from_tz->getName();
			}
			return "(($date)::TIMESTAMP AT TIME ZONE '$from_tz' AT TIME ZONE '$to_tz')";
		}

		return "(($date)::TIMESTAMPTZ AT TIME ZONE '$to_tz')";
	}

	/**
	 * @var int the current transaction depth
	 */
	protected $_transactionDepth = 0;

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
		return DABLPDO::ID_METHOD_AUTOINCREMENT;
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

		ClassLoader::import('DATABASE:propel:');
		ClassLoader::import('DATABASE:propel:model');
		ClassLoader::import('DATABASE:propel:reverse');
		ClassLoader::import('DATABASE:propel:reverse:pgsql');
		ClassLoader::import('DATABASE:propel:platform');

		$parser = new RedshiftSchemaParser($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new PgsqlPlatform($this));
		$parser->parse($database);
		$database->doFinalInitialization();
		return $database;
	}

	/**
	 * Start transaction
	 *
	 * @return bool|void
	 */
	public function beginTransaction() {
		if ($this->_transactionDepth == 0) {
			parent::beginTransaction();
		} else {
			$this->exec("SAVEPOINT LEVEL{$this->_transactionDepth}");
		}

		$this->_transactionDepth++;
	}

	/**
	 * Commit current transaction
	 *
	 * @return bool|void
	 */
	public function commit() {
		$this->_transactionDepth--;

		if ($this->_transactionDepth == 0) {
			parent::commit();
		} else {
			$this->exec("RELEASE SAVEPOINT LEVEL{$this->_transactionDepth}");
		}
	}

	/**
	 * Rollback current transaction,
	 *
	 * @throws PDOException if there is no transaction started
	 * @return bool|void
	 */
	public function rollBack() {
		if ($this->_transactionDepth == 0) {
			throw new PDOException('Rollback error : There is no transaction started');
		}

		$this->_transactionDepth--;

		if ($this->_transactionDepth == 0) {
			parent::rollBack();
		} else {
			$this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->_transactionDepth}");
		}
	}
}
