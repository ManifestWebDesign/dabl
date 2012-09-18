<?php echo '<?php' ?>


abstract class base<?php echo $model_name ?>Query extends Query {

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
		$records = <?php echo $model_name ?>::doSelect($this);
		return array_shift($records);
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
		if (null === $value && is_array($column) && Model::isTemporalType($type)) {
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
		if (null === $value && is_array($column) && Model::isTemporalType($type)) {
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
		foreach (array('', 'Not', 'Like', 'NotLike', 'Greater', 'GreaterEqual', 'Less', 'LessEqual', 'Null', 'NotNull', 'Between', 'BeginsWith', 'EndsWith', 'Contains') as $oper):
			switch($oper) {
				case 'Null':
				case 'NotNull':
					$params = '';
					break;
				case 'Between':
					$params = $value_param . ', $from, $to';
					break;
				default:
					$params = $value_param;
					break;
			}
?>
	/**
	 * @return <?php echo $model_name ?>Query
	 */
	function <?php echo $verb ?><?php echo $php_name ?><?php echo $oper ?>(<?php echo $params ?>) {
		return $this-><?php if($verb == 'and' && $oper == '') echo 'addAnd'; else echo $verb; ?><?php echo $oper ?>(<?php echo $constant ?><?php if ($params) echo ', ' . $params ?>);
	}

<?php endforeach;endforeach; ?>

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