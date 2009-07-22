<?php

/**
 * Last Modified July 3rd 2009
 */

abstract class BaseTable {

	/**
	 * Array to contain names of modified columns
	 */
	protected $_modifiedColumns = array();

	protected $_cacheResults = true;

	protected $_isNew = true;

	static function getPrimaryKey(){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	/**
	 * @return DBAdapter
	 */
	static function getConnection(){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	static function getColumnNames(){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	static function getTableName(){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	static function getPrimaryKeys(){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	static function doDelete(Query $q){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	function __clone(){
		$class = $this->getTableName();
		$new = new $class;
		$newValues = $this->toArray();
		$this->fromArray($new->toArray());
		$this->fromArray($newValues);
		$this->setNew(true);
	}

	/**
	 * Checks whether the given column is in the modified array
	 * @return Bool
	 */
	function isColumnModified($columnName){
		if(in_array($columnName, $this->_modifiedColumns))
			return true;
		return false;
	}

	function getModifiedColumns(){
		return $this->_modifiedColumns ? $this->_modifiedColumns : array();
	}

	/**
	 * Clears the array of modified column names
	 */
	function resetModified(){
		$this->_modifiedColumns = array();
	}

	/**
	 * Populates $this with the values of an associative Array.
	 * Array keys must match column names to be used.
	 */
	function fromArray($array){
		foreach($this->getColumnNames() as $column){
			if(!array_key_exists($column, $array))
				continue;
			$method = "set$column";
			$this->$method($array[$column]);
		}
	}

	/**
	 * Returns an associative Array with the values of $this.
	 * Array keys match column names.
	 * @return Array of BaseTable Objects
	 */
	function toArray(){
		$array = array();
		foreach($this->getColumnNames() as $column){
			$method = "get$column";
			$array[$column] = $this->$method();
		}
		return $array;
	}

	/**
	 * Sets whether to use cached results for foreign keys or to execute
	 * the query each time, even if it hasn't changed.
	 * @param $value Bool[optional]
	 */
	function setCacheResults($value=true){
		$this->_cacheResults = (bool)$value;
	}

	/**
	 * Returns true if this object is set to cache results
	 * @return Bool
	 */
	function getCacheResults(){
		return (bool)$this->_cacheResults;
	}

	/**
	 * Returns true if this table has primary keys and if all of the primary values are not null
	 * @return Bool
	 */
	function hasPrimaryKeyValues(){
		$pks = $this->getPrimaryKeys();
		if(!$pks) return false;

		foreach($pks as $pk)
			if($this->$pk===null)
				return false;
		return true;
	}

	/**
	 * Creates and executess DELETE Query for this object
	 * Deletes any database rows with a primary key(s) that match $this
	 * NOTE/BUG: If you alter pre-existing primary key(s) before deleting, then you will be
	 * deleting based on the new primary key(s) and not the originals,
	 * leaving the original row unchanged(if it exists).  Also, since NULL isn't an accurate way
	 * to look up a row, I return if one of the primary keys is null.
	 */
	function delete(){
		$conn = $this->getConnection();
		$pks = $this->getPrimaryKeys();
		if(!$pks)throw new Exception("This table has no primary keys");
		$q = new Query();
		foreach($pks as $pk){
			if($this->$pk===null)
				throw new Exception("Cannot delete using NULL primary key.");
			$q->addAnd($conn->quoteIdentifier($pk), $this->$pk);
		}
		$q->setLimit(1);
		$q->setTable($this->getTableName());
		return $this->doDelete($q);
	}

	/**
	 * Saves the values of $this to a row in the database.  If there is an
	 * existing row with a primary key(s) that matches $this, the row will
	 * be updated.  Otherwise a new row will be inserted.  If there is only
	 * 1 primary key, it will be set using the last_insert_id() function.
	 * NOTE/BUG: If you alter pre-existing primary key(s) before saving, then you will be
	 * updating/inserting based on the new primary key(s) and not the originals,
	 * leaving the original row unchanged(if it exists).
	 * @todo find a way to solve the above issue
	 */
	public function save(){
		if($this->getPrimaryKeys()){
			if($this->isNew())
				return $this->insert();
			return $this->update();
		}
		return $this->replace();
	}

	/**
	 * Returns true if this has not yet been saved to the database
	 * @return Bool
	 */
	function isNew(){
		return (bool)$this->_isNew;
	}

	/**
	 * Indicate whether this object has been saved to the database
	 * @param Bool $bool
	 */
	function setNew($bool){
		$this->_isNew = (bool)$bool;
	}

	/**
	 * Creates and executes INSERT query string for this object
	 * @return
	 */
	protected function insert(){
		$conn = $this->getConnection();
		$quotedTable = $conn->quoteIdentifier($this->getTableName());

		$fields = array();
		$values = array();
		foreach($this->getColumnNames() as $column){
			if(!$this->isColumnModified($column))
				continue;
			$fields[] = $conn->quoteIdentifier($column);
			$values[] = $conn->checkInput($this->$column);
	//		$values[] = $this->$column;
	//		$placeholders[] = '?';
		}

		$queryString = "INSERT INTO $quotedTable (".implode(", ", $fields).") VALUES (".implode(', ', $values).") ";
	//	$queryString = "INSERT INTO $quotedTable (".implode(", ", $fields).") VALUES (".implode(', ', $placeholders).") ";

		try{
			$count = $conn->exec($queryString);
	//		$stmnt = $conn->prepare($queryString);
	//		$count = $stmnt->execute($values);

			if($this->getPrimaryKey()){
				$pk = $this->getPrimaryKey();
				$id = $conn->lastInsertId();
				if($id)$this->$pk = $id;
			}
			$this->resetModified();
			$this->setNew(false);
		}
		catch(PDOException $e){
			throw new PDOException($e->getMessage().$queryString);
		}
	}

	/**
	 * Creates and executes REPLACE query string for this object.  Returns
	 * the number of affected rows.
	 * @return Int
	 */
	protected function replace(){
		$conn = $this->getConnection();
		$quotedTable = $conn->quoteIdentifier($this->getTableName());

		$fields = array();
		$values = array();
		foreach($this->getColumnNames() as $column){
			$fields[] = $conn->quoteIdentifier($column);
			$values[] = $conn->checkInput($this->$column);
	//		$values[] = $this->$column;
	//		$placeholders[] = '?';
		}
		$queryString = "REPLACE INTO $quotedTable (".implode(", ", $fields).") VALUES (".implode(', ', $values).") ";
	//	$queryString = "REPLACE INTO $quotedTable (".implode(", ", $fields).") VALUES (".implode(', ', $placeholders).") ";

		try{
			$count = $conn->exec($queryString);
	//		$stmnt = $conn->prepare($queryString);
	//		$count = $stmnt->execute($values);

			if($this->getPrimaryKey()){
				$pk = $this->getPrimaryKey();
				$id = $conn->lastInsertId();
				if($id)$this->$pk = $id;
			}
			$this->resetModified();
			$this->setNew(false);
			return $count;
		}
		catch(PDOException $e){
			throw new PDOException($e->getMessage().$queryString);
		}
	}

	/**
	 * Creates and executes UPDATE query string for this object.  Returns
	 * the number of affected rows.
	 * @return Int
	 */
	protected function update(){
		$conn = $this->getConnection();
		$quotedTable = $conn->quoteIdentifier($this->getTableName());

		if(!$this->getPrimaryKeys())
			throw new Exception("This table has no primary keys");

		$updateValues = array();
		foreach($this->getColumnNames() as $column){
			if(!$this->isColumnModified($column))
				continue;
			$updateValues[] = $conn->quoteIdentifier($column)."=".$conn->checkInput($this->$column);
		}

		//If array is empty there is nothing to update
		if(!$updateValues)
			return 0;

		$pkWhere = array();
		foreach($this->getPrimaryKeys() as $pk){
			if($this->$pk===null)
				throw new Exception("Cannot update with NULL primary key.");
			$pkWhere[] = "$pk=".$conn->checkInput($this->$pk);
		}

		$queryString = "UPDATE $quotedTable SET ".implode(", ", $updateValues)." WHERE ".implode(" AND ", $pkWhere);

		try{
			$count = $conn->exec($queryString);
			$this->resetModified();
			return $count;
		}
		catch(PDOException $e){
			throw new PDOException($e->getMessage().$queryString);
		}
	}

}

?>