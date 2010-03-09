<?php

/**
 * Represents a Column.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.13 $
 * @package   creole.metadata
 */
class ColumnInfo {

	// FIXME
	//	- Currently all member attributes are public.  This should be fixed
	// when PHP's magic __sleep() and __wakeup() functions & serialization support
	// handles protected/private members. (if ever)

	/** Column name */
	public $name;

	/** Column Creole type. */
	public $type;

	/** Column native type */
	public $nativeType;

	/** Column length */
	public $size;

	/** Column presision */
	public $precision;

	/** Column scale (number of digits after decimal ) */
	public $scale;

	/** Is nullable? */
	public $isNullable;

	/** Default value */
	public $defaultValue;

	/** Is autoincrement? */
	public $isAutoIncrement;

	/** Table */
	public $table;

	/**
	 * Additional and optional vendor specific information.
	 * @var vendorSpecificInfo
	 */
	protected $vendorSpecificInfo = array();

	/**
	 * Construct a new ColumnInfo object.
	 *
	 * @param TableInfo $table The table that owns this column.
	 * @param string $name Column name.
	 * @param int $type Creole type.
	 * @param string $nativeType Native type name.
	 * @param int $size Column length.
	 * @param int $scale Column scale (number of digits after decimal).
	 * @param boolean $is_nullable Whether col is nullable.
	 * @param mixed $default Default value.
	 * @param boolean $is_auto_increment Whether col is of autoIncrement type.
	 */
	function __construct(TableInfo $table, $name, $type = null, $nativeType = null, $size = null, $precision=null, $scale = null, $is_nullable = null, $default = null, $is_auto_increment = null, $vendorInfo = array()) {
		$this->table = $table;
		$this->name = $name;
		$this->type = $type;
		$this->nativeType = $nativeType;
		$this->size = $size;
		$this->precision = $precision;
		$this->scale = $scale;
		$this->isNullable = $is_nullable;
		$this->defaultValue = $default;
		$this->isAutoIncrement = $is_auto_increment;
		$this->vendorSpecificInfo = $vendorInfo;
	}

	/**
	 * This "magic" method is invoked upon serialize().
	 * Because the Info class hierarchy is recursive, we must handle
	 * the serialization and unserialization of this object.
	 * @return array The class variables that should be serialized (all must be public!).
	 */
	function __sleep() {
		return array('name', 'type', 'nativeType', 'size', 'precision', 'isNullable', 'defaultValue');
	}

	/**
	 * Get column name.
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Get column type.
	 * @return int
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * Gets the native type name.
	 * @return string
	 */
	function getNativeType() {
		return $this->nativeType;
	}

	/**
	 * Get column size.
	 * @return int
	 */
	function getSize() {
		return $this->size;
	}

	/**
	 * Get column precision.
	 * @return int
	 */
	function getPrecision() {
		return $this->precision;
	}

	/**
	 * Get column scale.
	 * Scale refers to number of digits after the decimal.  Sometimes this is referred
	 * to as precision, but precision is the total number of digits (i.e. length).
	 * @return int
	 */
	function getScale() {
		return $this->scale;
	}

	/**
	 * Get the default value.
	 * @return mixed
	 */
	function getDefaultValue() {
		return $this->defaultValue;
	}

	/**
	 * Is column nullable?
	 * @return boolean
	 */
	function isNullable() {
		return $this->isNullable;
	}

	/**
	 * Is column of autoincrement type?
	 * @return boolean
	 */
	function isAutoIncrement() {
		return $this->isAutoIncrement === true;
	}

	/**
	 * Get vendor specific optional information for this column.
	 * @return array vendorSpecificInfo[]
	 */
	function getVendorSpecificInfo() {
		return $this->vendorSpecificInfo;
	}

	/**
	 * @return string
	 */
	function toString() {
		return $this->name;
	}

	/**
	 * Get parent table.
	 * @return TableInfo
	 */
	function getTable() {
		return $this->table;
	}

}
