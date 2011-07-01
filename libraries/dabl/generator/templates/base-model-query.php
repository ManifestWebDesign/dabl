<?php echo '<?php' ?>


abstract class Base<?php echo $model_name ?>Query extends Query {

	function __construct($table_name = null, $alias = null) {
		if (null === $table_name) {
			$table_name = <?php echo $model_name ?>::getTableName();
		}
		return parent::__construct($table_name, $alias);
	}

	/**
	 * Returns new instance of self by passing arguments directly to constructor.
	 * @param string $alias
	 * @return <?php echo $model_name ?>Query
	 */
	static function create($table_name = null, $alias = null) {
		return new <?php echo $model_name ?>Query($table_name, $alias);
	}

	/**
	 * @return <?php echo $model_name ?>[]
	 */
	function select() {
		return <?php echo $model_name ?>::doSelect($this);
	}

	/**
	 * @return <?php echo $model_name ?>

	 */
	function selectOne() {
		return array_shift(<?php echo $model_name ?>::doSelect($this));
	}

	/**
	 * @return int
	 */
	function delete(){
		return <?php echo $model_name ?>::doDelete($this);
	}

	/**
	 * @return int
	 */
	function count(){
		return <?php echo $model_name ?>::doCount($this);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function addAnd($column, $value=null, $operator=self::EQUAL, $quote = null, $type = null) {
		if (null !== $type && <?php echo $model_name ?>::isTemporalType($type)) {
			$value = <?php echo $model_name ?>::coerceTemporalValue($value, $type);
		}
		if (null === $value && is_array($column) && BaseModel::isTemporalType($type)) {
			$column = <?php echo $model_name ?>::coerceTemporalValue($column, $type);
		}
		return parent::addAnd($column, $value, $operator, $quote);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function addOr($column, $value=null, $operator=self::EQUAL, $quote = null, $type = null) {
		if (null !== $type && <?php echo $model_name ?>::isTemporalType($type)) {
			$value = <?php echo $model_name ?>::coerceTemporalValue($value, $type);
		}
		if (null === $value && is_array($column) && BaseModel::isTemporalType($type)) {
			$column = <?php echo $model_name ?>::coerceTemporalValue($column, $type);
		}
		return parent::addOr($column, $value, $operator, $quote);
	}

<?php
foreach ($columns as $key => &$column):
	$constant = $model_name . '::' . StringFormat::constant($column->getName());
	$php_name = StringFormat::titleCase($column->getName());
	$column_type = $model_name . '::COLUMN_TYPE_' . $column->getType();
	$value_param = '$' . StringFormat::variable($column->getType());
	foreach(array('and', 'or') as $verb):
		$verb_method = 'add' . ucFirst($verb);
?>
	/**
	 * Alias of {@link <?php echo $verb_method ?>()}
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>(<?php echo $value_param ?>) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, <?php echo $value_param ?>, null, null, <?php echo $column_type ?>);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>Not(<?php echo $value_param ?>) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, <?php echo $value_param ?>, self::NOT_EQUAL, null, <?php echo $column_type ?>);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>Like(<?php echo $value_param ?>) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, <?php echo $value_param ?>, self::LIKE);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>NotLike(<?php echo $value_param ?>) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, <?php echo $value_param ?>, self::NOT_LIKE);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>Greater(<?php echo $value_param ?>) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, <?php echo $value_param ?>, self::GREATER_THAN, null, <?php echo $column_type ?>);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>GreaterEqual(<?php echo $value_param ?>) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, <?php echo $value_param ?>, self::GREATER_EQUAL, null, <?php echo $column_type ?>);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>Less(<?php echo $value_param ?>) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, <?php echo $value_param ?>, self::LESS_THAN, null, <?php echo $column_type ?>);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>LessEqual(<?php echo $value_param ?>) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, <?php echo $value_param ?>, self::LESS_EQUAL, null, <?php echo $column_type ?>);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>Between($column, $min, $max) {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, array($min, $max), self::BETWEEN, null, <?php echo $column_type ?>);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>Null() {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, null);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?>NotNull() {
		return $this-><?php echo $verb_method ?>(<?php echo $constant ?>, null, self::NOT_EQUAL);
	}

<?php endforeach ?>

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function orderBy<?php echo $php_name ?>Asc() {
		return $this->orderBy(<?php echo $constant ?>, self::ASC);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function orderBy<?php echo $php_name ?>Desc() {
		return $this->orderBy(<?php echo $constant ?>, self::DESC);
	}

	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function groupBy<?php echo $php_name ?>() {
		return $this->groupBy(<?php echo $constant ?>);
	}

<?php endforeach ?>

}