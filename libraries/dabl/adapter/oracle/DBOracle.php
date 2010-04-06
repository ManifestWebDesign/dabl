<?php

/**
 * Oracle adapter.
 *
 * @author	 David Giffin <david@giffin.org> (Propel)
 * @author	 Hans Lellelid <hans@xmpl.org> (Propel)
 * @author	 Jon S. Stevens <jon@clearink.com> (Torque)
 * @author	 Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author	 Bill Schneider <bschneider@vecna.com> (Torque)
 * @author	 Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @version	$Revision: 718 $
 * @package	propel.adapter
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
	 * @see		DABLPDO::applyLimit()
	 */
	function applyLimit(&$sql, $offset, $limit){
		 $sql =
			'SELECT B.* FROM (  '
			.  'SELECT A.*, rownum AS PROPEL$ROWNUM FROM (  '
			. $sql
			. '  ) A '
			.  ' ) B WHERE ';

		if ( $offset > 0 ) {
			$sql				.= ' B.PROPEL$ROWNUM > ' . $offset;

			if ( $limit > 0 )
			{
				$sql			.= ' AND B.PROPEL$ROWNUM <= '
									. ( $offset + $limit );
			}
		} else {
			$sql				.= ' B.PROPEL$ROWNUM <= ' . $limit;
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

		Module::import('ROOT:libraries:propel');
		Module::import('ROOT:libraries:propel:database');
		Module::import('ROOT:libraries:propel:database:model');
		Module::import('ROOT:libraries:propel:database:reverse');
		Module::import('ROOT:libraries:propel:database:reverse:oracle');
		Module::import('ROOT:libraries:propel:database:tranform');
		Module::import('ROOT:libraries:propel:platform');

		$parser = new OracleSchemaParser();
		$parser->setConnection($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new OraclePlatform());
		$parser->parse($database);
		return $database;
	}

}
