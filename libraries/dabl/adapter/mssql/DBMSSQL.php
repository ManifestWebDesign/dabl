<?php

/**
 * This is used to connect to a MSSQL database.
 *
 * @author	 Hans Lellelid <hans@xmpl.org> (Propel)
 * @version	$Revision: 989 $
 * @package	propel.adapter
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
		return '[' . $text . ']';
	}

	/**
	 * @see		DABLPDO::random()
	 */
	function random($seed = null) {
		return 'rand(' . ((int) $seed) . ')';
	}

	/**
	 * Simulated Limit/Offset
	 * This rewrites the $sql query to apply the offset and limit.
	 * @see		DABLPDO::applyLimit()
	 * @author	 Justin Carlson <justin.carlson@gmail.com>
	 */
	function applyLimit(&$sql, $offset, $limit) {
		// make sure offset and limit are numeric
		if(!is_numeric($offset) || !is_numeric($limit)) {
			throw new Exception('DBMSSQL::applyLimit() expects a number for argument 2 and 3');
		}

		//split the select and from clauses out of the original query
		$selectSegment = array();

		$selectText = 'SELECT ';

		if(preg_match('/\Aselect(\s+)distinct/i', $sql)) {
			$selectText .= 'DISTINCT ';
		}

		preg_match('/\Aselect(.*)from(.*)/si', $sql, $selectSegment);
		if(count($selectSegment) == 3) {
			$selectStatement = trim($selectSegment[1]);
			$fromStatement = trim($selectSegment[2]);
		} else {
			//only works with select statements, ignore limits otherwise.
			return;
			throw new Exception('DBMSSQL::applyLimit() could not locate the select statement at the start of the query. ' . $sql);
		}

		// if we're starting at offset 0 then theres no need to simulate limit,
		// just grab the top $limit number of rows
		if($offset == 0) {
			$sql = $selectText . 'TOP ' . $limit . ' ' . $selectStatement . ' FROM ' . $fromStatement;
			return;
		}

		//get the ORDER BY clause if present
		$orderStatement = stristr($fromStatement, 'ORDER BY');
		$orders = '';

		if($orderStatement !== false) {
			//remove order statement from the from statement
			$fromStatement = trim(str_replace($orderStatement, '', $fromStatement));

			$order = str_ireplace('ORDER BY', '', $orderStatement);
			$orders = explode(',', $order);

			for($i = 0; $i < count($orders); $i++) {
				$orderArr[trim(preg_replace('/\s+(ASC|DESC)$/i', '', $orders[$i]))] = array(
					'sort' => (stripos($orders[$i], ' DESC') !== false) ? 'DESC' : 'ASC',
					'key' => $i
				);
			}
		}

		//setup inner and outer select selects
		$innerSelect = '';
		$outerSelect = '';
		foreach(explode(', ', $selectStatement) as $selCol) {
			$selColArr = explode(' ', $selCol);
			$selColCount = count($selColArr) - 1;

			//make sure the current column isn't * or an aggregate
			if(strpos($selColArr[0], '*') === false && !strstr($selColArr[0], '(')) {
				if(isset($orderArr[$selColArr[0]])) {
					$orders[$orderArr[$selColArr[0]]['key']] = $selColArr[0] . ' ' . $orderArr[$selColArr[0]]['sort'];
				}

				//use the alias if one was present otherwise use the column name
				$alias = (!stristr($selCol, ' AS ')) ? $selColArr[0] : $selColArr[$selColCount];
				//don't quote the identifier if it is already quoted
				if($alias[0] != '[')
					$alias = $this->quoteIdentifier($alias);

				//save the first non-aggregate column for use in ROW_NUMBER() if required
				if(!isset($firstColumnOrderStatement)) {
					$firstColumnOrderStatement = 'ORDER BY ' . $selColArr[0];
				}

				//add an alias to the inner select so all columns will be unique
				$innerSelect .= $selColArr[0] . ' AS ' . $alias . ', ';
				$outerSelect .= $alias . ', ';
			} elseif(stristr($selCol, ' AS ')) {
				//aggregate column alias can't be used as the count column you must use the entire aggregate statement
				if(isset($orderArr[$selColArr[$selColCount]])) {
					$orders[$orderArr[$selColArr[$selColCount]]['key']] = str_replace($selColArr[$selColCount - 1] . ' ' . $selColArr[$selColCount], '', $selCol) . $orderArr[$selColArr[$selColCount]]['sort'];
				}

				//quote the alias
				$alias = $selColArr[$selColCount];
				//don't quote the identifier if it is already quoted
				if($alias[0] != '[')
					$alias = $this->quoteIdentifier($alias);
				$innerSelect .= str_replace($selColArr[$selColCount], $alias, $selCol) . ', ';
				$outerSelect .= $alias . ', ';
			} else {
				$orders[] = '(select 1)';
				$innerSelect .= $selColArr[0] . '  ';
				$outerSelect .= '*  ';
			}
		}

		if(is_array($orders)) {
			$orderStatement = 'ORDER BY ' . implode(', ', $orders);
		} else {
			//use the first non aggregate column in our select statement if no ORDER BY clause present
			if(isset($firstColumnOrderStatement)) {
				$orderStatement = $firstColumnOrderStatement;
			} else {
				throw new Exception('DBMSSQL::applyLimit() unable to find column to use with ROW_NUMBER()');
			}
		}

		//substring the select strings to get rid of the last comma and add our FROM and SELECT clauses
		$innerSelect = $selectText . 'ROW_NUMBER() OVER(' . $orderStatement . ') AS [RowNumber], ' . substr($innerSelect, 0, - 2) . ' FROM';
		//outer select can't use * because of the RowNumber column
		$outerSelect = 'SELECT ' . substr($outerSelect, 0, - 2) . ' FROM';

		//ROW_NUMBER() starts at 1 not 0
		$sql = $outerSelect . ' (' . $innerSelect . ' ' . $fromStatement . ') AS derivedb WHERE RowNumber BETWEEN ' . ($offset + 1) . ' AND ' . ($limit + $offset);
		return;
	}

	/**
	 * @return Database
	 */
	function getDatabaseSchema() {

		ClassLoader::import('ROOT:libraries:propel');
		ClassLoader::import('ROOT:libraries:propel:database');
		ClassLoader::import('ROOT:libraries:propel:database:model');
		ClassLoader::import('ROOT:libraries:propel:database:reverse');
		ClassLoader::import('ROOT:libraries:propel:database:reverse:mssql');
		ClassLoader::import('ROOT:libraries:propel:database:tranform');
		ClassLoader::import('ROOT:libraries:propel:platform');

		$parser = new MssqlSchemaParser();
		$parser->setConnection($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new MssqlPlatform());
		$parser->parse($database);
		return $database;
	}

	function beginTransaction() {
		$this->query('BEGIN TRANSACTION');
	}

	function commit() {
		$this->query('COMMIT TRANSACTION');
	}

	function rollback() {
		$this->query('ROLLBACK TRANSACTION');
	}

}
