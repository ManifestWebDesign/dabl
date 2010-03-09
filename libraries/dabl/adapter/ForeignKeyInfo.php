<?php

/**
 * Represents a foreign key.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.9 $
 * @package   creole.metadata
 */
class ForeignKeyInfo {

	private $name;
	private $references = array();
	
	/**
	 * Additional and optional vendor specific information.
	 * @var vendorSpecificInfo
	 */
	protected $vendorSpecificInfo = array();


	const NONE			= "";			// No "ON [ DELETE | UPDATE]" behaviour specified.
	const NOACTION		= "NO ACTION";
	const CASCADE		= "CASCADE";
	const RESTRICT		= "RESTRICT";
	const SETDEFAULT	= "SET DEFAULT";
	const SETNULL		= "SET NULL";

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
	 * Adds a foreign-local mapping.
	 * @param ColumnInfo $local
	 * @param ColumnInfo $foreign
	 */
	function addReference(ColumnInfo $local, ColumnInfo $foreign, $onDelete = self::NONE, $onUpdate = self::NONE) {
		$this->references[] = array($local, $foreign, $onDelete, $onUpdate);
	}

	/**
	 * Gets the local-foreign column mapping.
	 * @return array array( [0] => array([0] => local ColumnInfo object, [1] => foreign ColumnInfo object, [2] => onDelete, [3] => onUpdate) )
	 */
	function getReferences() {
		return $this->references;
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
