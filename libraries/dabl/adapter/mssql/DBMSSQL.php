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
		return 'rand('.((int) $seed).')';
	}

	/**
	 * Simulated Limit/Offset
	 * This rewrites the $sql query to apply the offset and limit.
	 * @see		DABLPDO::applyLimit()
	 * @author	 Justin Carlson <justin.carlson@gmail.com>
	 */
	function applyLimit(&$sql, $offset, $limit) {
		// make sure offset and limit are numeric
		if (!is_numeric($offset) || !is_numeric($limit)) {
			throw new Exception("DBMSSQL::applyLimit() expects a number for argument 2 and 3");
		}

		//split the select and from clauses out of the original query
		$selectSegment = array();
		preg_match('/\Aselect(.*)from(.*)/si',$sql,$selectSegment);
		if (count($selectSegment)==3) {
			$selectStatement = trim($selectSegment[1]);
			$fromStatement = trim($selectSegment[2]);
		}
		else {
			throw new Exception("DBMSSQL::applyLimit() could not locate the select statement at the start of the query. ");
		}

		//handle the ORDER BY clause if present
		$orderSegment = array();
		preg_match('/order by(.*)\Z/si',$fromStatement,$orderSegment);
		if (count($orderSegment)==2) {
			//remove the ORDER BY from $sql
			$fromStatement = trim(str_replace($orderSegment[0], '', $fromStatement));
			//the ORDER BY clause is used in our inner select ROW_NUMBER() clause
			$countColumn = trim($orderSegment[1]);
		}

		//setup inner and outer select selects
		$innerSelect = '';
		$outerSelect = '';
		foreach(explode(', ',$selectStatement) as $selCol) {
			@list($column,,$alias) = explode(' ', $selCol);
			//make sure the current column isn't * or an aggregate
			if ($column!='*' && !strstr($column,'(')) {
				//we can use the first non-aggregate column for ROW_NUMBER() if it wasn't already set from an order by clause
				if(!isset($countColumn)) {
					$countColumn = $column;
				}

				//add an alias to the inner select so all columns will be unique
				$innerSelect .= $column." AS [$column],";

				//use the alias in the outer select if one was present on the original select column
				if(isset($alias)) {
					$outerSelect .= "[$column] AS $alias,";
				} else {
					$outerSelect .= "[$column],";
				}
			} else {
				//agregate columns must always have an alias clause
				if(!isset($alias)) {
					throw new Exception("DBMSSQL::applyLimit() requires aggregate columns to have an Alias clause");
				}
				//use the whole aggregate column in the inner select
				$innerSelect .= "$selCol,";
				//only add the alias for the aggregate to the outer select
				$outerSelect .= "$alias,";
			}
		}

		//check if we got this far and still don't have a viable column to user with ROW_NUMBER()
		if(!isset($countColumn)) {
			throw new Exception("DBMSSQL::applyLimit() requires an ORDER BY clause or at least one non-aggregate column in the select statement");
		}

		//ROW_NUMBER() starts at 1 not 0
		$from = ($offset+1);
		$to = ($limit+$offset);

		//substring our select strings to get rid of the last comma and add our FROM and SELECT clauses
		$innerSelect = "SELECT ROW_NUMBER() OVER(ORDER BY $countColumn) AS RowNumber, ".substr($innerSelect,0,-1).' FROM';
		$outerSelect = 'SELECT '.substr($outerSelect,0,-1).' FROM';

		// build the query
		$sql = "$outerSelect ($innerSelect $fromStatement) AS derivedb WHERE RowNumber BETWEEN $from AND $to";
	}

	function lastInsertId() {
		$query = "SELECT scope_identity() as ID";
		$result = $this->query($query);
		foreach($result as $r) {
			return (int)$r['ID'];
		}
	}

	/**
	 * @return Database
	 */
	function getDatabaseSchema(){

		Module::import('ROOT:libraries:propel');
		Module::import('ROOT:libraries:propel:database');
		Module::import('ROOT:libraries:propel:database:model');
		Module::import('ROOT:libraries:propel:database:reverse');
		Module::import('ROOT:libraries:propel:database:reverse:mssql');
		Module::import('ROOT:libraries:propel:database:tranform');
		Module::import('ROOT:libraries:propel:platform');

		$parser = new MssqlSchemaParser();
		$parser->setConnection($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new MssqlPlatform());
		$parser->parse($database);
		return $database;
	}

}
