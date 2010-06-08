<?php

abstract class BaseModel {

	/**
	 * Array to contain names of modified columns
	 */
	protected $_modifiedColumns = array();

	protected $_cacheResults = true;

	protected $_formatDates = true;

	protected $_isNew = true;

	protected $_validationErrors = array();

	protected static $_instancePool = array();

	static function getPrimaryKey(){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	/**
	 * @return DABLPDO
	 */
	static function getConnection(){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	static function getColumnNames(){
		throw new Exception("This should be replaced by an extension of this class.");
	}

	static function hasColumn(){
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

	/**
	 * Creates new instance of $this and
	 * @return BaseModel
	 */
	function copy(){
		$class = get_class($this);
		$new_object = new $class;
		$new_object->fromArray($this->toArray());

		if($this->getPrimaryKey()){
			$pk = $this->getPrimaryKey();
			$set_pk_method = "set$pk";
			$new_object->$set_pk_method(null);
		}
		return $new_object;
	}

	function isModified(){
		return (bool)$this->getModifiedColumns();
	}

	/**
	 * Checks whether the given column is in the modified array
	 * @return Bool
	 */
	function isColumnModified($columnName){
		return in_array(strtolower($columnName), array_map('strtolower', $this->_modifiedColumns));
	}

	/**
	 * Returns an array of the names of modified columns
	 * @return array
	 */
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
		$columns = $this->getColumnNames();
       	foreach($array as $column => $value){
			if(in_array($column, $columns) === false) continue;
			$this->{'set'.$column}($value);
		}
	}

	/**
	 * Returns an associative Array with the values of $this.
	 * Array keys match column names.
	 * @return Array of BaseTable Objects
	 */
	function toArray(){
		$array = array();
		$column_names = $this->getColumnNames();
		while($column = array_shift($column_names))
			$array[$column] = $this->{'get'.$column}();
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
	 * Returns true if the column values validate.
	 * @return bool
	 */
	function validate(){
		$this->_validationErrors = array();
		return true;
	}

	/**
	 * See $this->validate()
	 * @return array Array of errors that occured when validating object
	 */
	function getValidationErrors(){
		return $this->_validationErrors;
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
		$result = $this->doDelete($q);
		$pk = $this->getPrimaryKey();
		if($result && $pk)
			unset(self::$_instancePool[$this->{"get$pk"}()]);
		return $result;
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
	function save(){
		if(!$this->validate())
			return 0;

		if($this->hasColumn('Created') && $this->hasColumn('Updated')){
			if($this->isNew() && !$this->isColumnModified('Created'))
				$this->setCreated(CURRENT_TIME);
			if(!$this->isColumnModified('Updated'))
				$this->setUpdated(CURRENT_TIME);
		}

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
	 * @return int
	 */
	protected function insert(){
		$conn = $this->getConnection();
		$pk = $this->getPrimaryKey();
		
		$fields = array();
		$values = array();
		$placeholders = array();
		foreach($this->getColumnNames() as $column){
			$value = $this->$column;
			if($value===null && !$this->isColumnModified($column))
				continue;
			$fields[] = $conn->quoteIdentifier($column);
			$values[] = $value;
			$placeholders[] = '?';
		}

		$quotedTable = $conn->quoteIdentifier($this->getTableName());
		$queryString = "INSERT INTO $quotedTable (".implode(", ", $fields).") VALUES (".implode(', ', $placeholders).") ";
		
		$statement = new QueryStatement($conn);
		$statement->setString($queryString);
		$statement->setParams($values);

		$result = $statement->bindAndExecute();
		$count = $result->rowCount();

		if($pk && $this->isAutoIncrement()){
			if($conn instanceof DBPostgres)
				$id = $conn->getId($this->getTableName(), $pk);
			elseif($conn->isGetIdAfterInsert())
				$id = $conn->lastInsertId();
			$this->{"set$pk"}($id);
		}
		$this->resetModified();
		$this->setNew(false);
		if($pk)
			self::$_instancePool[$this->{"get$pk"}()] = $this;
		return $count;
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
			$values[] = $this->$column;
			$placeholders[] = '?';
		}
		$queryString = "REPLACE INTO $quotedTable (".implode(", ", $fields).") VALUES (".implode(', ', $placeholders).") ";

		$statement = new QueryStatement($conn);
		$statement->setString($queryString);
		$statement->setParams($values);

		$result = $statement->bindAndExecute();
		$count = $result->rowCount();

		$this->resetModified();
		$this->setNew(false);
		return $count;
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
			throw new Exception('This table has no primary keys');

		$fields = array();
		$values = array();
		foreach($this->getModifiedColumns() as $column){
			$fields[] = $conn->quoteIdentifier($column).'=?';
			$values[] = $this->$column;
		}

		//If array is empty there is nothing to update
		if(!$fields) return 0;

		$pkWhere = array();
		foreach($this->getPrimaryKeys() as $pk){
			if($this->$pk===null)
				throw new Exception('Cannot update with NULL primary key.');
			$pkWhere[] = $conn->quoteIdentifier($pk).'=?';
			$values[] = $this->$pk;
		}

		$queryString = "UPDATE $quotedTable SET ".implode(", ", $fields)." WHERE ".implode(" AND ", $pkWhere);
		$statement = new QueryStatement($conn);
		$statement->setString($queryString);
		$statement->setParams($values);
		$result = $statement->bindAndExecute();

		$this->resetModified();
		return $result->rowCount();
	}

}
