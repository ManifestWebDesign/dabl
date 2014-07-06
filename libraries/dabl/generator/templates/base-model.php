<?php

$used_methods = array(
	'getTableName',
	'getColumnNames',
	'getColumns',
	'getColumnTypes',
	'getColumnType',
	'hasColumn',
	'getPrimaryKeys',
	'getPrimaryKey',
	'isAutoIncrement',
	'fetchSingle',
	'fetch',
	'fromResult',
	'castInts',
	'insertIntoPool',
	'retrieveFromPool',
	'removeFromPool',
	'flushPool',
	'setPoolEnabled',
	'getPoolEnabled',
	'getAll',
	'doCount',
	'doDelete',
	'doSelect',
	'doSelectOne',
	'doUpdate'
);

echo '<?php';
?>

/**
 *		Created by Dan Blaisdell's DABL
 *		Do not alter base files, as they will be overwritten.
 *		To alter the objects, alter the extended classes in
 *		the 'models' folder.
 *
 */
abstract class base<?php echo $class_name ?> extends ApplicationModel {

<?php foreach ($fields as $key => $field): ?>
	const <?php echo StringFormat::constant($field->getName()) ?> = '<?php echo $table_name ?>.<?php echo $field->getName() ?>';
<?php endforeach ?>

	/**
	 * Name of the table
	 * @var string
	 */
	protected static $_tableName = '<?php echo $table_name ?>';

	/**
	 * Cache of objects retrieved from the database
	 * @var <?php echo $class_name ?>[]
	 */
	protected static $_instancePool = array();

	protected static $_instancePoolCount = 0;

	protected static $_poolEnabled = true;

	/**
	 * Array of objects to batch insert
	 */
	protected static $_insertBatch = array();

	/**
	 * Maximum size of the insert batch
	 */
	protected static $_insertBatchSize = 500;

	/**
	 * Array of all primary keys
	 * @var string[]
	 */
	protected static $_primaryKeys = array(
<?php if ($PKs): ?>
<?php foreach ($PKs as &$the_pk): ?>
		'<?php echo $the_pk ?>',
<?php endforeach ?>
<?php endif ?>
	);

	/**
	 * string name of the primary key column
	 * @var string
	 */
	protected static $_primaryKey = '<?php echo $PK ?>';

	/**
	 * true if primary key is an auto-increment column
	 * @var bool
	 */
	protected static $_isAutoIncrement = <?php echo $auto_increment ? 'true' : 'false' ?>;

	/**
	 * array of all fully-qualified(table.column) columns
	 * @var string[]
	 */
	protected static $_columns = array(
<?php foreach ($fields as $key => $field): ?>
		<?php echo $class_name ?>::<?php echo StringFormat::constant($field->getName()) ?>,
<?php endforeach ?>
	);

	/**
	 * array of all column names
	 * @var string[]
	 */
	protected static $_columnNames = array(
<?php foreach ($fields as $key => $field): ?>
		'<?php echo $field->getName() ?>',
<?php endforeach ?>
	);

	/**
	 * array of all column types
	 * @var string[]
	 */
	protected static $_columnTypes = array(
<?php foreach ($fields as $key => $field): ?>
		'<?php echo $field->getName() ?>' => Model::COLUMN_TYPE_<?php echo $field->getType() ?>,
<?php endforeach ?>
	);

<?php
foreach ($fields as $key => $field) {
	$default = $field->getDefaultValue() ? $field->getDefaultValue()->getValue() : null;
	// fix for MSSQL default value weirdness
	if ($field->isNumericType()) {
		$default = trim($default, '()');
	}
?>
	/**
	 * <?php echo $conn->quoteIdentifier($field->getName(), true) ?> <?php echo $field->getType() ?>
<?php if ($field->isNotNull()): ?> NOT NULL<?php endif ?>
<?php if (null !== $default): ?> DEFAULT <?php echo ctype_digit($default) ? $default : $conn->quote($default) ?><?php endif ?>

	 * @var <?php echo $field->getPhpType() ?>

	 */
<?php
	if (($field->isNumericType()) && (!ctype_digit($default)) && (!$default)) $default = null;
?>
	protected $<?php echo $field->getName() ?><?php
if ($field->isNumericType() && $default !== null)
	echo ' = ' . $default;
elseif ($default !== null && strtolower($default) !== 'null')
	echo " = '" . addslashes($default) . "'"
?>;

<?php
}

// GETTERS AND SETTERS
foreach ($fields as $key => $field):
	$default = $field->getDefaultValue() ? $field->getDefaultValue()->getValue() : null;
	$method_name = StringFormat::titleCase($field->getName());
	$params = '';
	$param_vars = '';
	if ($field->isTemporalType()) {
		if ($field->getType() === Model::COLUMN_TYPE_INTEGER_TIMESTAMP) {
			$params = '$format = \'' . $conn->getTimeStampFormatter() . '\'';
		} else {
			$params = '$format = null';
		}
		$param_vars = '$format';
	}
	$used_methods[] = "get$method_name";
	$raw_method_name = ucfirst($field->getName());
?>
	/**
	 * Gets the value of the <?php echo $field->getName() ?> field
	 */
	function get<?php echo $method_name ?>(<?php echo $params ?>) {
<?php if ($field->isTemporalType()): ?>
		if (null === $this-><?php echo $field->getName() ?> || null === $format) {
			return $this-><?php echo $field->getName() ?>;
		}
<?php if ($field->getType() === Model::COLUMN_TYPE_INTEGER_TIMESTAMP): ?>
		return date($format, $this-><?php echo $field->getName() ?>);
<?php else: ?>
		if (0 === strpos($this-><?php echo $field->getName() ?>, '0000-00-00')) {
			return null;
		}
		return date($format, strtotime($this-><?php echo $field->getName() ?>));
<?php endif ?>
<?php else: ?>
		return $this-><?php echo $field->getName() ?>;
<?php endif ?>
	}

<?php $used_methods[] = "set$method_name"; ?>
	/**
	 * Sets the value of the <?php echo $field->getName() ?> field
	 * @return <?php echo $class_name ?>

	 */
	function set<?php echo $method_name ?>($value) {
		return $this->setColumnValue('<?php echo $field->getName() ?>', $value, Model::COLUMN_TYPE_<?php echo $field->getType() ?>);
	}

<?php if (strtolower($raw_method_name) != strtolower($method_name)): ?>
<?php $used_methods[] = "get$raw_method_name"; ?>
	/**
	 * Convenience function for <?php echo $class_name ?>::get<?php echo $method_name ?>

	 * final because get<?php echo $method_name ?> should be extended instead
	 * to ensure consistent behavior
	 * @see <?php echo $class_name ?>::get<?php echo $method_name ?>

	 */
	final function get<?php echo $raw_method_name ?>(<?php echo $params ?>) {
		return $this->get<?php echo $method_name ?>(<?php echo $param_vars ?>);
	}

<?php $used_methods[] = "set$raw_method_name"; ?>
	/**
	 * Convenience function for <?php echo $class_name ?>::set<?php echo $method_name ?>

	 * final because set<?php echo $method_name ?> should be extended instead
	 * to ensure consistent behavior
	 * @see <?php echo $class_name ?>::set<?php echo $method_name ?>

	 * @return <?php echo $class_name ?>

	 */
	final function set<?php echo $raw_method_name ?>($value) {
		return $this->set<?php echo $method_name ?>($value);
	}

<?php endif ?>
<?php endforeach ?>
	/**
	 * @return DABLPDO
	 */
<?php $used_methods[] = 'getConnection'; ?>
	static function getConnection() {
		return DBManager::getConnection('<?php echo $this->getConnectionName() ?>');
	}

	/**
	 * Searches the database for a row with the ID(primary key) that matches
	 * the one input.
	 * @return <?php echo $class_name ?>

	 */
<?php $used_methods[] = 'retrieveByPK'; ?>
	 static function retrieveByPK(<?php if ($PKs && count($PKs) == 1): ?>$<?php echo StringFormat::variable($PKs[0]) ?><?php else: ?>$the_pk<?php endif ?>) {
<?php if (count($PKs) > 1): ?>
		throw new Exception('This table has more than one primary key.  Use retrieveByPKs() instead.');
<?php else: ?>
		return static::retrieveByPKs(<?php if ($PKs && count($PKs) == 1): ?>$<?php echo StringFormat::variable($PKs[0]) ?><?php else: ?>$the_pk<?php endif ?>);
<?php endif ?>
	}

	/**
	 * Searches the database for a row with the primary keys that match
	 * the ones input.
	 * @return <?php echo $class_name ?>

	 */
<?php $used_methods[] = 'retrieveByPKs'; ?>
	static function retrieveByPKs(<?php foreach ($PKs as $k => &$v): ?><?php if ($k > 0): ?>, <?php endif ?>$<?php echo StringFormat::variable($v) ?><?php endforeach ?>) {
<?php if (0 === count($PKs)): ?>
		throw new Exception('This table does not have any primary keys.');
<?php else: ?>
<?php foreach ($PKs as $k => &$v): ?>
		if (null === $<?php echo StringFormat::variable($v) ?>) {
			return null;
		}
<?php endforeach ?>
<?php if (1 !== count($PKs)): ?>
		$args = func_get_args();
<?php endif; ?>
		if (static::$_poolEnabled) {
			$pool_instance = static::retrieveFromPool(<?php if (1 == count($PKs)): ?>$<?php echo StringFormat::variable($PK) ?><?php else: ?>implode('-', $args)<?php endif ?>);
			if (null !== $pool_instance) {
				return $pool_instance;
			}
		}
		$q = new Query;
<?php foreach ($PKs as $k => &$v): ?>
		$q->add('<?php echo $v ?>', $<?php echo StringFormat::variable($v) ?>);
<?php endforeach ?>
		return static::doSelectOne($q);
<?php endif ?>
	}

<?php
	foreach ($this->getColumns($table_name) as $field) {
?>
	/**
	 * Searches the database for a row with a <?php echo $field->getName() ?>

	 * value that matches the one provided
	 * @return <?php echo $class_name ?>

	 */
	static function retrieveBy<?php echo StringFormat::titleCase($field->getName()) ?>($value) {
<?php
		if ($field->isPrimaryKey() && count($field->getTable()->getPrimaryKey()) === 1) {
?>
		return <?php echo $class_name?>::retrieveByPK($value);
<?php
		} else {
?>
		return static::retrieveByColumn('<?php echo $field->getName() ?>', $value);
<?php
		}
?>
	}

<?php
	}
?>

	/**
	 * Casts values of int fields to (int)
	 * @return <?php echo $class_name ?>

	 */
	function castInts() {
<?php foreach ($fields as $key => $field): ?>
<?php if (Model::isIntegerType($field->getType())): ?>
		$this-><?php echo $field->getName() ?> = (null === $this-><?php echo $field->getName() ?>) ? null : (int) $this-><?php echo $field->getName() ?>;
<?php endif ?>
<?php endforeach ?>
		return $this;
	}

<?php
$to_table_list = array();

foreach ($this->getForeignKeysFromTable($table_name) as $r){
	$to_table = $r->getForeignTableName();
	if (isset($to_table_list[$to_table])) {
		if ($to_table_list[$to_table] == 1) {
			$this->warnings[] = "$table_name has more than one foreign key to $to_table.  $to_table::get{$table_name}s will not be created.";
		}
		++$to_table_list[$to_table];
	} else {
		$to_table_list[$to_table] = 1;
	}
}

foreach ($this->getForeignKeysFromTable($table_name) as $r):
	$to_table = $r->getForeignTableName();
	$to_class_name = $this->getModelName($to_table);
	$lc_to_class_name = strtolower($to_class_name);
	$foreign_columns = $r->getForeignColumns();
	$to_column = array_shift($foreign_columns);
	$local_columns = $r->getLocalColumns();
	$from_column = array_shift($local_columns);
	$named_id = false;
	$foreign_column = $this->database->getTable($to_table)->getColumn($to_column);
	$fk_is_pk = $foreign_column->isPrimaryKey();

	$id_pos = strrpos(strtolower($from_column), '_id');
	if ($id_pos !== strlen($from_column) - 3) {
		$id_pos = strrpos($from_column, 'Id');
		if ($id_pos !== strlen($from_column) - 2) {
			$id_pos = strrpos($from_column, 'ID');
			if ($id_pos !== strlen($from_column) - 2) {
				$id_pos = false;
			}
		}
	}

	if (false !== $id_pos) {
		$from_column_clean = substr($from_column, 0, $id_pos);
		$is_field = false;
		foreach ($fields as $field) {
			if ($field->getName() == $from_column_clean) {
				$is_field = true;
				break;
			}
		}

		if ($is_field) {
			$this->warnings[] = "Can't create convenience functions for column $from_column: get$from_column_clean() and set$from_column_clean(), consider renaming column $from_column_clean.";
		} else {
			$named_id = true;
		}
	}
	if (!$fk_is_pk) {
		$fk_property = '_' . $to_class_name . 'RelatedBy' . StringFormat::titleCase($from_column);
?>
	protected $<?php echo $fk_property?>;

<?php
	}
	if ($named_id) {
?>
<?php $used_methods[] = 'set' . StringFormat::titleCase($from_column_clean); ?>
	/**
	 * @return <?php echo $class_name ?>

	 */
	function set<?php echo StringFormat::titleCase($from_column_clean) ?>(<?php echo $to_class_name ?> $<?php echo $lc_to_class_name ?> = null) {
		return $this->set<?php echo $to_class_name ?>RelatedBy<?php echo StringFormat::titleCase($from_column) ?>($<?php echo $lc_to_class_name ?>);
	}

<?php
	}
?>
<?php $used_methods[] = "set$to_class_name" . 'RelatedBy' . StringFormat::titleCase($from_column); ?>
	/**
	 * @return <?php echo $class_name ?>

	 */
	function set<?php echo $to_class_name ?>RelatedBy<?php echo StringFormat::titleCase($from_column) ?>(<?php echo $to_class_name ?> $<?php echo $lc_to_class_name ?> = null) {
		if (null === $<?php echo $lc_to_class_name ?>) {
			$this->set<?php echo $from_column ?>(null);
		} else {
			if (!$<?php echo $lc_to_class_name ?>->get<?php echo $to_column ?>()) {
				throw new Exception('Cannot connect a <?php echo $to_class_name ?> without a <?php echo $to_column ?>');
			}
			$this->set<?php echo $from_column ?>($<?php echo $lc_to_class_name ?>->get<?php echo $to_column ?>());
		}
<?php
if (!$fk_is_pk) {
?>
		if ($this->getCacheResults()) {
			$this-><?php echo $fk_property?> = $<?php echo $lc_to_class_name ?>;
		}
<?php
}
?>
		return $this;
	}
<?php
	if ($named_id) {
?>

	/**
	 * Returns a <?php echo $to_table ?> object with a <?php echo $to_column ?>

	 * that matches $this-><?php echo $from_column ?>.
	 * @return <?php echo $to_class_name ?>

	 */
<?php $used_methods[] = 'get' . StringFormat::titleCase($from_column_clean); ?>
	function get<?php echo StringFormat::titleCase($from_column_clean) ?>() {
		return $this->get<?php echo $to_class_name ?>RelatedBy<?php echo StringFormat::titleCase($from_column) ?>();
	}
<?php
	}
?>

	/**
	 * Returns a <?php echo $to_table ?> object with a <?php echo $to_column ?>

	 * that matches $this-><?php echo $from_column ?>.
	 * @return <?php echo $to_class_name ?>

	 */
<?php $used_methods[] = "get$to_class_name" . 'RelatedBy' . StringFormat::titleCase($from_column); ?>
	function get<?php echo $to_class_name ?>RelatedBy<?php echo StringFormat::titleCase($from_column) ?>() {
		$fk_value = $this->get<?php echo $from_column ?>();
		if (null === $fk_value) {
			return null;
		}
<?php
	if ($fk_is_pk) {
?>
		return <?php echo $to_class_name ?>::retrieveByPK($fk_value);
<?php
	} else {
?>
		$result = $this-><?php echo $fk_property?>;
		if (null !== $result && $result->get<?php echo StringFormat::titleCase($to_column) ?>) === $fk_value) {
			return $result;
		}

		$result = <?php echo $to_class_name ?>::retrieveBy<?php echo StringFormat::titleCase($to_column) ?>($fk_value);

		if ($this->getCacheResults()) {
			$this-><?php echo $fk_property?> = $result;
		}
		return $result;
<?php
	}
?>
	}

<?php
	if ($named_id) {
?>
<?php $used_methods[] = 'doSelectJoin' . StringFormat::titleCase($from_column_clean); ?>
	static function doSelectJoin<?php echo StringFormat::titleCase($from_column_clean) ?>(Query $q = null, $join_type = Query::LEFT_JOIN) {
		return static::doSelectJoin<?php echo $to_class_name ?>RelatedBy<?php echo StringFormat::titleCase($from_column) ?>($q, $join_type);
	}

<?php
	}
	if ($to_table_list[$to_table] < 2) {
		if (!in_array("get$to_class_name", $used_methods)) {
?>
	/**
	 * Returns a <?php echo $to_table ?> object with a <?php echo $to_column ?>

	 * that matches $this-><?php echo $from_column ?>.
	 * @return <?php echo $to_class_name ?>

	 */
<?php $used_methods[] = "get$to_class_name"; ?>
	function get<?php echo $to_class_name ?>() {
		return $this->get<?php echo $to_class_name ?>RelatedBy<?php echo StringFormat::titleCase($from_column) ?>();
	}

<?php
		}
		if (!in_array("set$to_class_name", $used_methods)) {

?>
<?php $used_methods[] = "set$to_class_name"; ?>
	/**
	 * @return <?php echo $class_name ?>

	 */
	function set<?php echo $to_class_name ?>(<?php echo $to_class_name ?> $<?php echo $lc_to_class_name ?> = null) {
		return $this->set<?php echo $to_class_name ?>RelatedBy<?php echo StringFormat::titleCase($from_column) ?>($<?php echo $lc_to_class_name ?>);
	}

<?php
		}
	}
?>
	/**
	 * @return <?php echo $class_name ?>[]
	 */
<?php $used_methods[] = "doSelectJoin$to_class_name" . 'RelatedBy' . StringFormat::titleCase($from_column); ?>
	static function doSelectJoin<?php echo $to_class_name ?>RelatedBy<?php echo StringFormat::titleCase($from_column) ?>(Query $q = null, $join_type = Query::LEFT_JOIN) {
		$q = $q ? clone $q : new Query;
		$columns = $q->getColumns();
		$alias = $q->getAlias();
		$this_table = $alias ? $alias : static::getTableName();
		if (!$columns) {
			if ($alias) {
				foreach (static::getColumns() as $column_name) {
					$columns[] = $alias . '.' . $column_name;
				}
			} else {
				$columns = static::getColumns();
			}
		}

		$to_table = <?php echo $to_class_name ?>::getTableName();
		$q->join($to_table, $this_table . '.<?php echo $from_column ?> = ' . $to_table . '.<?php echo $to_column ?>', $join_type);
		foreach (<?php echo $to_class_name ?>::getColumns() as $column) {
			$columns[] = $column;
		}
		$q->setColumns($columns);

		return static::doSelect($q, array('<?php echo $to_class_name ?>'));
	}

<?php endforeach ?>
	/**
	 * @return <?php echo $class_name ?>[]
	 */
<?php $used_methods[] = 'doSelectJoinAll'; ?>
	static function doSelectJoinAll(Query $q = null, $join_type = Query::LEFT_JOIN) {
		$q = $q ? clone $q : new Query;
		$columns = $q->getColumns();
		$classes = array();
		$alias = $q->getAlias();
		$this_table = $alias ? $alias : static::getTableName();
		if (!$columns) {
			if ($alias) {
				foreach (static::getColumns() as $column_name) {
					$columns[] = $alias . '.' . $column_name;
				}
			} else {
				$columns = static::getColumns();
			}
		}
<?php
	foreach ($this->getForeignKeysFromTable($table_name) as $r):
		$to_table = $r->getForeignTableName();
		$to_class_name = $this->getModelName($to_table);
		$foreign_columns = $r->getForeignColumns();
		$to_column = array_shift($foreign_columns);
		$local_columns = $r->getLocalColumns();
		$from_column = array_shift($local_columns);
?>

		$to_table = <?php echo $to_class_name ?>::getTableName();
		$q->join($to_table, $this_table . '.<?php echo $from_column ?> = ' . $to_table . '.<?php echo $to_column ?>', $join_type);
		foreach (<?php echo $to_class_name ?>::getColumns() as $column) {
			$columns[] = $column;
		}
		$classes[] = '<?php echo $to_class_name ?>';
	<?php endforeach ?>

		$q->setColumns($columns);
		return static::doSelect($q, $classes);
	}

<?php
$from_table_list = array();

foreach ($this->getForeignKeysToTable($table_name) as $r):
	$from_table = $r->getTableName();

	if (isset($from_table_list[$from_table])) {
		$from_table_list[$from_table] += 1;
	} else {
		$from_table_list[$from_table] = 1;
	}

	$from_class_name = $this->getModelName($from_table);
	$local_columns = $r->getLocalColumns();
	$from_column = array_shift($local_columns);
	$foreign_columns = $r->getForeignColumns();
	$to_column = array_shift($foreign_columns);
	$to_table = $r->getForeignTableName();
	$cache_property = $from_class_name . 'sRelatedBy' . StringFormat::titleCase($from_column) . '_c';
?>
	/**
	 * Returns a Query for selecting <?php echo $from_table ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return Query
	 */
<?php $used_methods[] = 'get' . StringFormat::titleCase($from_class_name) . 'sRelatedBy' . StringFormat::titleCase($from_column) . 'Query'; ?>
	function get<?php echo StringFormat::titleCase($from_class_name) ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>Query(Query $q = null) {
		return $this->getForeignObjectsQuery('<?php echo $from_table ?>', '<?php echo $from_column ?>', '<?php echo $to_column ?>', $q);
	}

	/**
	 * Returns the count of <?php echo $from_class_name ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return int
	 */
<?php $used_methods[] = 'count' . StringFormat::titleCase($from_class_name) . 'sRelatedBy' . StringFormat::titleCase($from_column); ?>
	function count<?php echo StringFormat::titleCase($from_class_name) ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>(Query $q = null) {
		if (null === $this->get<?php echo $to_column ?>()) {
			return 0;
		}
		return <?php echo $from_class_name ?>::doCount($this->get<?php echo StringFormat::titleCase($from_class_name) ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>Query($q));
	}

	/**
	 * Deletes the <?php echo $from_table ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return int
	 */
<?php $used_methods[] = 'delete' . StringFormat::titleCase($from_class_name) . 'sRelatedBy' . StringFormat::titleCase($from_column); ?>
	function delete<?php echo StringFormat::titleCase($from_class_name) ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>(Query $q = null) {
		if (null === $this->get<?php echo $to_column ?>()) {
			return 0;
		}
		$this-><?php echo $cache_property ?> = array();
		return <?php echo $from_class_name ?>::doDelete($this->get<?php echo StringFormat::titleCase($from_class_name) ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>Query($q));
	}

	protected $<?php echo $cache_property ?> = array();

	/**
	 * Returns an array of <?php echo $from_class_name ?> objects with a <?php echo $from_column ?>

	 * that matches $this-><?php echo $to_column ?>.
	 * When first called, this method will cache the result.
	 * After that, if $this-><?php echo $to_column ?> is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return <?php echo $from_class_name ?>[]
	 */
<?php $used_methods[] = 'get' . StringFormat::titleCase($from_class_name) . 'sRelatedBy' . StringFormat::titleCase($from_column); ?>
	function get<?php echo StringFormat::titleCase($from_class_name) ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>(Query $q = null) {
		if (null === $this->get<?php echo $to_column ?>()) {
			return array();
		}

		if (
			null === $q
			&& $this->getCacheResults()
			&& !empty($this-><?php echo $cache_property ?>)
			&& !$this->isColumnModified('<?php echo $to_column ?>')
		) {
			return $this-><?php echo $cache_property ?>;
		}

		$result = <?php echo $from_class_name ?>::doSelect($this->get<?php echo $from_class_name ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>Query($q));

		if ($q !== null) {
			return $result;
		}

		if ($this->getCacheResults()) {
			$this-><?php echo $cache_property ?> = $result;
		}
		return $result;
	}

<?php endforeach ?>
<?php
	foreach ($this->getForeignKeysToTable($table_name) as $r) {
		$from_table = $r->getTableName();

		if (@$from_table_list[$from_table] > 1) {
			continue;
		}

		$from_class_name = $this->getModelName($from_table);
		$local_columns = $r->getLocalColumns();
		$from_column = array_shift($local_columns);
		$foreign_columns = $r->getForeignColumns();
		$to_column = array_shift($foreign_columns);
		$to_table = $r->getForeignTableName();

		if (!in_array('get' . StringFormat::titleCase($from_class_name) . 's', $used_methods)) {
?>
	/**
	 * Convenience function for <?php echo $class_name ?>::get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>

	 * @return <?php echo $from_class_name ?>[]
	 * @see <?php echo $class_name ?>::get<?php echo $from_class_name ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>

	 */
<?php $used_methods[] = 'get' . StringFormat::titleCase($from_class_name) . 's'; ?>
	function get<?php echo StringFormat::titleCase($from_class_name) ?>s($extra = null) {
		return $this->get<?php echo StringFormat::titleCase($from_class_name) ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>($extra);
	}

<?php
		}

		if (!in_array('get' . StringFormat::titleCase($from_class_name) . 'sQuery', $used_methods)) {
?>
	/**
	  * Convenience function for <?php echo $class_name ?>::get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>Query
	  * @return Query
	  * @see <?php echo $class_name ?>::get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>Query
	  */
<?php $used_methods[] = 'get' . StringFormat::titleCase($from_class_name) . 'sQuery'; ?>
	function get<?php echo StringFormat::titleCase($from_class_name) ?>sQuery(Query $q = null) {
		return $this->getForeignObjectsQuery('<?php echo $from_table ?>', '<?php echo $from_column ?>','<?php echo $to_column ?>', $q);
	}

<?php
		}

		if (!in_array('delete' . StringFormat::titleCase($from_class_name) . 's', $used_methods)) {
?>
	/**
	  * Convenience function for <?php echo $class_name ?>::delete<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>

	  * @return int
	  * @see <?php echo $class_name ?>::delete<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>

	  */
<?php $used_methods[] = 'delete' . StringFormat::titleCase($from_class_name) . 's'; ?>
	function delete<?php echo StringFormat::titleCase($from_class_name) ?>s(Query $q = null) {
		return $this->delete<?php echo $from_class_name ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>($q);
	}

<?php
		}

		if (!in_array('count' . StringFormat::titleCase($from_class_name) . 's', $used_methods)) {
?>
	/**
	  * Convenience function for <?php echo $class_name ?>::count<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>

	  * @return int
	  * @see <?php echo $class_name ?>::count<?php echo $from_class_name ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>

	  */
<?php $used_methods[] = 'count' . StringFormat::titleCase($from_class_name) . 's'; ?>
	function count<?php echo StringFormat::titleCase($from_class_name) ?>s(Query $q = null) {
		return $this->count<?php echo StringFormat::titleCase($from_class_name) ?>sRelatedBy<?php echo StringFormat::titleCase($from_column) ?>($q);
	}

<?php
		}
	}
?>
	/**
	 * Returns true if the column values validate.
	 * @return bool
	 */
	function validate() {
		$this->_validationErrors = array();
<?php
	foreach ($fields as $key => $field){
		if (
			$field->isNotNull()
			&& !$field->isAutoIncrement()
			&& !$field->getDefaultValue()
			&& !$field->isPrimaryKey()
			&& !in_array(strtolower($field->getName()), array('created', 'updated'))
		) {
?>
		if (null === $this->get<?php echo $field->getName() ?>()) {
			$this->_validationErrors[] = '<?php echo $field->getName()?> must not be null';
		}
<?php
		}
	}
?>
		return 0 === count($this->_validationErrors);
	}

}