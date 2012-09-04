<?php

/**
 * Used to build query strings using OOP
 */
class Query {
	const ACTION_COUNT = 'COUNT';
	const ACTION_DELETE = 'DELETE';
	const ACTION_SELECT = 'SELECT';
	const ACTION_UPDATE = 'UPDATE';

	// Comparison types
	const EQUAL = '=';
	const NOT_EQUAL = '<>';
	const ALT_NOT_EQUAL = '!=';
	const GREATER_THAN = '>';
	const LESS_THAN = '<';
	const GREATER_EQUAL = '>=';
	const LESS_EQUAL = '<=';
	const LIKE = 'LIKE';
	const BEGINS_WITH = 'BEGINS_WITH';
	const ENDS_WITH = 'ENDS_WITH';
	const CONTAINS = 'CONTAINS';
	const NOT_LIKE = 'NOT LIKE';
	const CUSTOM = 'CUSTOM';
	const DISTINCT = 'DISTINCT';
	const IN = 'IN';
	const NOT_IN = 'NOT IN';
	const ALL = 'ALL';
	const IS_NULL = 'IS NULL';
	const IS_NOT_NULL = 'IS NOT NULL';
	const BETWEEN = 'BETWEEN';

	// Comparison type for update
	const CUSTOM_EQUAL = 'CUSTOM_EQUAL';

	// PostgreSQL comparison types
	const ILIKE = 'ILIKE';
	const NOT_ILIKE = 'NOT ILIKE';

	// JOIN TYPES
	const JOIN = 'JOIN';
	const LEFT_JOIN = 'LEFT JOIN';
	const RIGHT_JOIN = 'RIGHT JOIN';
	const INNER_JOIN = 'INNER JOIN';
	const OUTER_JOIN = 'OUTER JOIN';

	// Binary AND
	const BINARY_AND = '&';

	// Binary OR
	const BINARY_OR = '|';

	// 'Order by' qualifiers
	const ASC = 'ASC';
	const DESC = 'DESC';

	private $_action = self::ACTION_SELECT;
	/**
	 * @var array
	 */
	private $_columns = array();
	/**
	 * @var mixed
	 */
	private $_table;
	/**
	 * @var string
	 */
	private $_tableAlias;
	/**
	 * @var array
	 */
	private $_extraTables = array();
	/**
	 * @var QueryJoin[]
	 */
	private $_joins = array();
	/**
	 * @var Condition
	 */
	private $_where;
	/**
	 * @var array
	 */
	private $_orders = array();
	/**
	 * @var array
	 */
	private $_groups = array();
	/**
	 * @var Condition
	 */
	private $_having;
	/**
	 * @var int
	 */
	private $_limit;
	/**
	 * @var int
	 */
	private $_offset = 0;
	/**
	 * @var bool
	 */
	private $_distinct = false;

	private $_updateColumnValues;

	/**
	 * Creates new instance of Query, parameters will be passed to the
	 * setTable() method.
	 * @return self
	 * @param $table_name Mixed[optional]
	 * @param $alias String[optional]
	 */
	function __construct($table_name = null, $alias = null) {
		$this->setWhere(new Condition);
		$this->setTable($table_name, $alias);
		return $this;
	}

	function __clone() {
		if ($this->_where instanceof Condition) {
			$this->_where = clone $this->_where;
		}
		if ($this->_having instanceof Condition) {
			$this->_having = clone $this->_having;
		}
		foreach ($this->_joins as $key => $join) {
			$this->_joins[$key] = clone $join;
		}
	}

	/**
	 * Returns new instance of self by passing arguments directly to constructor.
	 * @param mixed $table_name
	 * @param string $alias
	 * @return Query
	 */
	static function create($table_name = null, $alias = null) {
		return new self($table_name, $alias);
	}

	/**
	 * Specify whether to select only distinct rows
	 * @param Bool $bool
	 */
	function setDistinct($bool = true) {
		$this->_distinct = (bool) $bool;
		return $this;
	}

	/**
	 * Sets the action of the query.  Should be SELECT, DELETE, or COUNT.
	 * @return Query
	 * @param $action String
	 */
	function setAction($action) {
		$this->_action = strtoupper($action);
		return $this;
	}

	/**
	 * Returns the action of the query.  Should be SELECT, DELETE, or COUNT.
	 * @return String
	 */
	function getAction() {
		return $this->_action;
	}

	/**
	 * Add a column to the list of columns to select.  If unused, defaults to *.
	 *
	 * {@example libraries/dabl/database/query/Query_addColumn.php}
	 *
	 * @param String $column_name
	 * @return Query
	 */
	function addColumn($column_name, $alias = null) {
		if ($alias) {
			$column_name .= ' AS "' . $alias . '"';
		}
		$this->_columns[$column_name] = $column_name;
		return $this;
	}

	/**
	 * Set array of strings of columns to be selected
	 * @param array $columns_array
	 * @return Query
	 */
	function setColumns($columns_array) {
		$this->_columns = $columns_array;
		return $this;
	}

	/**
	 * Return array of columns to be selected
	 * @return array
	 */
	function getColumns() {
		return $this->_columns;
	}

	/**
	 * Set array of strings of groups to be selected
	 * @param array $groups_array
	 * @return Query
	 */
	function setGroups($groups_array) {
		$this->_groups = $groups_array;
		return $this;
	}

	/**
	 * Return array of groups to be selected
	 * @return array
	 */
	function getGroups() {
		return $this->_groups;
	}

	/**
	 * Sets the table to be queried. This can be a string table name
	 * or an instance of Query if you would like to nest queries.
	 * This function also supports arbitrary SQL.
	 *
	 * @param String|Query $table_name Name of the table to add, or sub-Query
	 * @param String[optional] $alias Alias for the table
	 * @return Query
	 */
	function setTable($table_name, $alias = null) {
		if ($table_name instanceof Query) {
			if (!$alias) {
				throw new Exception('The nested query must have an alias.');
			}
			$table_name = clone $table_name;
		} elseif (null === $alias) {
			$space = strrpos($table_name, ' ');
			$as = strrpos(strtoupper($table_name), ' AS ');
			if ($as != $space - 3) {
				$as = false;
			}
			if ($space) {
				$alias = trim(substr($table_name, $space + 1));
				$table_name = trim(substr($table_name, 0, $as === false ? $space : $as));
			}
		}

		if ($alias) {
			$this->setAlias($alias);
		}

		$this->_table = $table_name;
		return $this;
	}

	/**
	 * Returns a String representation of the table being queried,
	 * NOT including its alias.
	 *
	 * @return String
	 */
	function getTable() {
		return $this->_table;
	}

	function setAlias($alias) {
		$this->_tableAlias = $alias;
		return $this;
	}

	/**
	 * Returns a String of the alias of the table being queried,
	 * if present.
	 *
	 * @return String
	 */
	function getAlias() {
		return $this->_tableAlias;
	}

	/**
	 * @param type $table_name
	 * @param type $alias
	 * @return Query
	 */
	function addTable($table_name, $alias = null) {
		if ($table_name instanceof Query) {
			if (!$alias) {
				throw new Exception('The nested query must have an alias.');
			}
			$table_name = clone $table_name;
		} elseif (null === $alias) {
			// find the last space in the string
			$space = strrpos($table_name, ' ');
			if ($space) {
				$table_name = substr($table_name, 0, $space + 1);
				$alias = substr($table_name, $space);
			}
			$alias = $table_name;
		}

		$this->_extraTables[$alias] = $table_name;
		return $this;
	}

	/**
	 * Provide the Condition object to generate the WHERE clause of
	 * the query.
	 *
	 * @param Condition $w
	 * @return Query
	 */
	function setWhere(Condition $w) {
		$this->_where = $w;
		return $this;
	}

	/**
	 * Returns the Condition object that generates the WHERE clause
	 * of the query.
	 *
	 * @return Condition
	 */
	function getWhere() {
		return $this->_where;
	}

	/**
	 * Add a JOIN to the query.
	 *
	 * @todo Support the ON clause being NULL correctly
	 * @param string|Query $table_or_column Table to join on
	 * @param string|Condition $on_clause_or_column ON clause to join with
	 * @param string $join_type Type of JOIN to perform
	 * @return Query
	 */
	function addJoin($table_or_column, $on_clause_or_column = null, $join_type = self::JOIN) {
		if ($table_or_column instanceof QueryJoin) {
			$this->_joins[] = clone $table_or_column;
			return $this;
		}

		if (null === $on_clause_or_column) {
			if ($join_type == self::JOIN || $join_type == self::INNER_JOIN) {
				$this->addTable($table_or_column);
				return $this;
			}
			$on_clause_or_column = '1 = 1';
		}

		$this->_joins[] = new QueryJoin($table_or_column, $on_clause_or_column, $join_type);
		return $this;
	}

	/**
	 * Alias of {@link addJoin()}.
	 * @return Query
	 */
	function join($table_or_column, $on_clause_or_column = null, $join_type = self::JOIN) {
		return $this->addJoin($table_or_column, $on_clause_or_column, $join_type);
	}

	/**
	 * @param mixed $table_or_column
	 * @param mixed $on_clause_or_column
	 * @return Query
	 */
	function innerJoin($table_or_column, $on_clause_or_column = null) {
		return $this->addJoin($table_or_column, $on_clause_or_column, self::INNER_JOIN);
	}

	/**
	 * @param mixed $table_or_column
	 * @param mixed $on_clause_or_column
	 * @return Query
	 */
	function leftJoin($table_or_column, $on_clause_or_column = null) {
		return $this->addJoin($table_or_column, $on_clause_or_column, self::LEFT_JOIN);
	}

	/**
	 * @param mixed $table_or_column
	 * @param mixed $on_clause_or_column
	 * @return Query
	 */
	function rightJoin($table_or_column, $on_clause_or_column = null) {
		return $this->addJoin($table_or_column, $on_clause_or_column, self::RIGHT_JOIN);
	}

	/**
	 * @param mixed $table_or_column
	 * @param mixed $on_clause_or_column
	 * @return Query
	 */
	function outerJoin($table_or_column, $on_clause_or_column = null) {
		return $this->addJoin($table_or_column, $on_clause_or_column, self::OUTER_JOIN);
	}

	/**
	 * @return QueryJoin[]
	 */
	function getJoins() {
		return $this->_joins;
	}

	/**
	 * @param type $joins
	 * @return Query
	 */
	function setJoins($joins) {
		$this->_joins = $joins;
		return $this;
	}

	/**
	 * Shortcut to adding an AND statement to the Query's WHERE Condition.
	 * @return Query
	 * @param $column Mixed
	 * @param $value Mixed[optional]
	 * @param $operator String[optional]
	 * @param $quote Int[optional]
	 */
	function addAnd($column, $value = null, $operator = self::EQUAL, $quote = null) {
		if (func_num_args() === 1) {
			$this->_where->addAnd($column);
		} else {
			$this->_where->addAnd($column, $value, $operator, $quote);
		}
		return $this;
	}

	/**
	 * Alias of {@link addAnd()}
	 * @return Query
	 */
	function add($column, $value = null, $operator = self::EQUAL, $quote = null) {
		if (func_num_args() === 1) {
			return $this->addAnd($column);
		} else {
			return $this->addAnd($column, $value, $operator, $quote);
		}
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andNot($column, $value) {
		$this->_where->andNot($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andLike($column, $value) {
		$this->_where->andLike($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andNotLike($column, $value) {
		$this->_where->andNotLike($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andGreater($column, $value) {
		$this->_where->andGreater($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andGreaterEqual($column, $value) {
		$this->_where->andGreaterEqual($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andLess($column, $value) {
		$this->_where->andLess($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andLessEqual($column, $value) {
		$this->_where->andLessEqual($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @return Query
	 */
	function andNull($column) {
		$this->_where->andNull($column);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @return Query
	 */
	function andNotNull($column) {
		$this->_where->andNotNull($column);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $from
	 * @param mixed $to
	 * @return Query
	 */
	function andBetween($column, $from, $to) {
		$this->_where->andBetween($column, $from, $to);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andBeginsWith($column, $value) {
		$this->_where->andBeginsWith($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andEndsWith($column, $value) {
		$this->_where->andEndsWith($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function andContains($column, $value) {
		$this->_where->andContains($column, $value);
		return $this;
	}

	/**
	 * Shortcut to adding an OR statement to the Query's WHERE Condition.
	 * @return Query
	 * @param $column Mixed
	 * @param $value Mixed[optional]
	 * @param $operator String[optional]
	 * @param $quote Int[optional]
	 */
	function addOr($column, $value = null, $operator = self::EQUAL, $quote = null) {
		if (func_num_args() === 1) {
			$this->_where->addOr($column);
		} else {
			$this->_where->addOr($column, $value, $operator, $quote);
		}
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orNot($column, $value) {
		$this->_where->orNot($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orLike($column, $value) {
		$this->_where->orLike($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orNotLike($column, $value) {
		$this->_where->orNotLike($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orGreater($column, $value) {
		$this->_where->orGreater($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orGreaterEqual($column, $value) {
		$this->_where->orGreaterEqual($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orLess($column, $value) {
		$this->_where->orLess($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orLessEqual($column, $value) {
		$this->_where->orLessEqual($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @return Query
	 */
	function orNull($column) {
		$this->_where->orNull($column);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @return Query
	 */
	function orNotNull($column) {
		$this->_where->orNotNull($column);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $from
	 * @param mixed $to
	 * @return Query
	 */
	function orBetween($column, $from, $to) {
		$this->_where->orBetween($column, $from, $to);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orBeginsWith($column, $value) {
		$this->_where->orBeginsWith($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orEndsWith($column, $value) {
		$this->_where->orEndsWith($column, $value);
		return $this;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Query
	 */
	function orContains($column, $value) {
		$this->_where->orContains($column, $value);
		return $this;
	}

	/**
	 * Shortcut to addGroup() method
	 * @return Query
	 */
	function groupBy($column) {
		$this->_groups[] = $column;
		return $this;
	}

	/**
	 * Shortcut to addGroup() method
	 * @return Query
	 */
	final function group($column) {
		return $this->groupBy($column);
	}

	/**
	 * Adds a clolumn to GROUP BY
	 * @return Query
	 * @param $column String
	 */
	final function addGroup($column) {
		return $this->groupBy($column);
	}

	/**
	 * Provide the Condition object to generate the HAVING clause of the query
	 * @return Query
	 * @param $w Condition
	 */
	function setHaving(Condition $where) {
		$this->_having = $where;
		return $this;
	}

	/**
	 * Returns the Condition object that generates the HAVING clause of the query
	 * @return Condition
	 */
	function getHaving() {
		return $this->_having;
	}

	/**
	 * Shortcut for addOrder()
	 * @return Query
	 */
	function orderBy($column, $dir = null) {
		if (null !== $dir && '' !== $dir) {
			$dir = strtoupper($dir);
			if ($dir !== self::ASC && $dir !== self::DESC) {
				throw new Exception("$dir is not a valid sorting direction.");
			}
			$column .= ' ' . $dir;
		}
		$this->_orders[] = trim($column);
		return $this;
	}

	/**
	 * Shortcut for addOrder()
	 * @return Query
	 */
	final function order($column, $dir = null) {
		return $this->orderBy($column, $dir);
	}

	/**
	 * Adds a column to ORDER BY in the form of "COLUMN DIRECTION"
	 * @return Query
	 * @param $column String
	 */
	final function addOrder($column, $dir = null) {
		return $this->orderBy($column, $dir);
	}

	/**
	 * Sets the limit of rows that can be returned
	 * @return Query
	 * @param $limit Int
	 */
	function setLimit($limit) {
		$this->_limit = (int) $limit;
		return $this;
	}

	/**
	 * Returns the LIMIT integer for this Query, if it has one
	 * @return int
	 */
	function getLimit() {
		return $this->_limit;
	}

	/**
	 * Sets the offset for the rows returned.  Used to build
	 * the LIMIT part of the query.
	 * @return Query
	 * @param $offset Int
	 */
	function setOffset($offset) {
		$this->_offset = (int) $offset;
		return $this;
	}

	/**
	 * Builds and returns the query string
	 *
	 * @param mixed $conn Database connection to use
	 * @return QueryStatement
	 */
	function getQuery(PDO $conn = null) {
		if (null === $conn && class_exists('DBManager')) {
			$conn = DBManager::getConnection();
		}

		// the QueryStatement for the Query
		$stmnt = new QueryStatement($conn);

		// the string $statement will use
		$qry_s = '';

		$action = $this->_action;

		switch ($action) {
			default:
			case self::ACTION_COUNT:
			case self::ACTION_SELECT:
				$columns_stmnt = $this->getColumnsClause($conn);
				$stmnt->addIdentifiers($columns_stmnt->identifiers);
				$stmnt->addParams($columns_stmnt->params);
				$qry_s .= 'SELECT ' . $columns_stmnt->string . "\nFROM ";
				break;
			case self::ACTION_DELETE:
				$qry_s .= "DELETE\nFROM ";
				break;
			case self::ACTION_UPDATE:
				$qry_s .= "UPDATE\n";
				break;
		}

		$table_stmnt = $this->getTablesClause($conn);
		$stmnt->addIdentifiers($table_stmnt->identifiers);
		$stmnt->addParams($table_stmnt->params);
		$qry_s .= $table_stmnt->string;

		if ($this->_joins) {
			foreach ($this->_joins as $join) {
				$join_stmnt = $join->getQueryStatement($conn);
				$qry_s .= "\n\t" . $join_stmnt->string;
				$stmnt->addParams($join_stmnt->params);
				$stmnt->addIdentifiers($join_stmnt->identifiers);
			}
		}

		if (self::ACTION_UPDATE === $action) {
			if (empty($this->_updateColumnValues)) {
				throw new RuntimeException('Unable to build UPDATE query without update column values');
			}

			$column_updates = array();

			foreach ($this->_updateColumnValues as $column_name => &$column_value) {
				$column_updates[] = QueryStatement::IDENTIFIER . '=' . QueryStatement::PARAM;
				$stmnt->addIdentifier($column_name);
				$stmnt->addParam($column_value);
			}
			$qry_s .= "\nSET " . implode(',', $column_updates);
		}

		$where_stmnt = $this->getWhereClause();

		if (null !== $where_stmnt && $where_stmnt->string !== '') {
			$qry_s .= "\nWHERE " . $where_stmnt->string;
			$stmnt->addParams($where_stmnt->params);
			$stmnt->addIdentifiers($where_stmnt->identifiers);
		}

		if ($this->_groups) {
			$clause = $this->getGroupByClause();
			$stmnt->addIdentifiers($clause->identifiers);
			$stmnt->addParams($clause->params);
			$qry_s .= $clause->string;
		}

		if (null !== $this->getHaving()) {
			$having_stmnt = $this->getHaving()->getQueryStatement();
			if (null !== $having_stmnt) {
				$qry_s .= "\nHAVING " . $having_stmnt->string;
				$stmnt->addParams($having_stmnt->params);
				$stmnt->addIdentifiers($having_stmnt->identifiers);
			}
		}

		if ($action !== self::ACTION_COUNT && $this->_orders) {
			$clause = $this->getOrderByClause();
			$stmnt->addIdentifiers($clause->identifiers);
			$stmnt->addParams($clause->params);
			$qry_s .= $clause->string;
		}

		if (null !== $this->_limit) {
			if ($conn) {
				if (class_exists('DBMSSQL') && $conn instanceof DBMSSQL) {
					$qry_s = QueryStatement::embedIdentifiers($qry_s, $stmnt->getIdentifiers(), $conn);
					$stmnt->setIdentifiers(array());
				}
				$conn->applyLimit($qry_s, $this->_offset, $this->_limit);
			} else {
				$qry_s .= "\nLIMIT " . ($this->_offset ? $this->_offset . ', ' : '') . $this->_limit;
			}
		}

		if (self::ACTION_COUNT === $action && $this->needsComplexCount()) {
			$qry_s = "SELECT count(0)\nFROM ($qry_s) a";
		}

		$stmnt->string = $qry_s;
		return $stmnt;
	}

	/**
	 * Protected for now.  Likely to be public in the future.
	 * @return QueryStatement
	 */
	protected function getTablesClause($conn) {

		$table = $this->getTable();

		if (!$table) {
			throw new Exception('No table specified.');
		}

		$statement = new QueryStatement($conn);
		$alias = $this->getAlias();
		// if $table is a Query, get its QueryStatement
		if ($table instanceof Query) {
			$table_statement = $table->getQuery($conn);
			$table_string = '(' . $table_statement->string . ')';
		} else {
			$table_statement = null;
		}

		switch ($this->_action) {
			case self::ACTION_UPDATE:
			case self::ACTION_COUNT:
			case self::ACTION_SELECT:
				// setup identifiers for $table_string
				if (null !== $table_statement) {
					$statement->addIdentifiers($table_statement->identifiers);
					$statement->addParams($table_statement->params);
				} else {
					// if $table has no spaces, assume it is an identifier
					if (strpos($table, ' ') === false) {
						$statement->addIdentifier($table);
						$table_string = QueryStatement::IDENTIFIER;
					} else {
						$table_string = $table;
					}
				}

				// append $alias, if it's not empty
				if ($alias) {
					$table_string .= " AS $alias";
				}

				// setup identifiers for any additional tables
				if ($this->_extraTables) {
					foreach ($this->_extraTables as $tAlias => $extra_table) {
						if ($extra_table instanceof Query) {
							$extra_table_statement = $extra_table->getQuery($conn);
							$extra_table_string = '(' . $extra_table_statement->string . ') AS ' . $tAlias;
							$statement->addParams($extra_table_statement->params);
							$statement->addIdentifiers($extra_table_statement->identifiers);
						} else {
							$extra_table_string = $extra_table;
							if (strpos($extra_table_string, ' ') === false) {
								$extra_table_string = QueryStatement::IDENTIFIER;
								$statement->addIdentifier($extra_table);
							}
							if ($tAlias != $extra_table) {
								$extra_table_string .= " AS $tAlias";
							}
						}
						$table_string .= ", $extra_table_string";
					}
				}
				$statement->string = $table_string;
				break;
			case self::ACTION_DELETE:
				if (null !== $table_statement) {
					$statement->addIdentifiers($table_statement->identifiers);
					$statement->addParams($table_statement->params);
				} else {
					// if $table has no spaces, assume it is an identifier
					if (strpos($table, ' ') === false) {
						$statement->addIdentifier($table);
						$table_string = QueryStatement::IDENTIFIER;
					} else {
						$table_string = $table;
					}
				}

				// append $alias, if it's not empty
				if ($alias) {
					$table_string .= " AS $alias";
				}
				$statement->string = $table_string;
				break;
			default:
				throw new RuntimeException('Uknown action "' . $this->_action . '", cannot build table list');
				break;
		}
		return $statement;
	}

	/**
	 * Returns true if this Query uses aggregate functions in either a GROUP BY clause or in the
	 * select columns
	 * @return bool
	 */
	protected function hasAggregates() {
		if ($this->_groups) {
			return true;
		}
		foreach ($this->_columns as $column) {
			if (strpos($column, '(') !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns true if this Query requires a complex count
	 * @return bool
	 */
	protected function needsComplexCount() {
		return $this->hasAggregates()
		|| null !== $this->_having
		|| $this->_distinct;
	}

	/**
	 * Protected for now.  Likely to be public in the future.
	 * @return QueryStatement
	 */
	protected function getColumnsClause($conn) {
		$table = $this->getTable();

		if (!$table) {
			throw new Exception('No table specified.');
		}

		$statement = new QueryStatement($conn);
		$alias = $this->getAlias();
		$action = $this->_action;

		if ($action == self::ACTION_DELETE) {
			return $statement;
		}

		if ($action == self::ACTION_COUNT) {
			if (!$this->needsComplexCount()) {
				$statement->string = 'count(0)';
				return $statement;
			}

			if ($this->_groups) {
				$groups = $this->_groups;
				foreach ($groups as &$group) {
					$statement->addIdentifier($group);
					$group = QueryStatement::IDENTIFIER;
				}
				$statement->string = implode(', ', $groups);
				return $statement;
			}

			if (!$this->_distinct && null === $this->getHaving() && $this->_columns) {
				$columns_to_use = array();
				foreach ($this->_columns as $column) {
					if (strpos($column, '(') === false) {
						continue;
					}
					$statement->addIdentifier($column);
					$columns_to_use[] = QueryStatement::IDENTIFIER;
				}
				if ($columns_to_use) {
					$statement->string = implode(', ', $columns_to_use);
					return $statement;
				}
			}
		}

		// setup $columns_string
		if ($this->_columns) {
			$columns = $this->_columns;
			foreach ($columns as &$column) {
				$statement->addIdentifier($column);
				$column = QueryStatement::IDENTIFIER;
			}
			$columns_string = implode(', ', $columns);
		} elseif ($alias) {
			// default to selecting only columns from the target table
			$columns_string = "$alias.*";
		} else {
			// default to selecting only columns from the target table
			$columns_string = QueryStatement::IDENTIFIER . '.*';
			$statement->addIdentifier($table);
		}

		if ($this->_distinct) {
			$columns_string = "DISTINCT $columns_string";
		}

		$statement->string = $columns_string;
		return $statement;
	}

	/**
	 * Protected for now.  Likely to be public in the future.
	 * @return QueryStatement
	 */
	protected function getWhereClause() {
		return $this->getWhere()->getQueryStatement();
	}

	/**
	 * Protected for now.  Likely to be public in the future.
	 * @return QueryStatement
	 */
	protected function getOrderByClause($conn = null) {
		$statement = new QueryStatement($conn);
		$orders = $this->_orders;
		foreach ($orders as &$order) {
			$order_parts = explode(' ', $order);
			if (count($order_parts) == 1 || count($order_parts) == 2) {
				$statement->addIdentifier($order_parts[0]);
				$order_parts[0] = QueryStatement::IDENTIFIER;
			}
			$order = implode(' ', $order_parts);
		}
		$statement->string = "\nORDER BY " . implode(', ', $orders);
		return $statement;
	}

	/**
	 * Protected for now.  Likely to be public in the future.
	 * @return QueryStatement
	 */
	protected function getGroupByClause($conn = null) {
		$statement = new QueryStatement($conn);
		if ($this->_groups) {
			$groups = $this->_groups;
			foreach ($groups as &$group) {
				$statement->addIdentifier($group);
				$group = QueryStatement::IDENTIFIER;
			}
			$statement->string = "\nGROUP BY " . implode(', ', $groups);
		}
		return $statement;
	}

	/**
	 * @return string
	 */
	function __toString() {
		$q = clone $this;
		if (!$q->getTable())
			$q->setTable('{UNSPECIFIED-TABLE}');
		return (string) $q->getQuery();
	}

	/**
	 * Returns a count of rows for result
	 * @return int
	 * @param $conn PDO[optional]
	 */
	function doCount(PDO $conn = null) {
		$q = clone $this;

		if (!$q->getTable()) {
			throw new RuntimeException('No table specified.');
		}

		$q->setAction(self::ACTION_COUNT);
		return (int) $q->getQuery($conn)->bindAndExecute()->fetchColumn();
	}

	/**
	 * Executes DELETE query and returns count of
	 * rows deleted.
	 * @return int
	 * @param $conn PDO[optional]
	 */
	function doDelete(PDO $conn = null) {
		$q = clone $this;

		if (!$q->getTable()) {
			throw new RuntimeException('No table specified.');
		}

		$q->setAction(self::ACTION_DELETE);
		return (int) $q->getQuery($conn)->bindAndExecute()->rowCount();
	}

	/**
	 * Executes SELECT query and returns a result set.
	 * @return PDOStatement
	 * @param $conn PDO[optional]
	 */
	function doSelect(PDO $conn = null) {
		$q = clone $this;

		if (!$q->getTable()) {
			throw new RuntimeException('No table specified.');
		}

		$q->setAction(self::ACTION_SELECT);
		return $q->getQuery($conn)->bindAndExecute();
	}

	/**
	 * Do not use this if you can avoid it.  Just use doUpdate.
	 * @deprecated
	 * @see Query::doUpdate
	 * @return Query
	 */
	function setUpdateColumnValues(array $column_values) {
		$this->_updateColumnValues = &$column_values;
		return $this;
	}

	/**
	 * @param array $column_values
	 * @param PDO $conn
	 * @return int
	 * @throws RuntimeException
	 */
	function doUpdate(array $column_values, PDO $conn = null) {
		$q = clone $this;

		$q->_updateColumnValues = &$column_values;

		if (!$q->getTable()) {
			throw new RuntimeException('No table specified.');
		}

		$q->setAction(self::ACTION_UPDATE);
		return (int) $q->getQuery($conn)->bindAndExecute()->rowCount();
	}
}
