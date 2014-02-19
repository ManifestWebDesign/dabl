<?php

/**
 * This is used to connect to a MSSQL database.
 */
class DBMSSQL extends DABLPDO {

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  in The string to transform to upper case.
	 * @return	 The upper case string.
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
		return "($s1 + $s2)";
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
		return "SUBSTRING($s, $pos, $len)";
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param	  string String to calculate length of.
	 * @return	 string
	 */
	function strLength($s) {
		return "LEN($s)";
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

		return '[' . str_replace('.', '].[', $text) . ']';
	}

	/**
	 * @see		DABLPDO::random()
	 */
	function random($seed = null) {
		return 'rand(' . ((int) $seed) . ')';
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
	function dateFormat($field, $format, $alias = null) {
		$alias = $alias ? (' AS "' . $this->quoteIdentifier($alias) . '"') : '';

		// todo: use strtok() to parse $format
		$parts = array();
		foreach (explode('-', $format) as $part) {
			$expr = false;
			switch (strtolower($part)) {
				case 'yyyy': case 'yy': case '%y':
					$expr = "DATEPART(YY, {$field})";
					break;
				case '%x':
					$expr = "(CASE WHEN DATEPART(ISOWK, {$field}) - DATEPART(WW, {$field}) > 49 THEN -1 ELSE 0 END)+DATEPART(YY, {$field})";
					break;
				case 'ww': case 'w': case '%v':
					$expr = "DATEPART(ISOWK, {$field})";
					break;
				case 'mm': case 'm': case '%m':
					$expr = "DATEPART(MM, {$field})";
					break;
				case 'dd': case 'd': case '%d':
					$expr = "DATEPART(DD, {$field})";
					break;
				default:
					$expr = "DATEPART({$part}, {$field})";
					break;
			}
			if ($expr) {
				$expr = "CAST({$expr} AS VARCHAR)";
				$length = false;

				switch ($part) {
					case 'YYYY': case 'yyyy': case '%Y':
						$length = 4;
						break;
					case 'YY': case 'yy': case '%y':
					case '%d': case 'DD': case 'dd':
					case '%m': case 'MM': case 'mm':
						$length = 2;
						break;
				}

				if ($length) {
					$expr = "RIGHT('" . str_repeat('0', $length) . "' + {$expr}, {$length})";
				}

				$parts[] = $expr;
			}
		}

		foreach ($parts as &$v)
			$v = "CAST({$v} AS VARCHAR)";
		return join("+ '-' +", $parts) . $alias;
	}

	/**
	 * Simulated Limit/Offset
	 *
	 * This rewrites the $sql query to apply the offset and limit.
	 * some of the ORDER BY logic borrowed from Doctrine MsSqlPlatform
	 *
	 * @see       AdapterInterface::applyLimit()
	 * @author    Benjamin Runnels <kraven@kraven.org>
	 *
	 * @param     string   $sql
	 * @param     integer  $offset
	 * @param     integer  $limit
	 *
	 * @return    void
	 */
	public function applyLimit(&$sql, $offset, $limit) {
		// make sure offset and limit are numeric
		if (!is_numeric($offset) || !is_numeric($limit)) {
			throw new InvalidArgumentException('MssqlAdapter::applyLimit() expects a number for argument 2 and 3');
		}

		//split the select and from clauses out of the original query
		$selectSegment = array();

		$selectText = 'SELECT ';

		preg_match('/\Aselect(.*)from(.*)/si', $sql, $selectSegment);
		if (count($selectSegment) == 3) {
			$selectStatement = trim($selectSegment[1]);
			$fromStatement = trim($selectSegment[2]);
		} else {
			throw new RuntimeException('MssqlAdapter::applyLimit() could not locate the select statement at the start of the query.');
		}

		if (preg_match('/\Aselect(\s+)distinct/i', $sql)) {
			$selectText .= 'DISTINCT ';
			$selectStatement = str_ireplace('distinct ', '', $selectStatement);
		}

		// if we're starting at offset 0 then theres no need to simulate limit,
		// just grab the top $limit number of rows
		if ($offset == 0) {
			$sql = $selectText . 'TOP ' . $limit . ' ' . $selectStatement . ' FROM ' . $fromStatement;

			return;
		}

		//get the ORDER BY clause if present
		$orderStatement = stristr($fromStatement, 'ORDER BY');
		$orders = '';

		if ($orderStatement !== false) {
			//remove order statement from the from statement
			$fromStatement = trim(str_replace($orderStatement, '', $fromStatement));

			$order = str_ireplace('ORDER BY', '', $orderStatement);
			$orders = explode(',', $order);

			for ($i = 0; $i < count($orders); $i++) {
				$orderArr[trim(preg_replace('/\s+(ASC|DESC)$/i', '', $orders[$i]))] = array(
					'sort' => (stripos($orders[$i], ' DESC') !== false) ? 'DESC' : 'ASC',
					'key' => $i
				);
			}
		}

		//setup inner and outer select selects
		$innerSelect = '';
		$outerSelect = '';
		foreach (explode(', ', $selectStatement) as $selCol) {
			$selColArr = explode(' ', $selCol);
			$selColCount = count($selColArr) - 1;

			//make sure the current column isn't * or an aggregate
			if (strpos($selColArr[0], '*') === false && !strstr($selColArr[0], '(')) {
				if (isset($orderArr[$selColArr[0]])) {
					$orders[$orderArr[$selColArr[0]]['key']] = $selColArr[0] . ' ' . $orderArr[$selColArr[0]]['sort'];
				}

				//use the alias if one was present otherwise use the column name
				$alias = (!stristr($selCol, ' AS ')) ? $selColArr[0] : $selColArr[$selColCount];
				//don't quote the identifier if it is already quoted
				if ($alias[0] != '[') {
					$alias = $this->quoteIdentifier($alias);
				}

				//save the first non-aggregate column for use in ROW_NUMBER() if required
				if (!isset($firstColumnOrderStatement)) {
					$firstColumnOrderStatement = 'ORDER BY ' . $selColArr[0];
				}

				//add an alias to the inner select so all columns will be unique
				$innerSelect .= $selColArr[0] . ' AS ' . $alias . ', ';
				$outerSelect .= $alias . ', ';
			} elseif(stristr($selCol, ' AS ')) {
				//agregate columns must always have an alias clause
				if (!stristr($selCol, ' AS ')) {
					throw new RuntimeException('MssqlAdapter::applyLimit() requires aggregate columns to have an Alias clause');
				}

				//aggregate column alias can't be used as the count column you must use the entire aggregate statement
				if (isset($orderArr[$selColArr[$selColCount]])) {
					$orders[$orderArr[$selColArr[$selColCount]]['key']] = str_replace($selColArr[$selColCount - 1] . ' ' . $selColArr[$selColCount], '', $selCol) . $orderArr[$selColArr[$selColCount]]['sort'];
				}

				//quote the alias
				$alias = $selColArr[$selColCount];
				//don't quote the identifier if it is already quoted
				if ($alias[0] != '[') {
					$alias = $this->quoteIdentifier($alias);
				}

				$innerSelect .= str_replace($selColArr[$selColCount], $alias, $selCol) . ', ';
				$outerSelect .= $alias . ', ';
			} else {
				$orders[] = '(select 1)';
				$innerSelect .= $selColArr[0] . '  ';
				$outerSelect .= '*  ';
			}
		}

		if (is_array($orders)) {
			$orderStatement = 'ORDER BY ' . implode(', ', $orders);
		} else {
			//use the first non aggregate column in our select statement if no ORDER BY clause present
			if (isset($firstColumnOrderStatement)) {
				$orderStatement = $firstColumnOrderStatement;
			} else {
				throw new RuntimeException('MssqlAdapter::applyLimit() unable to find column to use with ROW_NUMBER()');
			}
		}

		//substring the select strings to get rid of the last comma and add our FROM and SELECT clauses
		$innerSelect = $selectText . 'ROW_NUMBER() OVER(' . $orderStatement . ') AS [RowNumber], ' . substr($innerSelect, 0, - 2) . ' FROM';
		//outer select can't use * because of the RowNumber column
		$outerSelect = 'SELECT ' . substr($outerSelect, 0, - 2) . ' FROM';

		//ROW_NUMBER() starts at 1 not 0
		$sql = $outerSelect . ' (' . $innerSelect . ' ' . $fromStatement . ') AS derivedb WHERE RowNumber BETWEEN ' . ($offset + 1) . ' AND ' . ($limit + $offset);
	}

	/**
	 * @return Database
	 */
	function getDatabaseSchema() {

		ClassLoader::import('DATABASE:propel:');
		ClassLoader::import('DATABASE:propel:model');
		ClassLoader::import('DATABASE:propel:reverse');
		ClassLoader::import('DATABASE:propel:reverse:mssql');
		ClassLoader::import('DATABASE:propel:platform');

		$parser = new MssqlSchemaParser($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new MssqlPlatform($this));
		$parser->parse($database);
		$database->doFinalInitialization();
		return $database;
	}

//	function beginTransaction() {
//		$this->query('BEGIN TRANSACTION');
//	}
//
//	function commit() {
//		$this->query('COMMIT TRANSACTION');
//	}
//
//	function rollback() {
//		$this->query('ROLLBACK TRANSACTION');
//	}

	public function prepareInput($value) {
		if (
			is_string($value)
			&& function_exists('mb_detect_encoding')
			&& mb_detect_encoding($value) === 'UTF-8'
		) {
			return 'N' . parent::prepareInput($value);
		}

		return parent::prepareInput($value);
	}

}