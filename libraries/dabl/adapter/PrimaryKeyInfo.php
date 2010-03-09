<?php

/**
 * Represents a PrimaryKey
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.6 $
 * @package   creole.metadata
 */
class PrimaryKeyInfo {

	/** name of the primary key */
	private $name;

	/** columns in the primary key */
	private $columns = array();

	/** additional vendor specific information */
	private $vendorSpecificInfo = array();

	/**
	 * @param string $name The name of the foreign key.
	 */
	function __construct($name, $vendorInfo = array()) {
		$this->name = $name;
		$this->vendorSpecificInfo = $vendorInfo;
	}

	/**
	 * Get foreign key name.
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * @param Column $column
	 * @return void
	 */
	function addColumn($column) {
		$this->columns[] = $column;
	}

	/**
	 * @return array Column[]
	 */
	function getColumns() {
		return $this->columns;
	}

	/**
	 * Get vendor specific optional information for this primary key.
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
}
