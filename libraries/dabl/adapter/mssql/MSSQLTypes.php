<?php

/**
 * MSSQL types / type map.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.8 $
 * @package   creole.drivers.mssql
 */
class MSSQLTypes extends CreoleTypes {

	/** Map PostgreSQL native types to Creole (JDBC) types. */
	private static $typeMap = array (
		"binary" => CreoleTypes::BINARY,
		"bit" => CreoleTypes::BOOLEAN,
		"char" => CreoleTypes::CHAR,
		"datetime" => CreoleTypes::TIMESTAMP,
		"decimal() identity"  => CreoleTypes::DECIMAL,
		"decimal"  => CreoleTypes::DECIMAL,
		"image" => CreoleTypes::LONGVARBINARY,
		"int" => CreoleTypes::INTEGER,
		"int identity" => CreoleTypes::INTEGER,
		"integer" => CreoleTypes::INTEGER,
		"money" => CreoleTypes::DECIMAL,
		"nchar" => CreoleTypes::CHAR,
		"ntext" => CreoleTypes::LONGVARCHAR,
		"numeric() identity" => CreoleTypes::NUMERIC,
		"numeric" => CreoleTypes::NUMERIC,
		"nvarchar" => CreoleTypes::VARCHAR,
		"real" => CreoleTypes::REAL,
		"float" => CreoleTypes::FLOAT,
		"smalldatetime" => CreoleTypes::TIMESTAMP,
		"smallint" => CreoleTypes::SMALLINT,
		"smallint identity" => CreoleTypes::SMALLINT,
		"smallmoney" => CreoleTypes::DECIMAL,
		"sysname" => CreoleTypes::VARCHAR,
		"text" => CreoleTypes::LONGVARCHAR,
		"timestamp" => CreoleTypes::BINARY,
		"tinyint identity" => CreoleTypes::TINYINT,
		"tinyint" => CreoleTypes::TINYINT,
		"uniqueidentifier" => CreoleTypes::CHAR,
		"varbinary" => CreoleTypes::VARBINARY,
		"varchar" => CreoleTypes::VARCHAR,
		"uniqueidentifier" => CreoleTypes::CHAR,
		// SQL Server 2000 only
		"bigint identity" => CreoleTypes::BIGINT,
		"bigint" => CreoleTypes::BIGINT,
		"sql_variant" => CreoleTypes::VARCHAR,
	);
				 
	/** Reverse lookup map, created on demand. */
	private static $reverseMap = null;
	
	public static function getType($mssqlType){	
		$t = strtolower($mssqlType);
		if (isset(self::$typeMap[$t])) {
			return self::$typeMap[$t];
		} else {
			return CreoleTypes::OTHER;
		}
	}
	
	public static function getNativeType($creoleType){
		if (self::$reverseMap === null) {
			self::$reverseMap = array_flip(self::$typeMap);
		}
		return @self::$reverseMap[$creoleType];
	}
	
}