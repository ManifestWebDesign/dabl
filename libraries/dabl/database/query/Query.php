<?php

/**
 * Used to build query strings using OOP
 */
class Query {
	const ACTION_COUNT = 'COUNT';
	const ACTION_DELETE = 'DELETE';
	const ACTION_SELECT = 'SELECT';

	//Comparison types
	const EQUAL = '=';
	const NOT_EQUAL = '<>';
	const ALT_NOT_EQUAL = '!=';
	const GREATER_THAN = '>';
	const LESS_THAN = '<';
	const GREATER_EQUAL = '>=';
	const LESS_EQUAL = '<=';
	const LIKE = 'LIKE';
	const NOT_LIKE = 'NOT LIKE';
	const CUSTOM = 'CUSTOM';
	const DISTINCT = 'DISTINCT';
	const IN = 'IN';
	const NOT_IN = 'NOT IN';
	const ALL = 'ALL';
	const IS_NULL = 'IS NULL';
	const IS_NOT_NULL = 'IS NOT NULL';
	const BETWEEN = 'BETWEEN';

	//Comparison type for update
	const CUSTOM_EQUAL = 'CUSTOM_EQUAL';

	//PostgreSQL comparison types
	const ILIKE = 'ILIKE';
	const NOT_ILIKE = 'NOT ILIKE';

	//JOIN TYPES
	const JOIN = 'JOIN';
	const LEFT_JOIN = 'LEFT JOIN';
	const RIGHT_JOIN = 'RIGHT JOIN';
	const INNER_JOIN = 'INNER JOIN';
	const OUTER_JOIN = 'OUTER JOIN';

	//Binary AND
	const BINARY_AND = '&';

	//Binary OR
	const BINARY_OR = '|';

	//'Order by' qualifiers
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
	function setDistinct($bool) {
		$this->_distinct = (bool) $bool;
	}

	/**
	 * Sets the action of the query.  Should be SELECT, DELETE, or COUNT.
	 * @return Query
	 * @param $action String
	 */
	function setAction($action) {
		$this->_action = $action;
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
	function addColumn($column_name) {
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
	function setTable($table_name, $alias=null) {
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
	 * Alias of {@link addJoin()}.
	 */
	function join($table, $on_clause=null, $join_type=self::JOIN) {
		return $this->addJoin($table, $on_clause, $join_type);
	}

	/**
	 * Add a JOIN to the query.
	 *
	 * @todo Support the ON clause being NULL correctly
	 * @param string|Query $table Table to join on
	 * @param string|Condition $on_clause ON clause to join with
	 * @param string $join_type Type of JOIN to perform
	 * @return Query
	 */
	function addJoin($table, $on_clause=null, $join_type=self::JOIN) {
		if ($table instanceof QueryJoin) {
			$this->_joins[] = clone $table;
			return $this;
		}

		if (null === $on_clause) {
			if ($join_type == self::JOIN || $join_type == self::INNER_JOIN) {
				$this->addTable($table);
				return this;
			}
			$on_clause = '1 = 1';
		}

		$this->_joins[] = new QueryJoin($table, $on_clause, $join_type);
		return $this;
	}

	function getJoins() {
		return $this->_joins;
	}

	function setJoins($joins) {
		$this->_joins = $joins;
	}

	/**
	 * Alias of {@link addAnd()}
	 * @return Query
	 */
	function add($column, $value=null, $operator=self::EQUAL, $quote = null) {
		if (func_num_args() === 1) {
			return $this->addAnd($column);
		} else {
			return $this->addAnd($column, $value, $operator, $quote);
		}
	}

	/**
	 * Shortcut to adding an AND statement to the Query's WHERE Condition.
	 * @return Query
	 * @param $column Mixed
	 * @param $value Mixed[optional]
	 * @param $operator String[optional]
	 * @param $quote Int[optional]
	 */
	function addAnd($column, $value=null, $operator=self::EQUAL, $quote = null) {
		if (func_num_args() === 1) {
			$this->_where->addAnd($column);
		} else {
			$this->_where->addAnd($column, $value, $operator, $quote);
		}
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
	function addOr($column, $value=null, $operator=self::EQUAL, $quote = null) {
		if (func_num_args() === 1) {
			$this->_where->addOr($column);
		} else {
			$this->_where->addOr($column, $value, $operator, $quote);
		}
		return $this;
	}

	/**
	 * Shortcut to addGroup() method
	 * @return Query
	 */
	function group($column) {
		return $this->addGroup($column);
	}

	/**
	 * Adds a clolumn to GROUP BY
	 * @return Query
	 * @param $column String
	 */
	function addGroup($column) {
		$this->_groups[] = $column;
		return $this;
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
	function order($column, $dir=null) {
		return $this->addOrder($column, $dir);
	}

	/**
	 * Adds a column to ORDER BY in the form of "COLUMN DIRECTION"
	 * @return Query
	 * @param $column String
	 */
	function addOrder($column, $dir=null) {
		$dir = strtoupper($dir);
		if (null != $dir && $dir !== self::ASC && $dir !== self::DESC) {
			throw new Exception("$dir is not a valid sorting direction.");
		}
		$this->_orders[] = "$column $dir";
		return $this;
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
	function getQuery($conn = null) {
		if (!$conn) {
			$conn = DBManager::getConnection();
		}

		// the QueryStatement for the Query
		$statement = new QueryStatement($conn);

		// the string $statement will use
		$query_s = '';

		switch (strtoupper($this->getAction())) {
			default:
			case self::ACTION_COUNT:
			case self::ACTION_SELECT:
				$columns_statement = $this->getColumnsClause($conn);
				$statement->addIdentifiers($columns_statement->getIdentifiers());
				$statement->addParams($columns_statement->getParams());
				$query_s .= 'SELECT ' . $columns_statement->getString();
				break;
			case self::ACTION_DELETE:
				$query_s .= 'DELETE';
				break;
		}

		$table_statement = $this->getTablesClause($conn);
		$statement->addIdentifiers($table_statement->getIdentifiers());
		$statement->addParams($table_statement->getParams());
		$query_s .= "\nFROM " . $table_statement->getString();

		if ($this->_joins) {
			foreach ($this->_joins as $join) {
				$join_statement = $join->getQueryStatement($conn);
				$query_s .= "\n\t" . $join_statement->getString();
				$statement->addParams($join_statement->getParams());
				$statement->addIdentifiers($join_statement->getIdentifiers());
			}
		}

		$where_statement = $this->getWhereClause();

		if ($where_statement) {
			$query_s .= "\nWHERE " . $where_statement->getString();
			$statement->addParams($where_statement->getParams());
			$statement->addIdentifiers($where_statement->getIdentifiers());
		}

		if ($this->_groups) {
			$clause = $this->getGroupClause();
			$statement->addIdentifiers($clause->getIdentifiers());
			$statement->addParams($clause->getParams());
			$query_s .= $clause->getString();
		}

		if (null !== $this->getHaving()) {
			$having_statement = $this->getHaving()->getQueryStatement();
			if ($having_statement) {
				$query_s .= "\nHAVING " . $having_statement->getString();
				$statement->addParams($having_statement->getParams());
				$statement->addIdentifiers($having_statement->getIdentifiers());
			}
		}

		if ($this->getAction() != self::ACTION_COUNT && $this->_orders) {
			$clause = $this->getOrderClause();
			$statement->addIdentifiers($clause->getIdentifiers());
			$statement->addParams($clause->getParams());
			$query_s .= $clause->getString();
		}

		if ($this->_limit) {
			if ($conn) {
				$conn->applyLimit($query_s, $this->_offset, $this->_limit);
			} else {
				$query_s .= "\nLIMIT " . ($this->_offset ? $this->_offset . ', ' : '') . $this->_limit;
			}
		}

		if ($this->needsComplexCount() && $this->getAction() == self::ACTION_COUNT) {
			$query_s = "SELECT count(0)\nFROM ($query_s) a";
		}

		$statement->setString($query_s);
		return $statement;
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
			$table_string = '(' . $table_statement->getString() . ')';
		} else {
			$table_statement = null;
		}

		switch (strtoupper($this->getAction())) {
			case self::ACTION_COUNT:
			case self::ACTION_SELECT:
				// setup identifiers for $table_string
				if (null !== $table_statement) {
					$statement->addIdentifiers($table_statement->getIdentifiers());
					$statement->addParams($table_statement->getParams());
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
					foreach ($this->_extraTables as $alias => $extra_table) {
						if ($extra_table instanceof Query) {
							$extra_table_statement = $extra_table->getQuery($conn);
							$extra_table_string = '(' . $extra_table_statement->getString() . ') AS ' . $alias;
							$statement->addParams($extra_table_statement->getParams());
							$statement->addIdentifiers($extra_table_statement->getIdentifiers());
						} else {
							$extra_table_string = $extra_table;
							if (strpos($extra_table_string, ' ') === false) {
								$extra_table_string = QueryStatement::IDENTIFIER;
								$statement->addIdentifier($extra_table);
							}
							if ($alias != $extra_table) {
								$extra_table_string .= " AS $alias";
							}
						}
						$table_string .= ", $extra_table_string";
					}
				}
				$statement->setString($table_string);
				break;
			case self::ACTION_DELETE:
				if (null !== $table_statement) {
					$statement->addIdentifiers($table_statement->getIdentifiers());
					$statement->addParams($table_statement->getParams());
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
				$statement->setString($table_string);
				break;
			default:
				break;
		}
		return $statement;
	}

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
		$action = strtoupper($this->getAction());

		if ($action == self::ACTION_DELETE) {
			return $statement;
		}

		if ($action == self::ACTION_COUNT) {
			if (!$this->needsComplexCount()) {
				$statement->setString('count(0)');
				return $statement;
			}

			if ($this->_groups) {
				$groups = $this->_groups;
				foreach ($groups as &$group) {
					$statement->addIdentifier($group);
					$group = QueryStatement::IDENTIFIER;
				}
				$statement->setString(implode(', ', $groups));
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
					$statement->setString(implode(', ', $columns_to_use));
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

		$statement->setString($columns_string);
		return $statement;
	}

	/**
	 * Protected for now.  Likely to be public in the future.
	 * @return QueryStatement
	 */
	protected function getWhereClause($conn = null) {
		return $this->getWhere()->getQueryStatement();
	}

	/**
	 * Protected for now.  Likely to be public in the future.
	 * @return QueryStatement
	 */
	protected function getOrderClause($conn = null) {
		$statement = new QueryStatement($conn);
		$orders = $this->_orders;
		foreach ($orders as &$order) {
			$order_parts = explode(' ', $order);
			foreach ($order_parts as &$order) {
				$statement->addIdentifier($order);
				$group = QueryStatement::IDENTIFIER;
			}
			$order = implode(' ', $order_parts);
		}
		$statement->setString("\nORDER BY " . implode(', ', $orders));
		return $statement;
	}

	/**
	 * Protected for now.  Likely to be public in the future.
	 * @return QueryStatement
	 */
	protected function getGroupClause($conn = null) {
		$statement = new QueryStatement($conn);
		if ($this->_groups) {
			$groups = $this->_groups;
			foreach ($groups as &$group) {
				$statement->addIdentifier($group);
				$group = QueryStatement::IDENTIFIER;
			}
			$statement->setString("\nGROUP BY " . implode(', ', $groups));
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

		if (!$q->getTable())
			throw new Exception('No table specified.');

		$q->setAction(self::ACTION_COUNT);
		return $q->getQuery($conn)->bindAndExecute()->fetchColumn();
	}

	/**
	 * Executes DELETE query and returns count of
	 * rows deleted.
	 * @return int
	 * @param $conn PDO[optional]
	 */
	function doDelete(PDO $conn = null) {
		$q = clone $this;

		if (!$q->getTable())
			throw new Exception('No table specified.');

		$q->setAction(self::ACTION_DELETE);
		return $q->getQuery($conn)->bindAndExecute()->rowCount();
	}

	/**
	 * Executes SELECT query and returns a result set.
	 * @return PDOStatement
	 * @param $conn PDO[optional]
	 */
	function doSelect(PDO $conn = null) {
		$q = clone $this;
		if (!$q->getTable())
			throw new Exception('No table specified.');

		$q->setAction(self::ACTION_SELECT);
		return $q->getQuery($conn)->bindAndExecute();
	}

}
