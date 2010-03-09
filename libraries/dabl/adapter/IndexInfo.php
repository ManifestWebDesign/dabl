<?php

/**
 * Represents an index.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.7 $
 * @package   creole.metadata
 */
class IndexInfo {

	/** name of the index */
	private $name;

	/** columns in this index */
	private $columns = array();

	/** uniqueness flag */
	private $isUnique = false;

	/** additional vendor specific information */
	private $vendorSpecificInfo = array();

	function __construct($name, $isUnique = false, $vendorInfo = array()){
		$this->name = $name;
		$this->isUnique = $isUnique;
		$this->vendorSpecificInfo = $vendorInfo;
	}

	function isUnique(){
		return $this->isUnique;
	}

	function getName(){
		return $this->name;
	}

	/**
	 * Get vendor specific optional information for this index.
	 * @return array vendorSpecificInfo[]
	 */
	function getVendorSpecificInfo(){
		return $this->vendorSpecificInfo;
	}

	function addColumn($column){
		$this->columns[] = $column;
	}

	function getColumns(){
		return $this->columns;
	}

	function toString(){
		return $this->name;
	}

}
