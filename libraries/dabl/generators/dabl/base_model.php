<?php echo '<?php' ?>

/**
 *		Created by Dan Blaisdell's DABL
 *		Do not alter base files, as they will be overwritten.
 *		To alter the objects, alter the extended clases in
 *		the 'tables' folder.
 *
 */

abstract class base<?php echo $class_name ?> extends BaseModel{

	/**
	 * Name of the table
	 */
	protected static $_tableName = '<?php echo $table_name ?>';

	/**
	 * Array of all primary keys
	 */
	protected static $_primaryKeys = array(
<?php if($PKs): ?>
<?php foreach($PKs as $thePK): ?>
		'<?php echo $thePK ?>',
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
	 * @var array
	 */
	protected static $_columnNames = array(
<?php foreach($fields as $key=>$field): ?>
		'<?php echo $field->getName() ?>',
<?php endforeach ?>
	);

<?php
foreach($fields as $key=>$field):
	$default = $field->getDefaultValue() ? $field->getDefaultValue()->getValue() : null;
?>
	protected $<?php echo $field->getName() ?><?php if($field->isNumericType() && $default !== NULL) echo ' = '.$default; elseif($default!==NULL && strtolower($default)!=='null') echo " = '$default'" ?>;
<?php endforeach ?>

	/**
	 * Column Accessors and Mutators
	 */
<?php
foreach($fields as $key=>$field):
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
	function get<?php echo $method_name ?>(<?php echo $params ?>) {
<?php if($field->isTemporalType()): ?>
		if($this-><?php echo $field_name ?>===null || !$format)
			return $this-><?php echo $field_name ?>;
		if(strpos($this-><?php echo $field_name ?>, "0000-00-00")===0)
			return null;
		return date($format, strtotime($this-><?php echo $field_name ?>));
<?php else: ?>
		return $this-><?php echo $field_name ?>;
<?php endif ?>
	}
	function set<?php echo $method_name ?>($value) {
<?php if($field->isNumericType() || $field->isTemporalType()): ?>
		if($value==="")
			$value = null;
<?php if($field->isTemporalType()): ?>
		elseif($value!==null && $this->_formatDates)
			$value = date('<?php echo $formatter ?>', strtotime($value));
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
	static function getConnection() {
		return DBManager::getConnection('<?php echo $this->getConnectionName() ?>');
	}

	/**
	 * Returns String representation of table name
	 * @return string
	 */
	static function getTableName() {
		return <?php echo $class_name ?>::$_tableName;
	}

	/**
	 * Access to array of column names
	 * @return array
	 */
	static function getColumnNames() {
		return <?php echo $class_name ?>::$_columnNames;
	}

	/**
	 * @return bool
	 */
	static function hasColumn($columnName) {
		return in_array(strtolower($columnName), array_map('strtolower', <?php echo $class_name ?>::getColumnNames()));
	}

	/**
	 * Access to array of primary keys
	 * @return array
	 */
	static function getPrimaryKeys() {
		return <?php echo $class_name ?>::$_primaryKeys;
	}

	/**
	 * Access to name of primary key
	 * @return array
	 */
	static function getPrimaryKey() {
		return <?php echo $class_name ?>::$_primaryKey;
	}

	/**
	 * Returns true if the primary key column for this table is auto-increment
	 * @return bool
	 */
	static function isAutoIncrement() {
		return <?php echo $class_name ?>::$_isAutoIncrement;
	}

	/**
	 * Searches the database for a row with the ID(primary key) that matches
	 * the one input.
	 * @return <?php echo $class_name ?>
	 
	 */
	static function retrieveByPK($thePK) {
		if($thePK===null)return null;
		$PKs = <?php echo $class_name ?>::getPrimaryKeys();
		if(count($PKs)>1)
			throw new Exception('This table has more than one primary key.  Use retrieveByPKs() instead.');
		elseif(count($PKs)==0)
			throw new Exception('This table does not have a primary key.');
		$q = new Query;
		$conn = <?php echo $class_name ?>::getConnection();
		$pkColumn = $conn->quoteIdentifier($PKs[0]);
		$q->add($pkColumn, $thePK);
		$q->setLimit(1);
		return array_shift(<?php echo $class_name ?>::doSelect($q));
	}

	/**
	 * Searches the database for a row with the primary keys that match
	 * the ones input.
	 * @return <?php echo $class_name ?>
	 
	 */
	static function retrieveByPKs(<?php foreach($PKs as $k=>$v): ?><?php if($k > 0): ?>, <?php endif ?>$PK<?php echo $k ?><? endforeach ?>) {
		$conn = <?php echo $class_name ?>::getConnection();
		$tableWrapped = $conn->quoteIdentifier(<?php echo $class_name ?>::getTableName());
<?php foreach($PKs as $k=>$v): ?>
		if($PK<?php echo $k ?>===null)return null;
<? endforeach ?>
		$query_string = "SELECT * FROM $tableWrapped WHERE <?php foreach($PKs as $k=>$v): ?><?php if($k > 0): ?> AND <?php endif ?>".$conn->quoteIdentifier('<?php echo $v ?>')." = ".$conn->checkInput($PK<?php echo $k ?>)."<?php endforeach ?>";
		$conn->applyLimit($query_string, 0, 1);
		return <?php echo $class_name ?>::fetchSingle($query_string);
	}

	/**
	 * Populates and returns an instance of <?php echo $class_name ?> with the
	 * first result of a query.  If the query returns no results,
	 * returns null.
	 * @return <?php echo $class_name ?>
	 
	 */
	static function fetchSingle($query_string) {
		return array_shift(<?php echo $class_name ?>::fetch($query_string));
	}

	/**
	 * Populates and returns an array of <?php echo $class_name ?> objects with the
	 * results of a query.  If the query returns no results,
	 * returns an empty Array.
	 * @return <?php echo $class_name ?>[]
	 */
	static function fetch($query_string) {
		$conn = <?php echo $class_name ?>::getConnection();
		$result = $conn->query($query_string);
		return <?php echo $class_name ?>::fromResult($result);
	}

	/**
	 * Returns an array of <?php echo $class_name ?> objects from the rows of a PDOStatement(query result)
	 * @return <?php echo $class_name ?>[]
	 */
	static function fromResult(PDOStatement $result, $class = '<?php echo $class_name ?>') {
		$objects = array();
		while($object = $result->fetchObject($class)){
			$object->castInts();
			$object->setNew(false);
			$objects[] = $object;
		}
		return $objects;
	}

	function castInts() {
<?php foreach($fields as $key => $field): ?>
<?php if($field->getPdoType()==PDO::PARAM_INT): ?>
		$this-><?php echo $field->getName() ?> = ($this-><?php echo $field->getName() ?> === null) ? null : (int)$this-><?php echo $field->getName() ?>;
<?php endif ?>
<?php endforeach ?>
	}

	/**
	 * Returns an array of all <?php echo $class_name ?> objects in the database.
	 * $extra SQL can be appended to the query to LIMIT, SORT, and/or GROUP results.
	 * If there are no results, returns an empty Array.
	 * @param $extra string
	 * @return <?php echo $class_name ?>[]
	 */
	static function getAll($extra = null) {
		$conn = <?php echo $class_name ?>::getConnection();
		$tableWrapped = $conn->quoteIdentifier(<?php echo $class_name ?>::getTableName());
		return <?php echo $class_name ?>::fetch("SELECT * FROM $tableWrapped $extra ");
	}

	/**
	 * @return int
	 */
	static function doCount(Query $q) {
		$conn = <?php echo $class_name ?>::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), <?php echo $class_name ?>::getTableName())===false )
			$q->setTable(<?php echo $class_name ?>::getTableName());
		return $q->doCount($conn);
	}

	/**
	 * @return int
	 */
	static function doDelete(Query $q) {
		$conn = <?php echo $class_name ?>::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), <?php echo $class_name ?>::getTableName())===false )
			$q->setTable(<?php echo $class_name ?>::getTableName());
		return $q->doDelete($conn);
	}

	/**
	 * @return <?php echo $class_name ?>[]
	 */
	static function doSelect(Query $q){
		$conn = <?php echo $class_name ?>::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), <?php echo $class_name ?>::getTableName())===false )
			$q->setTable(<?php echo $class_name ?>::getTableName());
		return <?php echo $class_name ?>::fromResult($q->doSelect($conn));
	}

<?php
$used_from = array();
foreach($this->getForeignKeysFromTable($table_name) as $r):
	$to_table = $r['to_table'];
	$to_className = $this->getModelName($to_table);
	$to_column = $r['to_column'];
	$from_column = $r['from_column'];
	if(@$used_from[$to_table]) continue;
	$used_from[$to_table] = $from_column;
?>
	/**
	 * @var <?php echo $to_className ?>
	 */
	private $<?php echo $to_className ?>_c;

	/**
	 * Returns a <?php echo $to_table ?> object with a <?php echo $to_column ?>
	 * that matches $this-><?php echo $from_column ?>.
	 * When first called, this method will cache the result.
	 * After that, if $this-><?php echo $from_column ?> is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time.
	 * @return <?php echo $to_className ?>
	 */
	function get<?php echo $to_className ?>() {
		if($this->get<?echo $from_column ?>()===null)
			return null;
		$conn = $this->getConnection();
		$columnQuoted = $conn->quoteIdentifier('<?php echo $to_column ?>');
		$tableQuoted = $conn->quoteIdentifier(<?php echo $to_className ?>::getTableName());
		if($this->getCacheResults() && @$this-><?php echo $to_className ?>_c && !$this->isColumnModified('<?php echo $from_column ?>'))return $this-><?php echo $to_className ?>_c;
		$query_string = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->get<?php echo $from_column ?>());
		$conn->applyLimit($query_string, 0, 1);
		return $this-><?php echo $to_className ?>_c = <?php echo $to_className ?>::fetchSingle($query_string);
	}
<?php endforeach ?>

<?php
$used_to = array();
foreach($this->getForeignKeysToTable($table_name) as $r):
	$from_table = $r['from_table'];
	$from_class_name = $this->getModelName($from_table);
	$from_column = $r['from_column'];
	$to_column = $r['to_column'];
	if(@$used_to[$from_table]){
		$this->warnings[] = "WARNING: <strong>$table_name.$to_column</strong> used by more than one foreign key in table: <strong>$from_table</strong>. Methods created for <strong>$from_table.".$used_to[$from_table]."</strong> only.";
		continue;
	}
	$used_to[$from_table]=$from_column;
?>
	/**
	 * Returns a Query for selecting <?php echo $from_table ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return Query
	 */
	function get<?php echo $from_class_name ?>sQuery(Query $q = null) {
		if($this->get<?php echo $to_column ?>()===null)
			throw new Exception("NULL cannot be used to match keys.");
		$conn = $this->getConnection();
		$column = $conn->quoteIdentifier("<?php echo $from_column ?>");
		if($q){
			$q = clone $q;
			$alias = $q->getAlias();
			if($alias && $q->getTableName()=="<?php echo $from_table ?>")
				$column = "$alias.$column";
		}
		else
			$q = new Query;
		$q->add($column, $this->get<?php echo $to_column ?>());
		return $q;
	}

	/**
	 * Returns the count of <?php echo $from_class_name ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return int
	 */
	function count<?php echo $from_class_name ?>s(Query $q = null) {
		if($this->get<?php echo $to_column ?>()===null)
			return 0;
		return <?php echo $from_class_name ?>::doCount($this->get<?php echo $from_class_name ?>sQuery($q));
	}

	/**
	 * Deletes the <?php echo $from_table ?> Objects(rows) from the <?php echo $from_table ?> table
	 * with a <?php echo $from_column ?> that matches $this-><?php echo $to_column ?>.
	 * @return int
	 */
	function delete<?php echo $from_class_name ?>s(Query $q = null) {
		if($this->get<?php echo $to_column ?>()===null)
			return 0;
		return <?php echo $from_class_name ?>::doDelete($this->get<?php echo $from_class_name ?>sQuery($q));
	}

	private $<?php echo $from_class_name ?>s_c = array();

	/**
	 * Returns an array of <?php echo $from_class_name ?> objects with a <?php echo $from_column ?>
	 * that matches $this-><?php echo $to_column ?>.
	 * When first called, this method will cache the result.
	 * After that, if $this-><?php echo $to_column ?> is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return <?php echo $from_class_name ?>[]
	 */
	function get<?php echo $from_class_name ?>s($extra=NULL) {
		if($this->get<?php echo $to_column ?>()===null)
			return array();

		if(!$extra || $extra instanceof Query)
			return <?php echo $from_class_name ?>::doSelect($this->get<?php echo $from_class_name ?>sQuery($extra));

		if(!$extra && $this->getCacheResults() && @$this-><?php echo $from_class_name ?>s_c && !$this->isColumnModified("<?php echo $to_column ?>"))
			return $this-><?php echo $from_class_name ?>s_c;

		$conn = $this->getConnection();
		$tableQuoted = $conn->quoteIdentifier(<?php echo $from_class_name ?>::getTableName());
		$columnQuoted = $conn->quoteIdentifier("<?php echo $from_column ?>");
		$query_string = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->get<?php echo $to_column ?>())." $extra";
		$<?php echo $from_table ?>s = <?php echo $from_class_name ?>::fetch($query_string);
		if(!$extra)$this-><?php echo $from_class_name ?>s_c = $<?php echo $from_table ?>s;
		return $<?php echo $from_table ?>s;
	}
<?php endforeach ?>

}