<?php echo '<?php' ?>

/**
 *		Created by Dan Blaisdell's DABL
 *		Do not alter base files, as they will be overwritten.
 *		To alter the objects, alter the extended classes in
 *		the 'models' folder.
 *
 */
<?php $used_functions = array(); ?>
abstract class base<?php echo $class_name ?> extends ApplicationModel {

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

	/**
	 * Array of all primary keys
	 * @var string[]
	 */
	protected static $_primaryKeys = array(
<?php if($PKs): ?>
<?php foreach($PKs as &$the_pk): ?>
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
	 * array of all column names
	 * @var string[]
	 */
	protected static $_columnNames = array(
<?php foreach($fields as $key => &$field): ?>
		'<?php echo $field->getName() ?>',
<?php endforeach ?>
	);

<?php
foreach($fields as $key => &$field):
	$default = $field->getDefaultValue() ? $field->getDefaultValue()->getValue() : null;
	// fix for MSSQL default value weirdness
	if($field->isNumericType())
		$default = trim($default, '()');
?>

	/**
	 * <?php echo $conn->quoteIdentifier($field->getName()) ?> <?php echo $field->getType() ?>
<?php if ($field->isNotNull()): ?> NOT NULL<?php endif ?>
<?php if (null!==$default): ?> DEFAULT <?php echo ctype_digit($default) ? $default : $conn->quote($default) ?><?php endif ?>

	 * @var <?php echo $field->getPhpType() ?>

	 */
<?
	if (($field->isNumericType()) && (!ctype_digit($default)) && (!$default)) $default = null;
?>
	protected $<?php echo $field->getName() ?><?php
if($field->isNumericType() && $default !== null)
	echo ' = '.$default;
elseif($default!==null && strtolower($default)!=='null')
	echo " = '".addslashes($default)."'"
?>;
<?php endforeach ?>

	/**
	 * Column Accessors and Mutators
	 */
<?php
foreach($fields as $key => &$field):
	$default = $field->getDefaultValue() ? $field->getDefaultValue()->getValue() : null;
	$method_name = $options['cap_method_names'] ? ucfirst($field->getName()) : $field->getName();
	$params = $field->isTemporalType() ? '$format = null' : '';
	$field_name = $field->getName();
	if($field->isTemporalType()){
		switch($field->getType()){
			case PropelTypes::TIMESTAMP:
				$formatter = $conn->getTimestampFormatter();
				break;
			case PropelTypes::DATE:
				$formatter = $conn->getDateFormatter();
				break;
			case PropelTypes::TIME:
				$formatter = $conn->getTimeFormatter();
				break;
		}
	}
?>

<?php // GETTERS AND SETTERS
	$used_functions[] = "get$method_name";
?>
	function get<?php echo $method_name ?>(<?php echo $params ?>) {
<?php if($field->isTemporalType()): ?>
		if($this-><?php echo $field_name ?>===null || !$format)
			return $this-><?php echo $field_name ?>;
		if(strpos($this-><?php echo $field_name ?>, '0000-00-00')===0)
			return null;
		return date($format, strtotime($this-><?php echo $field_name ?>));
<?php else: ?>
		return $this-><?php echo $field_name ?>;
<?php endif ?>
	}
<?php $used_functions[] = "set$method_name"; ?>
	function set<?php echo $method_name ?>($value) {
<?php if($field->isNumericType() || $field->isTemporalType()): ?>
		if($value==='')
			$value = null;
<?php if($field->isTemporalType()): ?>
		elseif($value!==null && $this->_formatDates)
			$value = date('<?php echo $formatter ?>', is_int($value) ? $value : strtotime($value));
<?php endif ?>
<?php endif ?>
<?php if($options['protect_not_null'] && $field->getName()!=$PK && $field->isNotNull()): ?>
		if($value===null)
			$value = <?php echo $field->isNumericType() ? '0' : "''" ?>;
<?php endif ?>
<?php if($field->getPdoType()==PDO::PARAM_INT): ?>
		elseif($value!==null)
			$value = (int)$value;
<?php endif ?>
		if($this-><?php echo $field_name ?> !== $value){
			$this->_modifiedColumns[] = '<?php echo $field_name ?>';
			$this-><?php echo $field_name ?> = $value;
		}
	}
<?php endforeach ?>

	/**
	 * @return DABLPDO
	 */
<?php $used_functions[] = "getConnection"; ?>
	static function getConnection() {
		return DBManager::getConnection('<?php echo $this->getConnectionName() ?>');
	}

	/**
	 * Returns String representation of table name
	 * @return string
	 */
<?php $used_functions[] = "getTableName"; ?>
	static function getTableName() {
		return <?php echo $class_name ?>::$_tableName;
	}

	/**
	 * Access to array of column names
	 * @return array
	 */
<?php $used_functions[] = "getColumnNames"; ?>
	static function getColumnNames() {
		return <?php echo $class_name ?>::$_columnNames;
	}

	/**
	 * @return bool
	 */
<?php $used_functions[] = "hasColumn"; ?>
	static function hasColumn($column_name) {
		static $lower_case_columns;
		if(!$lower_case_columns)
			$lower_case_columns = array_map('strtolower', <?php echo $class_name ?>::$_columnNames);
		return in_array(strtolower($column_name), $lower_case_columns);
	}

	/**
	 * Access to array of primary keys
	 * @return array
	 */
<?php $used_functions[] = "getPrimaryKeys"; ?>
	static function getPrimaryKeys() {
		return <?php echo $class_name ?>::$_primaryKeys;
	}

	/**
	 * Access to name of primary key
	 * @return array
	 */
<?php $used_functions[] = "getPrimaryKey"; ?>
	static function getPrimaryKey() {
		return <?php echo $class_name ?>::$_primaryKey;
	}

	/**
	 * Returns true if the primary key column for this table is auto-increment
	 * @return bool
	 */
<?php $used_functions[] = "isAutoIncrement"; ?>
	static function isAutoIncrement() {
		return <?php echo $class_name ?>::$_isAutoIncrement;
	}

	/**
	 * Searches the database for a row with the ID(primary key) that matches
	 * the one input.
	 * @return <?php echo $class_name ?>

	 */
<?php $used_functions[] = "retrieveByPK"; ?>
	static function retrieveByPK($the_pk) {
<?php if(count($PKs) > 1): ?>
		throw new Exception('This table has more than one primary key.  Use retrieveByPKs() instead.');
<?php else: ?>
		return <?php echo $class_name ?>::retrieveByPKs($the_pk);
<?php endif ?>
	}

	/**
	 * Searches the database for a row with the primary keys that match
	 * the ones input.
	 * @return <?php echo $class_name ?>

	 */
<?php $used_functions[] = "retrieveByPKs"; ?>
	static function retrieveByPKs(<?php foreach($PKs as $k => &$v): ?><?php if($k > 0): ?>, <?php endif ?>$<?php echo strtolower(str_replace('-', '_', $v)) ?><? endforeach ?>) {
<?php if(count($PKs)==0): ?>
		throw new Exception('This table does not have any primary keys.');
<?php else: ?>
<?php foreach($PKs as $k => &$v): ?>
		if($<?php echo strtolower(str_replace('-', '_', $v)) ?>===null)
			return null;
<?php endforeach ?>
<?php if (count($PKs)!=1): ?>
		$args = func_get_args();
<?php endif; ?>
		$pool_instance = <?php echo $class_name ?>::retrieveFromPool(<?php if(count($PKs)==1): ?>$<?php echo strtolower(str_replace('-', '_', $PK)) ?><?php else: ?>implode('-', $args)<?php endif ?>);
		if($pool_instance)
			return $pool_instance;
		$conn = <?php echo $class_name ?>::getConnection();
		$q = new Query;
<?php foreach($PKs as $k => &$v): ?>
		$q->add($conn->quoteIdentifier('<?php echo $v ?>'), $<?php echo strtolower(str_replace('-', '_', $v)) ?>);
<?php endforeach ?>
		$q->setLimit(1);
		return array_shift(<?php echo $class_name ?>::doSelect($q, true));
<?php endif ?>
	}
	
<?php
	foreach($this->getColumns($table_name) as $field) {
?>
	static function retrieveBy<?php echo $field->getName() ?>($value) {
<?php
		if($field->isPrimaryKey()) {
?>
		return <?php echo $class_name?>::retrieveByPK($value);
<?php
		} else {
?>
		return <?php echo $class_name ?>::retrieveByColumn('<?php echo $field->getName() ?>', $value);
<?php
		}
?>
	}

<?php
	}
?>
	static function retrieveByColumn($field, $value) {
		$conn = <?php echo $class_name ?>::getConnection();
		return array_shift(<?php echo $class_name ?>::doSelect(Query::create()->add($conn->quoteIdentifier($field), $value)->setLimit(1)->order('<?php echo $PK ?>')));
	}

	/**
	 * Populates and returns an instance of <?php echo $class_name ?> with the
	 * first result of a query.  If the query returns no results,
	 * returns null.
	 * @return <?php echo $class_name ?>

	 */
<?php $used_functions[] = "fetchSingle"; ?>
	static function fetchSingle($query_string, $write_cache = true) {
		return array_shift(<?php echo $class_name ?>::fetch($query_string, $write_cache));
	}

	/**
	 * Populates and returns an array of <?php echo $class_name ?> objects with the
	 * results of a query.  If the query returns no results,
	 * returns an empty Array.
	 * @return <?php echo $class_name ?>[]
	 */
<?php $used_functions[] = "fetch"; ?>
	static function fetch($query_string, $write_cache = false) {
		$conn = <?php echo $class_name ?>::getConnection();
		$result = $conn->query($query_string);
		return <?php echo $class_name ?>::fromResult($result, $class='<?php echo $class_name ?>', $write_cache);
	}

	/**
	 * Returns an array of <?php echo $class_name ?> objects from
	 * a PDOStatement(query result).
	 *
	 * @see BaseModel::fromResult
	 */
<?php $used_functions[] = "fromResult"; ?>
	static function fromResult(PDOStatement $result, $class='<?php echo $class_name ?>', $write_cache = false) {
		return baseModel::fromResult($result, $class, $write_cache);
	}

<?php $used_functions[] = "castInts"; ?>
	function castInts() {
<?php foreach($fields as $key => &$field): ?>
<?php if($field->getPdoType()==PDO::PARAM_INT): ?>
		$this-><?php echo $field->getName() ?> = ($this-><?php echo $field->getName() ?> === null) ? null : (int)$this-><?php echo $field->getName() ?>;
<?php endif ?>
<?php endforeach ?>
	}

	/**
	 * Add (or replace) to the instance pool.
	 *
	 * @param <?php echo $class_name ?> $object
	 * @return void
	 */
<?php $used_functions[] = "insertIntoPool"; ?>
	static function insertIntoPool(<?php echo $class_name ?> $object) {
<?php if(!$PKs): ?>
		// This table doesn't have primary keys, so there's no way to key the instance pool array
		return;
<?php endif ?>
		if (<?php echo $class_name ?>::$_instancePoolCount > <?php echo $class_name ?>::MAX_INSTANCE_POOL_SIZE) return;

		<?php echo $class_name ?>::$_instancePool[implode('-',$object->getPrimaryKeyValues())] = clone $object;
		++<?php echo $class_name ?>::$_instancePoolCount;
	}

	/**
	 * Return the cached instance from the pool.
	 *
	 * @param mixed $pk Primary Key
	 * @return <?php echo $class_name ?>

	 */
<?php $used_functions[] = "retrieveFromPool"; ?>
	static function retrieveFromPool($pk) {
		if($pk === null)
			return null;
		if (array_key_exists($pk, <?php echo $class_name ?>::$_instancePool))
			return clone <?php echo $class_name ?>::$_instancePool[$pk];

		return null;
	}

	/**
	 * Remove the object from the instance pool.
	 *
	 * @param mixed $object Object or PK to remove
	 * @return void
	 */
<?php $used_functions[] = "removeFromPool"; ?>
	static function removeFromPool($object) {
		$pk = is_object($object) ? implode('-', $object->getPrimaryKeyValues()) : $object;

		if (array_key_exists($pk, <?php echo $class_name ?>::$_instancePool)){
			unset(<?php echo $class_name ?>::$_instancePool[$pk]);
			--<?php echo $class_name ?>::$_instancePoolCount;
		}
	}

	/**
	 * Empty the instance pool.
	 *
	 * @return void
	 */
<?php $used_functions[] = "flushPool"; ?>
	static function flushPool() {
		<?php echo $class_name ?>::$_instancePool = array();
	}

	/**
	 * Returns an array of all <?php echo $class_name ?> objects in the database.
	 * $extra SQL can be appended to the query to LIMIT, SORT, and/or GROUP results.
	 * If there are no results, returns an empty Array.
	 * @param $extra string
	 * @return <?php echo $class_name ?>[]
	 */
<?php $used_functions[] = "getAll"; ?>
	static function getAll($extra = null, $write_cache = false) {
		$conn = <?php echo $class_name ?>::getConnection();
		$table_quoted = $conn->quoteIdentifier(<?php echo $class_name ?>::getTableName());
		return <?php echo $class_name ?>::fetch("SELECT * FROM $table_quoted $extra ", $write_cache);
	}

	/**
	 * @return int
	 */
<?php $used_functions[] = "doCount"; ?>
	static function doCount(Query $q) {
		$conn = <?php echo $class_name ?>::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), <?php echo $class_name ?>::getTableName())===false )
			$q->setTable(<?php echo $class_name ?>::getTableName());
		return $q->doCount($conn);
	}

	/**
	 * @param Query $q
	 * @param bool $dump_cache
	 * @return int
	 */
<?php $used_functions[] = "doDelete"; ?>
	static function doDelete(Query $q, $dump_cache=true) {
		$conn = <?php echo $class_name ?>::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), <?php echo $class_name ?>::getTableName())===false )
			$q->setTable(<?php echo $class_name ?>::getTableName());
		$result = $q->doDelete($conn);

		if ($dump_cache)
			<?php echo $class_name ?>::$_instancePool = array();

		return $result;
	}

	/**
	 * @param Query $q The Query object that creates the SELECT query string
	 * @param bool $write_cache Whether or not to store results in instance pool
	 * @param array $additional_classes Array of additional classes for fromResult to instantiate as properties
	 * @return <?php echo $class_name ?>[]
	 */
<?php $used_functions[] = "doSelect"; ?>
	static function doSelect(Query $q, $write_cache = false, $additional_classes = null) {
		$conn = <?php echo $class_name ?>::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), <?php echo $class_name ?>::getTableName())===false )
			$q->setTable(<?php echo $class_name ?>::getTableName());

		if(is_array($additional_classes)){
			array_unshift($additional_classes, '<?php echo $class_name ?>');
			$class = $additional_classes;
		} else {
			$class='<?php echo $class_name ?>';
		}
		return <?php echo $class_name ?>::fromResult($q->doSelect($conn), $class, $write_cache);
	}

<?php
$to_table_list = array();

foreach($this->getForeignKeysFromTable($table_name) as $r){
	$to_table = $r->getForeignTableName();
	if(isset($to_table_list[$to_table]))
		$to_table_list[$to_table] += 1;
	else
		$to_table_list[$to_table] = 1;
}

foreach($this->getForeignKeysFromTable($table_name) as $r):
	$to_table = $r->getForeignTableName();
	$to_class_name = $this->getModelName($to_table);
	$to_column = array_shift($r->getForeignColumns());
	$from_column = array_shift($r->getLocalColumns());
	$namedID = false;
	if(strpos($from_column, 'ID') === strlen($from_column) - 2) {
		$from_column_clean = rtrim($from_column, "ID");
		if(!in_array($from_column_clean, $fields)) {
			$namedID = true;
		} else {
			$this->warnings[] = "Can't create convenience functions for column $from_column: get$from_column_clean() and set$from_column_clean(), consider renaming column $from_column_clean";
		}
	}
?>
	protected $_<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>;
	
<?php
	if($namedID) {
?>
<?php $used_functions[] = "set$from_column_clean"; ?>
	function set<?php echo $from_column_clean ?>(<?php echo $to_class_name ?> $<?php echo $to_class_name ?> = null){
		$this->set<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>($<?php echo $to_class_name ?>);
	}

<?php
	}
?>
<?php $used_functions[] = "set$to_class_name" . "RelatedBy$from_column"; ?>
	function set<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>(<?php echo $to_class_name ?> $<?php echo $to_class_name ?> = null){
		if($<?php echo $to_class_name ?> === null)
			$this->set<?php echo $from_column ?>(null);
		else {
			if(!$<?php echo $to_class_name ?>->get<?php echo $to_column ?>())
				throw new Exception('Cannot connect a <?php echo $to_class_name ?> without a <?php echo $to_column ?>');
			$this->set<?echo $from_column ?>($<?php echo $to_class_name ?>->get<?php echo $to_column ?>());
		}
		if($this->getCacheResults())
			$this->_<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?> = $<?php echo $to_class_name ?>;
	}
<?php
	if($namedID) {
?>

	/**
	 * Returns a <?php echo $to_table ?> object with a <?php echo $to_column ?>

	 * that matches $this-><?php echo $from_column ?>.
	 * @return <?php echo $to_class_name ?>

	 */
<?php $used_functions[] = "get$from_column_clean"; ?>
	function get<?php echo $from_column_clean ?>(){
		return $this->get<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>();
	}
<?php
	}
?>
	
	/**
	 * Returns a <?php echo $to_table ?> object with a <?php echo $to_column ?>

	 * that matches $this-><?php echo $from_column ?>.
	 * @return <?php echo $to_class_name ?>

	 */
<?php $used_functions[] = "get$to_class_name" . "RelatedBy$from_column"; ?>
	function get<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>() {
		if($this->get<?echo $from_column ?>() === null)
			$result = null;
		else {
			if($this->getCacheResults() && $this->_<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?> !== null)
				return $this->_<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>;
<?php 
	$foreign_column = $this->database->getTable($to_table)->getColumn($to_column);
	if($foreign_column->isPrimaryKey()) {
?>
			$result = <?php echo $to_class_name ?>::retrieveByPK($this->get<?echo $from_column ?>());
<?php 
		} else {
?>
			$result = <?php echo $to_class_name ?>::retrieveBy<?php echo $from_column ?>($this->get<?echo $from_column ?>());
<?php } ?>
		}
		if($this->getCacheResults())
			$this->_<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?> = $result;
		return $result;
	}

<?php
	if($namedID) {
?>
<?php $used_functions[] = "doSelectJoin$from_column_clean"; ?>
	static function doSelectJoin<?php echo $from_column_clean ?>(Query $q, $write_cache = false, $join_type = Query::LEFT_JOIN) {
		return <?php echo $class_name ?>::doSelectJoin<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>($q, $write_cache, $join_type);
	}

<?php
	}
	if($to_table_list[$to_table] < 2) {
		if(!in_array("get$to_class_name", $used_functions)) {
?>
	/**
	 * Returns a <?php echo $to_table ?> object with a <?php echo $to_column ?>

	 * that matches $this-><?php echo $from_column ?>.
	 * @return <?php echo $to_class_name ?>

	 */
<?php $used_functions[] = "get$to_class_name"; ?>
	function get<?php echo $to_class_name ?>(){
		return $this->get<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>();
	}

<?
		}
		if(!in_array("set$to_class_name", $used_functions)) {

?>
<?php $used_functions[] = "set$to_class_name"; ?>
	function set<?php echo $to_class_name ?>(<?php echo $to_class_name ?> $<?php echo $to_class_name ?> = null){
		$this->set<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>($<?php echo $to_class_name ?>);
	}

<?
		}
	}
?>
	/**
	 * @return <?php echo $class_name ?>[]
	 */
<?php $used_functions[] = "doSelectJoin$to_class_name" . "RelatedBy$from_column"; ?>
	static function doSelectJoin<?php echo $to_class_name ?>RelatedBy<?php echo $from_column ?>(Query $q, $write_cache = false, $join_type = Query::LEFT_JOIN) {
		$columns = $q->getColumns();
		$alias = $q->getAlias();
		$this_table = $alias ? $alias : <?php echo $class_name ?>::getTableName();
		if(!$columns)
			$columns[] = $this_table.'.*';

		$to_table = <?php echo $to_class_name ?>::getTableName();
		$q->join($to_table, $this_table.'.<?php echo $from_column ?> = '.$to_table.'.<?php echo $to_column ?>', $join_type);
		$columns[] = $to_table.'.*';
		$q->setColumns($columns);

		return <?php echo $class_name ?>::doSelect($q, $write_cache, array('<?php echo $to_class_name ?>'));
	}

<?php endforeach ?>
	/**
	 * @return <?php echo $class_name ?>[]
	 */
<?php $used_functions[] = "doSelectJoinAll"; ?>
	static function doSelectJoinAll(Query $q, $write_cache = false, $join_type = Query::LEFT_JOIN) {
		$columns = $q->getColumns();
		$classes = array();
		$alias = $q->getAlias();
		$this_table = $alias ? $alias : <?php echo $class_name ?>::getTableName();
		if(!$columns)
			$columns[] = $this_table.'.*';
<?php
	foreach($this->getForeignKeysFromTable($table_name) as $r):
		$to_table = $r->getForeignTableName();
		$to_class_name = $this->getModelName($to_table);
		$to_column = array_shift($r->getForeignColumns());
		$from_column = array_shift($r->getLocalColumns());
?>

		$to_table = <?php echo $to_class_name ?>::getTableName();
		$q->join($to_table, $this_table.'.<?php echo $from_column ?> = '.$to_table.'.<?php echo $to_column ?>', $join_type);
		$columns[] = $to_table.'.*';
		$classes[] = '<?php echo $to_class_name ?>';
	<?php endforeach ?>

		$q->setColumns($columns);
		return <?php echo $class_name ?>::doSelect($q, $write_cache, $classes);
	}

<?php $used_functions[] = "getForeignObjectsQuery"; ?>
	/**
	 *
	 * @param string $tablename
	 * @param string $columnname
	 * @param Query $q
	 * @return Query
	 */
	protected function getForeignObjectsQuery($tablename, $columnname, $localcolumn, Query $q = null){
		$value = $this->{"get$localcolumn"}();
		if($value ===null)
			throw new Exception('NULL cannot be used to match keys.');
		$conn = $this->getConnection();
		$column = $conn->quoteIdentifier($columnname);
		if($q){
			$q = clone $q;
			$alias = $q->getAlias();
			if($alias && $q->getTableName()==$tablename)
				$column = "$alias.$column";
		}
		else
			$q = new Query;
		$q->add($column, $value);
		return $q;
	}

<?php
$from_table_list = array();

foreach($this->getForeignKeysToTable($table_name) as $r):
	$from_table = $r->getTableName();
	if(isset($from_table_list[$from_table]))
		$from_table_list[$from_table] += 1;
	else
		$from_table_list[$from_table] = 1;
	$from_class_name = $this->getModelName($from_table);
	$from_column = array_shift($r->getLocalColumns());
	$to_column = array_shift($r->getForeignColumns());
	$to_table = $r->getForeignTableName();
?>
	/**
	 * Returns a Query for selecting <?php echo $from_table ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return Query
	 */
<?php $used_functions[] = "get$from_class_name" . "sRelatedBy$from_column" . "Query"; ?>
	function get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>Query(Query $q = null) {
		return $this->getForeignObjectsQuery('<?php echo $from_table ?>', '<?php echo $from_column ?>', '<?php echo $to_column ?>', $q);
	}

	/**
	 * Returns the count of <?php echo $from_class_name ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return int
	 */
<?php $used_functions[] = "count$from_class_name" . "sRelatedBy$from_column"; ?>
	function count<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>(Query $q = null) {
		if($this->get<?php echo $to_column ?>()===null)
			return 0;
		return <?php echo $from_class_name ?>::doCount($this->get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>Query($q));
	}

	/**
	 * Deletes the <?php echo $from_table ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return int
	 */
<?php $used_functions[] = "delete$from_class_name" ."sRelatedBy$from_column"; ?>
	function delete<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>(Query $q = null) {
		if($this->get<?php echo $to_column ?>()===null)
			return 0;
		return <?php echo $from_class_name ?>::doDelete($this->get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>Query($q));
	}

	private $<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>_c = array();

	/**
	 * Returns an array of <?php echo $from_class_name ?> objects with a <?php echo $from_column ?>
	 * that matches $this-><?php echo $to_column ?>.
	 * When first called, this method will cache the result.
	 * After that, if $this-><?php echo $to_column ?> is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return <?php echo $from_class_name ?>[]
	 */
<?php $used_functions[] = "get$from_class_name" . "sRelatedBy$from_column"; ?>
	function get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>($extra = null) {
		if($this->get<?php echo $to_column ?>()===null)
			return array();

		if(!$extra || $extra instanceof Query)
			return <?php echo $from_class_name ?>::doSelect($this->get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>Query($extra));

		if(!$extra && $this->getCacheResults() && @$this-><?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>_c && !$this->isColumnModified('<?php echo $to_column ?>'))
			return $this-><?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>_c;

		$conn = $this->getConnection();
		$table_quoted = $conn->quoteIdentifier(<?php echo $from_class_name ?>::getTableName());
		$column_quoted = $conn->quoteIdentifier('<?php echo $from_column ?>');
		$<?php echo $from_table ?>s = <?php echo $from_class_name ?>::fetch("SELECT * FROM $table_quoted WHERE $column_quoted=".$conn->checkInput($this->get<?php echo $to_column ?>())." $extra");
		if($extra === null) $this-><?php echo $from_class_name ?>s_c = $<?php echo $from_table ?>s;
		return $<?php echo $from_table ?>s;
	}
<?php endforeach ?>
<?php
	foreach($this->getForeignKeysToTable($table_name) as $r){
		$from_table = $r->getTableName();

		$from_class_name = $this->getModelName($from_table);
		$from_column = array_shift($r->getLocalColumns());
		$to_column = array_shift($r->getForeignColumns());
		$to_table = $r->getForeignTableName();
		if($from_table_list[$from_table] < 2) {
			if(!in_array("get$from_class_name"."s", $used_functions)) {
?>
	/**
	 * Convenience function for <?php echo $class_name ?>::get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>
	 * @return <?php echo $from_class_name ?>[]
	 * @see <?php echo $class_name ?>::get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?> 
	 */
<?php $used_functions[] = "get$from_class_name"."s"; ?>
	function get<?php echo $from_class_name ?>s($extra = null){
		return $this->get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>($extra);
	}

<?
			}
			if(!in_array("get$from_class_name"."sQuery", $used_functions)) {
?>
	/**
	  * Convenience function for <?php echo $class_name ?>::get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>Query
	  * @return Query
	  * @see <?php echo $class_name ?>::get<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>Query
	  */
<?php $used_functions[] = "get$from_class_name" . "sQuery"; ?>
	function get<?php echo $from_class_name ?>sQuery(Query $q = null) {
		return $this->getForeignObjectsQuery('<?php echo $from_table ?>', '<?php echo $from_column ?>','<?php echo $to_column ?>', $q);
	}

<?
			}
			if(!in_array("delete$from_class_name" . "s", $used_functions)) {
?>
	/**
	  * Convenience function for <?php echo $class_name ?>::delete<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?> 
	  * @return int
	  * @see <?php echo $class_name ?>::delete<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?> 
	  */
<?php $used_functions[] = "delete$from_class_name" . "s"; ?>
	function delete<?php echo $from_class_name ?>s(Query $q = null) {
		return $this->delete<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>($q);
	}

<?
			}
			if(!in_array("count$from_class_name" . "s", $used_functions)) {
?>
	/**
	  * Convenience function for <?php echo $class_name ?>::count<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?> 
	  * @return int
	  * @see <?php echo $class_name ?>::count<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?> 
	  */
<?php $used_functions[] = "count$from_class_name" . "s"; ?>
	function count<?php echo $from_class_name ?>s(Query $q = null) {
		return $this->count<?php echo $from_class_name ?>sRelatedBy<?php echo $from_column ?>($q);
	}

<?
			}
		}
	}
?>
	/**
	 * Returns true if the column values validate.
	 * @return bool
	 */
	function validate() {
		$this->_validationErrors = array();
		$validation_passed = true;
<?php 
	foreach($fields as $key => &$field){
		if($field->isNotNull() && !$field->isAutoIncrement() && !$field->getDefaultValue() && !$field->isPrimaryKey() && !in_array($field->getName(), array('Created', 'Updated'))) {
?>
		if(!$this->get<?php echo $field->getName() ?>()) {
			$validation_passed = false;
			$this->_validationErrors[] = "<?php echo $field->getName()?> must not be null";
		}
<?php
		}
	}
?>
		return $validation_passed;
	}
}
