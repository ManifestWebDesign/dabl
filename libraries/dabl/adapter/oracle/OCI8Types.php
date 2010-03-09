<?php

/**
 * Oracle types / type map.
 *
 * @author	David Giffin <david@giffin.org>
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.8 $
 * @package   creole.drivers.oracle
 */
class OCI8Types extends CreoleTypes {

	/** Map Oracle native types to Creole (JDBC) types. */
	private static $typeMap = array(
		'char' => CreoleTypes::CHAR,
		'varchar2' => CreoleTypes::VARCHAR,
		'long' => CreoleTypes::LONGVARCHAR,
		'number' => CreoleTypes::NUMERIC,
		'float' => CreoleTypes::FLOAT,
		'integer' => CreoleTypes::INTEGER,
		'smallint' => CreoleTypes::SMALLINT,
		'double' => CreoleTypes::DOUBLE,
		'raw' => CreoleTypes::VARBINARY,
		'longraw' => CreoleTypes::LONGVARBINARY,
		'date' => CreoleTypes::TIMESTAMP,
		'blob' => CreoleTypes::BLOB,
		'clob' => CreoleTypes::CLOB,
		'varray' => CreoleTypes::ARR,
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
