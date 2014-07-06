<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

/**
 * @package dabl
 */
abstract class Model implements JsonSerializable {

	const COLUMN_TYPE_CHAR = 'CHAR';
	const COLUMN_TYPE_VARCHAR = 'VARCHAR';
	const COLUMN_TYPE_LONGVARCHAR = 'LONGVARCHAR';
	const COLUMN_TYPE_CLOB = 'CLOB';
	const COLUMN_TYPE_NUMERIC = 'NUMERIC';
	const COLUMN_TYPE_DECIMAL = 'DECIMAL';
	const COLUMN_TYPE_TINYINT = 'TINYINT';
	const COLUMN_TYPE_SMALLINT = 'SMALLINT';
	const COLUMN_TYPE_INTEGER = 'INTEGER';
	const COLUMN_TYPE_INTEGER_TIMESTAMP = 'INTEGER_TIMESTAMP';
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

	protected static $textTypes = array(
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

	protected static $integerTypes = array(
		self::COLUMN_TYPE_SMALLINT,
		self::COLUMN_TYPE_TINYINT,
		self::COLUMN_TYPE_INTEGER,
		self::COLUMN_TYPE_BIGINT,
		self::COLUMN_TYPE_BOOLEAN,
		self::COLUMN_TYPE_INTEGER_TIMESTAMP
	);

	protected static $lobTypes = array(
		self::COLUMN_TYPE_VARBINARY,
		self::COLUMN_TYPE_LONGVARBINARY,
		self::COLUMN_TYPE_BLOB
	);

	protected static $temportalTypes = array(
		self::COLUMN_TYPE_DATE,
		self::COLUMN_TYPE_TIME,
		self::COLUMN_TYPE_TIMESTAMP,
		self::COLUMN_TYPE_BU_DATE,
		self::COLUMN_TYPE_BU_TIMESTAMP,
		self::COLUMN_TYPE_INTEGER_TIMESTAMP
	);

	protected static $numericTypes = array(
		self::COLUMN_TYPE_SMALLINT,
		self::COLUMN_TYPE_TINYINT,
		self::COLUMN_TYPE_INTEGER,
		self::COLUMN_TYPE_BIGINT,
		self::COLUMN_TYPE_FLOAT,
		self::COLUMN_TYPE_DOUBLE,
		self::COLUMN_TYPE_NUMERIC,
		self::COLUMN_TYPE_DECIMAL,
		self::COLUMN_TYPE_REAL,
		self::COLUMN_TYPE_INTEGER_TIMESTAMP
	);

	/**
	 * The maximum size of the instance pool
	 */
	const MAX_INSTANCE_POOL_SIZE = 400;

	/**
	 * Name of the table
	 * @var string
	 */
	protected static $_tableName;

	/**
	 * Cache of objects retrieved from the database
	 * @var static[]
	 */
	protected static $_instancePool;

	protected static $_instancePoolCount = 0;

	protected static $_poolEnabled = true;

	/**
	 * Array of objects to batch insert
	 * @var static[]
	 */
	protected static $_insertBatch;

	/**
	 * Maximum size of the insert batch
	 * @var int
	 */
	protected static $_insertBatchSize = 500;

	/**
	 * Array of all primary keys
	 * @var string[]
	 */
	protected static $_primaryKeys;

	/**
	 * string name of the primary key column
	 * @var string
	 */
	protected static $_primaryKey;

	/**
	 * true if primary key is an auto-increment column
	 * @var bool
	 */
	protected static $_isAutoIncrement = false;

	/**
	 * array of all fully-qualified(table.column) columns
	 * @var string[]
	 */
	protected static $_columns;

	/**
	 * array of all column names
	 * @var string[]
	 */
	protected static $_columnNames;

	/**
	 * array of all column types
	 * @var string[]
	 */
	protected static $_columnTypes;

	/**
	 * Array to contain names of modified columns
	 * @var string[]
	 */
	protected $_modifiedColumns = array();

	/**
	 * Whether or not to cache results in the internal object cache
	 */
	protected $_cacheResults = true;

	/**
	 * Whether or not this is a new object
	 */
	protected $_isNew = true;

	/**
	 * Wether or not the object is out of sync with the databse
	 */
	protected $_isDirty = false;

	/**
	 * Errors from the validate() step of saving
	 */
	protected $_validationErrors = array();

	public function __toString() {
		return get_class($this) . implode('-', $this->getPrimaryKeyValues());
	}

	/**
	 * Magic get
	 */
	function __get($name) {
		$method = 'get' . $name;
		if (method_exists($this, $method)) {
			return $this->$method();
		}
	}

	/**
	 * Magic set
	 */
	function __set($name, $value) {
		$method = 'set' . $name;
		if (method_exists($this, $method)) {
			$this->$method($value);
		}
	}

	/**
	 * @return static

	 */
	static function create() {
		return new static();
	}

	/**
	 * Whether passed type is a temporal (date/time/timestamp) type.
	 *
	 * @param string $type Propel type
	 * @return boolean
	 */
	static function isTemporalType($type) {
		return in_array($type, static::$temportalTypes);
	}

	/**
	 * Returns true if values for the type need to be quoted.
	 *
	 * @param string $type The Propel type to check.
	 * @return boolean True if values for the type need to be quoted.
	 */
	static function isTextType($type) {
		return in_array($type, static::$textTypes);
	}

	/**
	 * Returns true if values for the type are numeric.
	 *
	 * @param string $type The Propel type to check.
	 * @return boolean True if values for the type need to be quoted.
	 */
	static function isNumericType($type) {
		return in_array($type, static::$numericTypes);
	}

	/**
	 * Returns true if values for the type are integer.
	 *
	 * @param string $type
	 * @return boolean
	 */
	static function isIntegerType($type) {
		return in_array($type, static::$integerTypes);
	}

	/**
	 * Returns true if type is a LOB type (i.e. would be handled by Blob/Clob class).
	 * @param string $type Propel type to check.
	 * @return boolean
	 */
	static function isLobType($type) {
		return in_array($type, static::$lobTypes);
	}

	/**
	 * Returns String representation of table name
	 * @return string
	 */
	static function getTableName() {
		return static::$_tableName;
	}

	/**
	 * Access to array of column names
	 * @return array
	 */
	static function getColumnNames() {
		return static::$_columnNames;
	}

	/**
	 * Access to array of fully-qualified(table.column) columns
	 * @return array
	 */
	static function getColumns() {
		return static::$_columns;
	}

	/**
	 * Access to array of column types, indexed by column name
	 * @return array
	 */
	static function getColumnTypes() {
		return static::$_columnTypes;
	}

	/**
	 * Get the type of a column
	 * @return string
	 */
	static function getColumnType($column_name) {
		return static::$_columnTypes[static::normalizeColumnName($column_name)];
	}

	private static $_lowerCaseColumns = array();

	/**
	 * @return bool
	 */
	static function hasColumn($column_name) {
		$class = get_called_class();
		if (!isset(self::$_lowerCaseColumns[$class])) {
			self::$_lowerCaseColumns[$class] = array_map('strtolower', static::$_columnNames);
		}
		return in_array(
			strtolower(static::normalizeColumnName($column_name)),
			self::$_lowerCaseColumns[$class]
		);
	}

	/**
	 * @param string $column_name
	 * @return string
	 */
	static function normalizeColumnName($column_name) {
		if (($pos = strrpos($column_name, '.')) !== false) {
			return substr($column_name, $pos + 1);
		}
		return $column_name;
	}

	/**
	 * Access to name of primary key
	 * @return array
	 */
	static function getPrimaryKey() {
		return static::$_primaryKey;
	}

	/**
	 * Access to array of primary keys
	 * @return array
	 */
	static function getPrimaryKeys() {
		return static::$_primaryKeys;
	}

	/**
	 * Returns true if the primary key column for this table is auto-increment
	 * @return bool
	 */
	static function isAutoIncrement() {
		return static::$_isAutoIncrement;
	}

	/**
	 *
	 * @param mixed $value
	 * @param string $column_type
	 * @param DABLPDO $conn
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	static function coerceTemporalValue($value, $column_type, DABLPDO $conn = null) {
		if (null === $conn) {
			$conn = static::getConnection();
		}

		if (is_array($value)) {
			foreach ($value as &$v) {
				$v = static::coerceTemporalValue($v, $column_type, $conn);
			}
			return $value;
		}

		$timestamp = is_int($value) ? $value : strtotime($value);
		if (false === $timestamp) {
			throw new InvalidArgumentException('Unable to parse date: ' . $value);
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
			case Model::COLUMN_TYPE_INTEGER_TIMESTAMP:
				return $timestamp;
		}

		return date($formatter, $timestamp);
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @return static
	 */
	static function retrieveByColumn($field, $value) {
		$pk = static::$_primaryKey;
		if ($pk) {
			if ($field === $pk) {
				return static::retrieveByPK($value);
			}
		}
		$q = static::getQuery()
			->add($field, $value)
			->setLimit(1);
		if ($pk) {
			$q->orderBy($pk);
		}

		return static::doSelectOne($q);
	}

	/**
	 * Populates and returns an instance with the
	 * first result of a query.  If the query returns no results,
	 * returns null.
	 * @return static
	 */
	static function fetchSingle($query_string) {
		$records = static::fetch($query_string);
		return array_shift($records);
	}

	/**
	 * Populates and returns an array of objects with the
	 * results of a query.  If the query returns no results,
	 * returns an empty Array.
	 * @return static[]
	 */
	static function fetch($query_string) {
		$result = static::getConnection()->query($query_string);
		return static::fromResult($result, get_called_class());
	}

	/**
	 * @return Query
	 */
	static function getQuery(array $params = array(), Query $q = null) {
		$model_class = get_called_class();
		$query_class = class_exists($model_class . 'Query') ? $model_class . 'Query' : 'Query';

		$q = $q ? clone $q : new $query_class;

		if (!$q->getTable()) {
			$q->setTable(static::getTableName());
		}

		// filters
		foreach ($params as $key => &$param) {
			if (static::hasColumn($key)) {
				$q->add($key, $param);
			}
		}

		// SortBy (alias of sort_by, deprecated)
		if (isset($params['SortBy']) && !isset($params['order_by'])) {
			$params['order_by'] = $params['SortBy'];
		}

		// order_by
		if (isset($params['order_by']) && static::hasColumn($params['order_by'])) {
			$q->orderBy($params['order_by'], isset($params['dir']) ? Query::DESC : Query::ASC);
		}

		// limit
		if (isset($params['limit'])) {
			$q->setLimit($params['limit']);
		}

		return $q;
	}

	/**
	 * Add (or replace) to the instance pool.
	 *
	 * @param Model $object
	 */
	static function insertIntoPool(Model $object) {
		if (
			!static::$_poolEnabled
			|| static::$_instancePoolCount >= static::MAX_INSTANCE_POOL_SIZE
			|| empty(static::$_primaryKeys)
		) {
			return;
		}

		$pool_key = implode('-', $object->getPrimaryKeyValues());
		if (empty($pool_key)) {
			return;
		}

		if (!isset(static::$_instancePool[$pool_key])) {
			++static::$_instancePoolCount;
		}

		static::$_instancePool[$pool_key] = $object;
	}

	/**
	 * Return the cached instance from the pool.
	 *
	 * @param mixed $pk_value Primary Key
	 * @return static
	 */
	static function retrieveFromPool($pk_value) {
		if (!static::$_poolEnabled || null === $pk_value) {
			return null;
		}

		$pk_value = strval($pk_value);
		if (isset(static::$_instancePool[$pk_value])) {
			return static::$_instancePool[$pk_value];
		}

		return null;
	}

	/**
	 * Remove the object from the instance pool.
	 *
	 * @param mixed $object_or_pk Object or PK to remove
	 * @return void
	 */
	static function removeFromPool($object_or_pk) {
		$pool_key = $object_or_pk instanceof Model ? implode('-', $object_or_pk->getPrimaryKeyValues()) : $object_or_pk;

		if (isset(static::$_instancePool[$pool_key])) {
			unset(static::$_instancePool[$pool_key]);
			--static::$_instancePoolCount;
		}
	}

	/**
	 * Empty the instance pool.
	 *
	 * @return void
	 */
	static function flushPool() {
		static::$_instancePool = array();
		static::$_instancePoolCount = 0;
	}

	/**
	 * @param bool $bool
	 */
	static function setPoolEnabled($bool = true) {
		static::$_poolEnabled = (bool) $bool;
	}

	/**
	 * @return bool
	 */
	static function getPoolEnabled() {
		return static::$_poolEnabled;
	}

	/**
	 * Returns an array of all objects in the database.
	 * $extra SQL can be appended to the query to LIMIT, SORT, and/or GROUP results.
	 * If there are no results, returns an empty Array.
	 * @param $extra string
	 * @return static[]
	 */
	static function getAll($extra = null) {
		$table_quoted = static::getConnection()->quoteIdentifier(static::getTableName(), true);
		return static::fetch("SELECT * FROM $table_quoted $extra ");
	}

	/**
	 * @return int
	 */
	static function doCount(Query $q = null) {
		$q = $q ? clone $q : static::getQuery();
		if (!$q->getTable()) {
			$q->setTable(static::getTableName());
		}
		return $q->doCount(static::getConnection());
	}

	/**
	 * @param Query $q The Query object that creates the SELECT query string
	 * @param array $additional_classes Array of additional classes for fromResult to instantiate as properties
	 * @return static[]
	 */
	static function doSelect(Query $q = null, $additional_classes = null) {
		if (is_array($additional_classes)) {
			array_unshift($additional_classes, get_called_class());
			$class = $additional_classes;
		} else {
			$class = get_called_class();
		}

		return static::fromResult(static::doSelectRS($q), $class);
	}

	/**
	 * @param Query $q The Query object that creates the SELECT query string
	 * @param array $additional_classes Array of additional classes for fromResult to instantiate as properties
	 * @return static
	 */
	static function doSelectOne(Query $q = null, $additional_classes = null) {
		$q = $q ? clone $q : static::getQuery();
		$q->setLimit(1);
		$result = static::doSelect($q, $additional_classes);
		return array_shift($result);
	}

	/**
	 * Executes a select query and returns the PDO result
	 * @return PDOStatement
	 */
	static function doSelectRS(Query $q = null) {
		$q = $q ? clone $q : static::getQuery();

		if (!$q->getTable()) {
			$q->setTable(static::getTableName());
		}

		return $q->doSelect(static::getConnection());
	}

	/**
	 * Returns a simple Iterator that wraps PDOStatement for lightweight foreach
	 *
	 * @param Query $q
	 * @return QueryModelIterator
	 */
	static function doSelectIterator(Query $q = null) {
		$q = $q ? clone $q : static::getQuery();

		if (!$q->getTable()) {
			$q->setTable(static::getTableName());
		}

		return new QueryModelIterator($q, get_called_class());
	}

	/**
	 * Returns an array of objects of class $class from
	 * the rows of a PDOStatement(query result)
	 *
	 * @param PDOStatement $result
	 * @param string $class_name name of class to create
	 * @return static[]
	 */
	static function fromResult(PDOStatement $result, $class_name = null, $use_pool = null) {
		if (null === $class_name) {
			$class_name = get_called_class();
		}
		if (null === $use_pool) {
			$use_pool = static::$_poolEnabled;
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
		foreach (static::$_columnNames as &$column_name) {
			$this->{$column_name} = $values[$startcol++];
		}
		if (static::$_primaryKeys && !$this->hasPrimaryKeyValues()) {
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
		foreach (static::$_columnNames as &$column_name) {
			if (array_key_exists($column_name, $values)) {
				$this->{$column_name} = $values[$column_name];
			}
		}
		if (static::$_primaryKeys && !$this->hasPrimaryKeyValues()) {
			return false;
		}
		$this->castInts();
		$this->setNew(false);
		return true;
	}

	/**
	 * @param Query $q
	 * @param bool $flush_pool
	 * @return int
	 */
	static function doDelete(Query $q, $flush_pool = true) {
		$q = clone $q;
		if (!$q->getTable()) {
			$q->setTable(static::getTableName());
		}
		$result = $q->doDelete(static::getConnection());

		if ($flush_pool) {
			static::flushPool();
		}

		return $result;
	}

	/**
	 * @param array $column_values
	 * @param Query $q The Query object that creates the SELECT query string
	 * @return static[]
	 */
	static function doUpdate(array $column_values, Query $q = null) {
		$q = $q ? clone $q : static::getQuery();

		if (!$q->getTable()) {
			$q->setTable(static::getTableName());
		}

		return $q->doUpdate($column_values, static::getConnection());
	}

	/**
	 * Set the maximum insert batch size, once this size is reached the batch automatically inserts.
	 * @param int $size
	 * @return int insert batch size
	 */
	static function setInsertBatchSize($size = 500) {
		return static::$_insertBatchSize = $size;
	}

	/**
	 * @return int row count
	 * @throws RuntimeException
	 */
	static function insertBatch() {
		$records = static::$_insertBatch;
		if (!$records) {
			return 0;
		}
		$conn = static::getConnection();
		$columns = static::$_columnNames;
		$quoted_table = $conn->quoteIdentifier(static::getTableName(), true);

		$auto_increment = static::isAutoIncrement();
		if ($auto_increment) {
			$pk = static::$_primaryKey;
			foreach ($columns as $index => &$column_name) {
				if ($column_name === $pk) {
					unset($columns[$index]);
					break;
				}
			}
		}

		$values = array();
		$query_s = 'INSERT INTO ' . $quoted_table . ' (' . implode(', ', array_map(array($conn, 'quoteIdentifier'), $columns)) . ') VALUES' . "\n";

		foreach ($records as $k => $r) {
			$placeholders = array();

			if (!$r->validate()) {
				throw new RuntimeException('Cannot save ' . get_class($r) . ' with validation errors: ' . implode(', ', $r->getValidationErrors()));
			}
			if (
				$r->isNew()
				&& $r->hasColumn('created')
				&& !$r->isColumnModified('created')
			) {
				$r->setCreated(time());
			}

			if (
				($r->isNew() || $r->isModified())
				&& $r->hasColumn('updated')
				&& !$r->isColumnModified('updated')
			) {
				$r->setUpdated(time());
			}

			foreach ($columns as &$column) {
				if ($auto_increment && $column === $pk) {
					continue;
				}
				$values[] = $r->$column;
				$placeholders[] = '?';
			}

			if ($k > 0) {
				$query_s .= ",\n";
			}
			$query_s .= '(' . implode(', ', $placeholders) . ')';
		}

		$statement = new QueryStatement($conn);
		$statement->setString($query_s);
		$statement->setParams($values);

		$result = $statement->bindAndExecute();

		foreach ($records as $r) {
			$r->setNew(false);
			$r->resetModified();

			if ($r->hasPrimaryKeyValues()) {
				static::insertIntoPool($r);
			} else {
				$r->setDirty(true);
			}
		}

		static::$_insertBatch = array();
		return $result->rowCount();
	}

	/**
	 * Queue for batch insert
	 * @return Model this
	 */
	function queueForInsert() {
		// If we've reached the maximum batch size, insert it and empty it.
		if (count(static::$_insertBatch) >= static::$_insertBatchSize) {
			static::insertBatch();
		}

		static::$_insertBatch[] = $this;

		return $this;
	}

	/**
	 * Creates new instance of self and with the same values as $this, except
	 * the primary key value is cleared
	 * @return static
	 */
	function copy() {
		$new_object = new static();
		$values = $this->toArray();

		foreach (static::$_primaryKeys as $pk) {
			unset($values[$pk]);
		}

		$new_object->fromArray($values);
		return $new_object;
	}

	/**
	 * Checks whether any of the columns have been modified from the database values.
	 * @return bool
	 */
	function isModified() {
		return !empty($this->_modifiedColumns);
	}

	/**
	 * Checks whether the given column is in the modified array
	 * @return bool
	 */
	function isColumnModified($column_name) {
		return array_key_exists(
			strtolower(static::normalizeColumnName($column_name)),
			array_map('strtolower', $this->_modifiedColumns)
		);
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
	 * @return static
	 */
	function setColumnValue($column_name, $value, $column_type = null) {
		if (null === $column_type) {
			$column_type = static::getColumnType($column_name);
		}

		static $true = array(true, 1, '1', 'on', 'true');
		static $false = array(false, 0, '0', 'off', 'false');

		if ($column_type === self::COLUMN_TYPE_BOOLEAN) {
			if (is_string($value)) {
				$value = strtolower($value);
			}
			if (in_array($value, $true, true)) {
				$value = 1;
			} elseif (in_array($value, $false, true)) {
				$value = 0;
			} elseif ($value === '' || $value === null) {
				$value = null;
			} else {
				throw new InvalidArgumentException($value . ' is not a valid boolean value');
			}
		} else {
			$temporal = static::isTemporalType($column_type);
			$numeric = static::isNumericType($column_type);

			if ($numeric || $temporal) {
				if (is_string($value)) {
					$value = trim($value);
				}
				if ('' === $value) {
					$value = null;
				} elseif (null !== $value) {
					if ($temporal) {
						$value = static::coerceTemporalValue($value, $column_type, static::getConnection());
					} elseif ($numeric) {
						if (is_bool($value)) {
							$value = $value ? 1 : 0;
						} elseif (static::isIntegerType($column_type)) {
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
				}
			}
		}

		if ($this->$column_name !== $value) {
			$this->_modifiedColumns[$column_name] = $column_name;
			$this->$column_name = $value;
		}
		return $this;
	}

	/**
	 * Clears the array of modified column names
	 * @return static
	 */
	function resetModified() {
		$this->_modifiedColumns = array();
		return $this;
	}

	/**
	 * Populates $this with the values of an associative Array.
	 * Array keys must match column names to be used.
	 * @param array $array
	 * @return static
	 */
	function fromArray($array) {
		foreach ($array as $column => &$v) {
			if (
				is_string($column) === false
				|| !isset(static::$_columnTypes[$column])
			) {
				continue;
			}
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
		foreach (static::$_columnNames as &$column)
			$array[$column] = $this->{'get' . $column}();
		return $array;
	}

	/**
	 * Returns an associative Array with the values of $this that are JSON friendly.
	 * Timestamps will be in ISO 8601 format.
	 * Array keys match column names.
	 * @return array
	 */
    function jsonSerialize() {
		$array = $this->toArray();
		foreach (static::$_columnTypes as $column => &$type) {
			if (!isset($array[$column])) {
				continue;
			}
			$value = &$array[$column];

			if ($type === Model::COLUMN_TYPE_BOOLEAN) {
				if (0 === $value) {
					$value = false;
				} elseif (1 === $value) {
					$value = true;
				}
			} else if (
				$type === Model::COLUMN_TYPE_TIMESTAMP
				|| $type === Model::COLUMN_TYPE_INTEGER_TIMESTAMP
			) {
				if (!$value) {
					$array[$column] = null;
					continue;
				}
				if (!is_int($value)) {
					$value = strtotime($value);
				}
				if (!is_int($value)) {
					throw new RuntimeException('Error parsing date "' . $array[$column] . '"');
				}
				$value = date('c', $value);
			}
		}
		return $array;
    }

	/**
	 * Sets whether to use cached results for foreign keys or to execute
	 * the query each time, even if it hasn't changed.
	 * @param bool $value[optional]
	 * @return static
	 */
	function setCacheResults($value = true) {
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
		$pks = static::$_primaryKeys;
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
		$pks = static::$_primaryKeys;

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
		$pks = static::$_primaryKeys;
		if (!$pks) {
			throw new RuntimeException('This table has no primary keys');
		}
		$q = static::getQuery();
		foreach ($pks as &$pk) {
			if ($this->$pk === null) {
				throw new RuntimeException('Cannot delete using NULL primary key.');
			}
			$q->addAnd($pk, $this->$pk);
		}
		$q->setTable(static::getTableName());
		$result = $this->doDelete($q, false);
		static::removeFromPool($this);
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
		if ($this->isDirty()) {
			throw new RuntimeException('Cannot save dirty ' . get_class($this) . '.  Perhaps it was already saved using bulk insert.');
		}

		if (!$this->validate()) {
			throw new RuntimeException('Cannot save ' . get_class($this) . ' with validation errors: ' . implode(', ', $this->getValidationErrors()));
		}

		if (
			$this->isNew()
			&& static::hasColumn('created')
			&& !$this->isColumnModified('created')
		) {
			$this->setCreated(time());
		}

		if (
			($this->isNew() || $this->isModified())
			&& static::hasColumn('updated')
			&& !$this->isColumnModified('Updated')
		) {
			$this->setUpdated(time());
		}

		if ($this->isNew()) {
			return $this->insert();
		} else {
			return $this->update();
		}
	}

	function archive() {
		if (!static::hasColumn('archived')) {
			throw new RuntimeException('Cannot call archive on models without "archived" column');
		}

		if (null !== $this->getArchived()) {
			throw new RuntimeException('This ' . get_class($this) . ' is already archived.');
		}

		$this->setArchived(time());

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
	 * @return static
	 */
	function setNew($bool) {
		$this->_isNew = (bool) $bool;
		return $this;
	}

	/**
	 * Returns true if this is out of sync with the database
	 * @return bool
	 */
	function isDirty() {
		return (bool) $this->_isDirty;
	}

	/**
	 * Indicate whether this object is out of sync with the database
	 * @param bool $bool
	 * @return static
	 */
	function setDirty($bool) {
		$this->_isDirty = (bool) $bool;
		return $this;
	}

	/**
	 * Cast returned values from the database into integers where appropriate.
	 * @return static
	 */
	function castInts() {
		foreach (static::$_columnTypes as $column => &$type) {
			if ($this->{$column} === null || !static::isIntegerType($type)) {
				continue;
			}
			if ('' === $this->{$column}) {
				$this->{$column} = null;
				continue;
			}
			$this->{$column} = (int) $this->{$column};
		}
		return $this;
	}

	/**
	 * Creates and executes INSERT query string for this object
	 * @return int
	 */
	protected function insert() {
		$conn = static::getConnection();
		$pk = static::$_primaryKey;

		$fields = array();
		$values = array();
		$placeholders = array();
		foreach (static::$_columnNames as &$column) {
			$value = $this->$column;
			if ($value === null && !$this->isColumnModified($column))
				continue;
			$fields[] = $conn->quoteIdentifier($column, true);
			$values[] = $value;
			$placeholders[] = '?';
		}

		$quoted_table = $conn->quoteIdentifier(static::getTableName(), true);
		$query_s = 'INSERT INTO ' . $quoted_table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ') ';

		if ($pk && $this->isAutoIncrement() && $conn instanceof DBPostgres) {
			$query_s .= ' RETURNING ' . $conn->quoteIdentifier($pk, true);
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
		if (!static::$_primaryKeys) {
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

		$q = static::getQuery();

		foreach (static::$_primaryKeys as $pk) {
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
		$column = "$foreign_table.$foreign_column";
		if ($q) {
			$q = clone $q;
			$alias = $q->getAlias();
			if ($alias && $foreign_table == $q->getTable()) {
				$column = "$alias.$foreign_column";
			}
		} else {
			$q = new Query;
		}
		$q->add($column, $value);
		return $q;
	}

}