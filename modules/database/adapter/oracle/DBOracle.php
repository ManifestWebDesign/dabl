<?php

/**
 * Oracle adapter.
 */
class DBOracle extends DABLPDO {

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  string $in The string to transform to upper case.
	 * @return	 string The upper case string.
	 */
	function toUpperCase($in){
		return "UPPER(" . $in . ")";
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  string $in The string whose case to ignore.
	 * @return	 string The string in a case that can be ignored.
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
		return "SUBSTR($s, $pos, $len)";
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param	  string String to calculate length of.
	 * @return	 string
	 */
	function strLength($s){
		return "LENGTH($s)";
	}

	/**
	 * Returns SQL which limits the result set.
	 *
	 * @param string $sql
	 * @param int $offset
	 * @param int $limit
	 * @see DABLPDO::applyLimit()
	 */
	function applyLimit(&$sql, $offset, $limit) {

		$max = $offset + $limit;

		// nesting all queries, in case there's already a WHERE clause
		$sql = <<<EOF
SELECT A.*, rownum AS PROPEL\$ROWNUM
FROM (
  $sql
) A
WHERE rownum <= $max
EOF;

		if ($offset > 0) {
			$sql = <<<EOF
SELECT B.*
FROM (
  $sql
) B
WHERE B.PROPEL\$ROWNUM > $offset
EOF;
		}
	}

	protected function getIdMethod(){
		return DABLPDO::ID_METHOD_SEQUENCE;
	}

	function getId($name = null){
		if ($name === null) {
			throw new Exception("Unable to fetch next sequence ID without sequence name.");
		}

		$stmt = $this->query("SELECT " . $name . ".nextval FROM dual");
		$row = $stmt->fetch(PDO::FETCH_NUM);

		return $row[0];
	}

	function random($seed=NULL){
		return 'dbms_random.value';
	}

	/**
	 * @return Database
	 */
	function getDatabaseSchema(){

		ClassLoader::import('DATABASE:propel:');
		ClassLoader::import('DATABASE:propel:database');
		ClassLoader::import('DATABASE:propel:database:model');
		ClassLoader::import('DATABASE:propel:database:reverse');
		ClassLoader::import('DATABASE:propel:database:reverse:oracle');
		ClassLoader::import('DATABASE:propel:database:tranform');
		ClassLoader::import('DATABASE:propel:platform');

		$parser = new OracleSchemaParser();
		$parser->setConnection($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new OraclePlatform());
		$parser->parse($database);
		$database->doFinalInitialization();
		return $database;
	}

}
