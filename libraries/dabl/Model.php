<?php

/**
 * @package dabl
 */
abstract class Model {
	const COLUMN_TYPE_CHAR = 'CHAR';
	const COLUMN_TYPE_VARCHAR = 'VARCHAR';
	const COLUMN_TYPE_LONGVARCHAR = 'LONGVARCHAR';
	const COLUMN_TYPE_CLOB = 'CLOB';
	const COLUMN_TYPE_NUMERIC = 'NUMERIC';
	const COLUMN_TYPE_DECIMAL = 'DECIMAL';
	const COLUMN_TYPE_TINYINT = 'TINYINT';
	const COLUMN_TYPE_SMALLINT = 'SMALLINT';
	const COLUMN_TYPE_INTEGER = 'INTEGER';
	const COLUMN_TYPE_BIGINT = 'BIGINT';
	const COLUMN_TYPE_REAL = 'REAL';
	const COLUMN_TYPE_FLOAT = 'FLOAT';
	const COLUMN_TYPE_DOUBLE = 'DOUBLE';
	const COLUMN_TYPE_BINARY = 'BINARY';
	const COLUMN_TYPE_VARBINARY = 'VARBINARY';
	const COLUMN_TYPE_LONGVARBINARY = 'LONGVARBINARY';
	const COLUMN_TYPE_BLOB = 'BLOB';
	const COLUMN_TYPE_DATE = 'DATE';
	const COLUMN_TYPE_TIME = 'TIME';
	const COLUMN_TYPE_TIMESTAMP = 'TIMESTAMP';
	const COLUMN_TYPE_BU_DATE = 'BU_DATE';
	const COLUMN_TYPE_BU_TIMESTAMP = 'BU_TIMESTAMP';
	const COLUMN_TYPE_BOOLEAN = 'BOOLEAN';

	private static $TEXT_TYPES = array(
		self::COLUMN_TYPE_CHAR,
		self::COLUMN_TYPE_VARCHAR,
		self::COLUMN_TYPE_LONGVARCHAR,
		self::COLUMN_TYPE_CLOB,
		self::COLUMN_TYPE_DATE,
		self::COLUMN_TYPE_TIME,
		self::COLUMN_TYPE_TIMESTAMP,
		self::COLUMN_TYPE_BU_DATE,
		self::COLUMN_TYPE_BU_TIMESTAMP
	);

	private static $INTEGER_TYPES = array(
		self::COLUMN_TYPE_SMALLINT,
		self::COLUMN_TYPE_TINYINT,
		self::COLUMN_TYPE_INTEGER,
		self::COLUMN_TYPE_BIGINT,
		self::COLUMN_TYPE_BOOLEAN
	);

	private static $LOB_TYPES = array(
		self::COLUMN_TYPE_VARBINARY,
		self::COLUMN_TYPE_LONGVARBINARY,
		self::COLUMN_TYPE_BLOB
	);

	private static $TEMPORAL_TYPES = array(
		self::COLUMN_TYPE_DATE,
		self::COLUMN_TYPE_TIME,
		self::COLUMN_TYPE_TIMESTAMP,
		self::COLUMN_TYPE_BU_DATE,
		self::COLUMN_TYPE_BU_TIMESTAMP
	);

	private static $NUMERIC_TYPES = array(
		self::COLUMN_TYPE_SMALLINT,
		self::COLUMN_TYPE_TINYINT,
		self::COLUMN_TYPE_INTEGER,
		self::COLUMN_TYPE_BIGINT,
		self::COLUMN_TYPE_FLOAT,
		self::COLUMN_TYPE_DOUBLE,
		self::COLUMN_TYPE_NUMERIC,
		self::COLUMN_TYPE_DECIMAL,
		self::COLUMN_TYPE_REAL
	);

	public function __toString() {
		return get_class($this) . implode('-', $this->getPrimaryKeyValues());
	}

	/**
	 * Magic get
	 */
	function __get($name) {
		if ($this->hasColumn($name)) {
			return $this->{'get' . $name}();
		}
	}

	/**
	 * Magic set
	 */
	function __set($name, $value) {
		if ($this->hasColumn($name)) {
			$this->{'set' . $name}($value);
		}
	}

	/**
	 * Whether passed type is a temporal (date/time/timestamp) type.
	 *
	 * @param string $type Propel type
	 * @return boolean
	 */
	static function isTemporalType($type) {
		return in_array($type, self::$TEMPORAL_TYPES);
	}

	/**
	 * Returns true if values for the type need to be quoted.
	 *
	 * @param string $type The Propel type to check.
	 * @return boolean True if values for the type need to be quoted.
	 */
	static function isTextType($type) {
		return in_array($type, self::$TEXT_TYPES);
	}

	/**
	 * Returns true if values for the type are numeric.
	 *
	 * @param string $type The Propel type to check.
	 * @return boolean True if values for the type need to be quoted.
	 */
	static function isNumericType($type) {
		return in_array($type, self::$NUMERIC_TYPES);
	}

	/**
	 * Returns true if values for the type are integer.
	 *
	 * @param string $type
	 * @return boolean
	 */
	static function isIntegerType($type) {
		return in_array($type, self::$INTEGER_TYPES);
	}

	/**
	 * Returns true if type is a LOB type (i.e. would be handled by Blob/Clob class).
	 * @param string $type Propel type to check.
	 * @return boolean
	 */
	static function isLobType($type) {
		return in_array($type, self::$LOB_TYPES);
	}

	const MAX_INSTANCE_POOL_SIZE = 400;

	/**
	 * Array to contain names of modified columns
	 */
	protected $_modifiedColumns = array();

	/**
	 * Whether or not to cache results in the internal object cache
	 */
	protected $_cacheResults = true;

	/**
	 * Whether or not to save dates as formatted date/time strings
	 */
	protected $_formatDates = true;

	/**
	 * Whether or not this is a new object
	 */
	protected $_isNew = true;

	/**
	 * Errors from the validate() step of saving
	 */
	protected $_validationErrors = array();

	/**
	 * Returns an array of objects of class $class from
	 * the rows of a PDOStatement(query result)
	 *
	 * @param PDOStatement $result
	 * @param string $class_name name of class to create
	 * @return Model[]
	 */
	static function fromResult(PDOStatement $result, $class_name, $use_pool = true) {
		if (!$class_name) {
			throw new RuntimeException('No class name given');
		}

		$objects = array();
		if (is_array($class_name)) {
			$class_names = $class_name;
			unset($class_name);
			while ($values = $result->fetch(PDO::FETCH_NUM)) {
				unset($main_object);
				$startcol = 0;
				foreach ($class_names as $key => $class_name) {
					$object = new $class_name;
					if (!$object->fromNumericResultArray($values, $startcol)) {
						continue;
					}

					if (
						$use_pool
						&& ($pk = $object->getPrimaryKey())
						&& ($pool_object = $object->retrieveFromPool($object->{'get' . $pk}()))
					) {
						$object = $pool_object;
					}

					if ($use_pool) {
						$object->insertIntoPool($object);
					}

					if (!isset($main_object)) {
						$main_object = $objects[] = $object;
					} else {
						if (method_exists($main_object, 'set' . $class_name)) {
							$main_object->{'set' . $class_name}($object);
						} else {
							$main_object->{$class_name} = $object;
						}
					}
				}
			}
		} else {
			// PDO::FETCH_PROPS_LATE is required to call the ctor after hydrating the fields
			$flags = PDO::FETCH_CLASS;
			if (defined('PDO::FETCH_PROPS_LATE')) {
				$flags |= PDO::FETCH_PROPS_LATE;
			}
			$result->setFetchMode($flags, $class_name);
			while (false !== ($object = $result->fetch())) {
				if (
					$use_pool
					&& (!empty($pk) || ($pk = $object->getPrimaryKey()))
					&& ($pool_object = $object->retrieveFromPool($object->{'get' . $pk}()))
				) {
					$object = $pool_object;
				} else {
					$object->castInts();
					$object->setNew(false);
				}

				$objects[] = $object;

				if ($use_pool) {
					$object->insertIntoPool($object);
				}
			}
		}
		return $objects;
	}

	/**
	 * Loads values from the array returned by PDOStatement::fetch(PDO::FETCH_NUM)
	 * @param array $values
	 * @param int $startcol
	 */
	function fromNumericResultArray($values, &$startcol) {
		foreach ($this->getColumnNames() as $column_name) {
			$this->{$column_name} = $values[$startcol++];
		}
		if ($this->getPrimaryKeys() && !$this->hasPrimaryKeyValues()) {
			return false;
		}
		$this->castInts();
		$this->setNew(false);
		return true;
	}

	/**
	 * Loads values from the array returned by PDOStatement::fetch(PDO::FETCH_ASSOC)
	 * @param array $values
	 */
	function fromAssociativeResultArray($values) {
		foreach ($this->getColumnNames() as $column_name) {
			if (array_key_exists($column_name, $values)) {
				$this->{$column_name} = $values[$column_name];
			}
		}
		if ($this->getPrimaryKeys() && !$this->hasPrimaryKeyValues()) {
			return false;
		}
		$this->castInts();
		$this->setNew(false);
		return true;
	}

	/**
	 * Creates new instance of self and with the same values as $this, except
	 * the primary key value is cleared
	 * @return Model
	 */
	function copy() {
		$class = get_class($this);
		$new_object = new $class;
		$new_object->fromArray($this->toArray());

		foreach($this->getPrimaryKeys() as $pk){
			$new_object->{'set' . $pk}(null);
		}
		return $new_object;
	}

	/**
	 * Checks whether any of the columns have been modified from the database values.
	 * @return bool
	 */
	function isModified() {
		return (bool) $this->getModifiedColumns();
	}

	/**
	 * Checks whether the given column is in the modified array
	 * @return bool
	 */
	function isColumnModified($column_name) {
		return array_key_exists(strtolower($column_name), array_map('strtolower', $this->_modifiedColumns));
	}

	/**
	 * Returns an array of the names of modified columns
	 * @return array
	 */
	function getModifiedColumns() {
		return array_values($this->_modifiedColumns);
	}

	/**
	 * Sets the value of a property/column
	 * @param string $column_name
	 * @param mixed $value
	 * @param string $column_type
	 * @return Model
	 */
	function setColumnValue($column_name, $value, $column_type = null) {
		if (null === $column_type) {
			$column_type = $this->getColumnType($column_name);
		}

		if ($column_type == self::COLUMN_TYPE_BOOLEAN) {
			if ($value === true || $value === 1 || $value === '1' || strtolower($value) === 'on' || strtolower($value) === 'true') {
				$value = 1;
			} elseif ($value === false || $value === 0 || $value === '0' || strtolower($value) === 'off' || strtolower($value) === 'false') {
				$value = 0;
			} elseif ($value === '' || $value === null) {
				$value = null;
			} else {
				throw new InvalidArgumentException($value . ' is not a valid boolean value');
			}
		} else {
			$temporal = self::isTemporalType($column_type);
			$numeric = self::isNumericType($column_type);

			if ($numeric || $temporal) {
				if (is_string($value)) {
					$value = trim($value);
				}
				if ('' === $value) {
					$value = null;
				} elseif (null !== $value) {
					if ($numeric) {
						if (is_bool($value)) {
							$value = $value ? 1 : 0;
						} elseif (self::isIntegerType($column_type)) {
							// validate and cast
							if (!is_int($value)) {
								$int_val = intval($value);
								if ((string) $int_val != (string) $value) {
									throw new InvalidArgumentException($value . ' is not a valid integer or it is too large');
								}
								$value = $int_val;
							}
						} else {
							// only validates, doesn't cast...yet
							$float_val = floatval($value);
							if ((string) $float_val != (string) $value) {
								throw new InvalidArgumentException($value . ' is not a valid float or it is too large');
							}
						}
					}
					if ($this->_formatDates && $temporal) {
						$value = self::coerceTemporalValue($value, $column_type, $this->getConnection());
					}
				}
			}
		}

		if ($this->$column_name !== $value) {
			$this->_modifiedColumns[$column_name] = $column_name;
			$this->$column_name = $value;
		}
		return $this;
	}

	static function coerceTemporalValue($value, $column_type, DABLPDO $conn) {
		if (is_array($value)) {
			foreach ($value as &$v) {
				$v = self::coerceTemporalValue($v, $column_type, $conn);
			}
			return $value;
		}
		switch ($column_type) {
			case Model::COLUMN_TYPE_TIMESTAMP:
				$formatter = $conn->getTimestampFormatter();
				break;
			case Model::COLUMN_TYPE_DATE:
				$formatter = $conn->getDateFormatter();
				break;
			case Model::COLUMN_TYPE_TIME:
				$formatter = $conn->getTimeFormatter();
				break;
		}
		$timestamp = is_int($value) ? $value : strtotime($value);
		if (false === $timestamp) {
			throw new InvalidArgumentException('Unable to parse date: ' . $value);
		}
		return date($formatter, $timestamp);
	}

	/**
	 * Clears the array of modified column names
	 * @return Model
	 */
	function resetModified() {
		$this->_modifiedColumns = array();
		return $this;
	}

	/**
	 * Populates $this with the values of an associative Array.
	 * Array keys must match column names to be used.
	 * @param array $array
	 * @return Model
	 */
	function fromArray($array) {
		$columns = $this->getColumnNames();
		foreach ($array as $column => &$v) {
			if (is_string($column) === false || in_array($column, $columns) === false)
				continue;
			$this->{'set' . $column}($v);
		}
		return $this;
	}

	/**
	 * Returns an associative Array with the values of $this.
	 * Array keys match column names.
	 * @return array
	 */
	function toArray() {
		$array = array();
		foreach ($this->getColumnNames() as $column)
			$array[$column] = $this->{'get' . $column}();
		return $array;
	}

	/**
	 * Sets whether to use cached results for foreign keys or to execute
	 * the query each time, even if it hasn't changed.
	 * @param bool $value[optional]
	 * @return Model
	 */
	function setCacheResults($value=true) {
		$this->_cacheResults = (bool) $value;
		return $this;
	}

	/**
	 * Returns true if this object is set to cache results
	 * @return bool
	 */
	function getCacheResults() {
		return (bool) $this->_cacheResults;
	}

	/**
	 * Returns true if this table has primary keys and if all of the primary values are not null
	 * @return bool
	 */
	function hasPrimaryKeyValues() {
		$pks = $this->getPrimaryKeys();
		if (!$pks)
			return false;

		foreach ($pks as &$pk)
			if ($this->$pk === null)
				return false;
		return true;
	}

	/**
	 * Returns an array of all primary key values.
	 *
	 * @return mixed[]
	 */
	function getPrimaryKeyValues() {
		$arr = array();
		$pks = $this->getPrimaryKeys();

		foreach ($pks as &$pk) {
			$arr[] = $this->{"get$pk"}();
		}

		return $arr;
	}

	/**
	 * Returns true if the column values validate.
	 * @return bool
	 */
	function validate() {
		$this->_validationErrors = array();
		return true;
	}

	/**
	 * See $this->validate()
	 * @return array Array of errors that occured when validating object
	 */
	function getValidationErrors() {
		return $this->_validationErrors;
	}

	/**
	 * Creates and executess DELETE Query for this object
	 * Deletes any database rows with a primary key(s) that match $this
	 * NOTE/BUG: If you alter pre-existing primary key(s) before deleting, then you will be
	 * deleting based on the new primary key(s) and not the originals,
	 * leaving the original row unchanged(if it exists).  Also, since NULL isn't an accurate way
	 * to look up a row, I return if one of the primary keys is null.
	 * @return int number of records deleted
	 */
	function delete() {
		$pks = $this->getPrimaryKeys();
		if (!$pks) {
			throw new RuntimeException('This table has no primary keys');
		}
		$q = new Query();
		foreach ($pks as &$pk) {
			if ($this->$pk === null) {
				throw new RuntimeException('Cannot delete using NULL primary key.');
			}
			$q->addAnd($pk, $this->$pk);
		}
		$q->setTable($this->getTableName());
		$result = $this->doDelete($q, false);
		$this->removeFromPool($this);
		return $result;
	}

	/**
	 * Saves the values of $this to a row in the database.  If there is an
	 * existing row with a primary key(s) that matches $this, the row will
	 * be updated.  Otherwise a new row will be inserted.  If there is only
	 * 1 primary key, it will be set using the last_insert_id() function.
	 * NOTE: If you alter pre-existing primary key(s) before saving, then you will be
	 * updating/inserting based on the new primary key(s) and not the originals,
	 * leaving the original row unchanged(if it exists).
	 * @todo find a way to solve the above issue
	 * @return int number of records inserted or updated
	 */
	function save() {
		if (!$this->validate()) {
			throw new RuntimeException('Cannot save ' . get_class($this) . ' with validation errors: ' . implode(', ', $this->getValidationErrors()));
		}

		if ($this->isNew() && $this->hasColumn('Created') && !$this->isColumnModified('Created')) {
			$this->setCreated(CURRENT_TIMESTAMP);
		}

		if (($this->isNew() || $this->isModified()) && $this->hasColumn('Updated') && !$this->isColumnModified('Updated')) {
			$this->setUpdated(CURRENT_TIMESTAMP);
		}

		if ($this->isNew()) {
			return $this->insert();
		} else {
			return $this->update();
		}
	}

	function archive() {
		if (!$this->hasColumn('Archived')) {
			throw new RuntimeException('Cannot call archive on models without "Archived" column');
		}

		if (null !== $this->getArchived()) {
			throw new RuntimeException('This ' . get_class($this) . ' is already archived.');
		}

		$this->setArchived(CURRENT_TIMESTAMP);

		return $this->save();
	}

	/**
	 * Returns true if this has not yet been saved to the database
	 * @return bool
	 */
	function isNew() {
		return (bool) $this->_isNew;
	}

	/**
	 * Indicate whether this object has been saved to the database
	 * @param bool $bool
	 * @return Model
	 */
	function setNew($bool) {
		$this->_isNew = (bool) $bool;
		return $this;
	}

	/**
	 * Creates and executes INSERT query string for this object
	 * @return int
	 */
	protected function insert() {
		$conn = $this->getConnection();
		$pk = $this->getPrimaryKey();

		$fields = array();
		$values = array();
		$placeholders = array();
		foreach ($this->getColumnNames() as $column) {
			$value = $this->$column;
			if ($value === null && !$this->isColumnModified($column))
				continue;
			$fields[] = $conn->quoteIdentifier($column);
			$values[] = $value;
			$placeholders[] = '?';
		}

		$quoted_table = $conn->quoteIdentifier($this->getTableName());
		$query_s = 'INSERT INTO ' . $quoted_table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ') ';

		if ($pk && $this->isAutoIncrement() && $conn instanceof DBPostgres) {
			$query_s .= ' RETURNING ' . $conn->quoteIdentifier($pk);
		}

		$statement = new QueryStatement($conn);
		$statement->setString($query_s);
		$statement->setParams($values);

		$result = $statement->bindAndExecute();
		$count = $result->rowCount();

		if ($pk && $this->isAutoIncrement()) {
			$id = null;
			if ($conn instanceof DBPostgres) {
				$id = $result->fetchColumn(0);
			} elseif ($conn->isGetIdAfterInsert()) {
				$id = $conn->lastInsertId();
			}
			if (null !== $id) {
				$this->{"set$pk"}($id);
			}
		}

		$this->resetModified();
		$this->setNew(false);

		$this->insertIntoPool($this);

		return $count;
	}

	/**
	 * Creates and executes UPDATE query string for this object.  Returns
	 * the number of affected rows.
	 * @return Int
	 */
	protected function update() {
		if (!$this->getPrimaryKeys()) {
			throw new RuntimeException('This table has no primary keys');
		}

		$column_values = array();
		foreach ($this->getModifiedColumns() as $column) {
			$column_values[$column] = $this->$column;
		}

		// If array is empty there is nothing to update
		if (empty($column_values)) {
			return 0;
		}

		$q = new Query;

		foreach ($this->getPrimaryKeys() as $pk) {
			if ($this->$pk === null) {
				throw new RuntimeException('Cannot update with NULL primary key.');
			}
			$q->add($pk, $this->$pk);
		}

		$row_count = $this->doUpdate($column_values, $q);
		$this->resetModified();
		return $row_count;
	}

	/**
	 * Cast returned values from the database into integers where appropriate.
	 */
	abstract function castInts();

	/**
	 * @param string $foreign_table
	 * @param string $foreign_column
	 * @param Query $q
	 * @return Query
	 */
	protected function getForeignObjectsQuery($foreign_table, $foreign_column, $local_column, Query $q = null) {
		$value = $this->{"get$local_column"}();
		if (null === $value) {
			throw new RuntimeException('NULL cannot be used to match keys.');
		}
		$conn = $this->getConnection();
		if ($q) {
			$q = clone $q;
			$alias = $q->getAlias();
			if ($alias && $foreign_table == $q->getTable()) {
				$foreign_column = "$alias.$foreign_column";
			}
		} else {
			$q = new Query;
		}
		$q->add($foreign_column, $value);
		return $q;
	}

}
