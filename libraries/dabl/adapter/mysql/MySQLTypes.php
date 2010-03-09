<?php

/**
 * MySQL types / type map.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.8 $
 * @package   creole.drivers.mysql
 */
class MySQLTypes extends CreoleTypes {

	/** Map MySQL native types to Creole (JDBC) types. */
	private static $typeMap = array(
		'tinyint' => CreoleTypes::TINYINT,
		'smallint' => CreoleTypes::SMALLINT,
		'mediumint' => CreoleTypes::SMALLINT,
		'int' => CreoleTypes::INTEGER,
		'integer' => CreoleTypes::INTEGER,
		'bigint' => CreoleTypes::BIGINT,
		'int24' => CreoleTypes::BIGINT,
		'real' => CreoleTypes::REAL,
		'float' => CreoleTypes::FLOAT,
		'decimal' => CreoleTypes::DECIMAL,
		'numeric' => CreoleTypes::NUMERIC,
		'double' => CreoleTypes::DOUBLE,
		'char' => CreoleTypes::CHAR,
		'varchar' => CreoleTypes::VARCHAR,
		'date' => CreoleTypes::DATE,
		'time' => CreoleTypes::TIME,
		'year' => CreoleTypes::YEAR,
		'datetime' => CreoleTypes::TIMESTAMP,
		'timestamp' => CreoleTypes::TIMESTAMP,
		'tinyblob' => CreoleTypes::BINARY,
		'blob' => CreoleTypes::VARBINARY,
		'mediumblob' => CreoleTypes::VARBINARY,
		'longblob' => CreoleTypes::VARBINARY,
		'longtext' => CreoleTypes::LONGVARCHAR,
		'tinytext' => CreoleTypes::VARCHAR,
		'mediumtext' => CreoleTypes::LONGVARCHAR,
		'text' => CreoleTypes::LONGVARCHAR,
		'enum' => CreoleTypes::CHAR,
		'set' => CreoleTypes::CHAR,
	);

	/** Reverse mapping, created on demand. */
	private static $reverseMap = null;

	/**
	 * This method returns the generic Creole (JDBC-like) type
	 * when given the native db type.
	 * @param string $nativeType DB native type (e.g. 'TEXT', 'byetea', etc.).
	 * @return int Creole native type (e.g. CreoleTypes::LONGVARCHAR, CreoleTypes::BINARY, etc.).
	 */
	public static function getType($nativeType) {
		$t = strtolower($nativeType);
		if (isset(self::$typeMap[$t])) {
			return self::$typeMap[$t];
		} else {
			return CreoleTypes::OTHER;
		}
	}

	/**
	 * This method will return a native type that corresponds to the specified
	 * Creole (JDBC-like) type.
	 * If there is more than one matching native type, then the LAST defined
	 * native type will be returned.
	 * @param int $creoleType
	 * @return string Native type string.
	 */
	public static function getNativeType($creoleType) {
		if (self::$reverseMap === null) {
			self::$reverseMap = array_flip(self::$typeMap);
		}
		return @self::$reverseMap[$creoleType];
	}

}