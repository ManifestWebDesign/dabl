<?php

/**
 * PostgreSQL types / type map.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.8 $
 * @package   creole.drivers.pgsql
 */
class PgSQLTypes extends CreoleTypes {

	/** Map PostgreSQL native types to Creole (JDBC) types. */
	private static $typeMap = array (
		"int2" => CreoleTypes::SMALLINT,
		"int4" => CreoleTypes::INTEGER,
		"oid" => CreoleTypes::INTEGER,
		"int8" => CreoleTypes::BIGINT,
		"cash"  => CreoleTypes::DOUBLE,
		"money"  => CreoleTypes::DOUBLE,
		"numeric" => CreoleTypes::NUMERIC,
		"float4" => CreoleTypes::REAL,
		"float8" => CreoleTypes::DOUBLE,
		"bpchar" => CreoleTypes::CHAR,
		"char" => CreoleTypes::CHAR,
		"char2" => CreoleTypes::CHAR,
		"char4" => CreoleTypes::CHAR,
		"char8" => CreoleTypes::CHAR,
		"char16" => CreoleTypes::CHAR,
		"varchar" => CreoleTypes::VARCHAR,
		"text" => CreoleTypes::VARCHAR,
		"name" => CreoleTypes::VARCHAR,
		"filename" => CreoleTypes::VARCHAR,
		"bytea" => CreoleTypes::BINARY,
		"bool" => CreoleTypes::BOOLEAN,
		"date" => CreoleTypes::DATE,
		"time" => CreoleTypes::TIME,
		"abstime" => CreoleTypes::TIMESTAMP,
		"timestamp" => CreoleTypes::TIMESTAMP,
		"timestamptz" => CreoleTypes::TIMESTAMP,
		"_bool" => CreoleTypes::ARR,
		"_char" => CreoleTypes::ARR,
		"_int2" => CreoleTypes::ARR,
		"_int4" => CreoleTypes::ARR,
		"_text" => CreoleTypes::ARR,
		"_oid" => CreoleTypes::ARR,
		"_varchar" => CreoleTypes::ARR,
		"_int8" => CreoleTypes::ARR,
		"_float4" => CreoleTypes::ARR,
		"_float8" => CreoleTypes::ARR,
		"_abstime" => CreoleTypes::ARR,
		"_date" => CreoleTypes::ARR,
		"_time" => CreoleTypes::ARR,
		"_timestamp" => CreoleTypes::ARR,
		"_numeric" => CreoleTypes::ARR,
		"_bytea" => CreoleTypes::ARR,
	);

	/** Reverse lookup map, created on demand. */
	private static $reverseMap = null;

	public static function getType($pgsqlType) {
		$t = strtolower($pgsqlType);
		if (isset(self::$typeMap[$t])) {
			return self::$typeMap[$t];
		} else {
			return CreoleTypes::OTHER;
		}
	}

	public static function getNativeType($creoleType) {
		if (self::$reverseMap === null) {
			self::$reverseMap = array_flip(self::$typeMap);
		}
		return @self::$reverseMap[$creoleType];
	}

}