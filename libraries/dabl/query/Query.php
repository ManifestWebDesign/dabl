<?php

/**
 * Used to build query strings using OOP
 */
class Query{

	const ACTION_COUNT = "COUNT";
	const ACTION_DELETE = "DELETE";
	const ACTION_SELECT = "SELECT";

	//Comparison types
	const EQUAL = "=";
	const NOT_EQUAL = "<>";
	const ALT_NOT_EQUAL = "!=";
	const GREATER_THAN = ">";
	const LESS_THAN = "<";
	const GREATER_EQUAL = ">=";
	const LESS_EQUAL = "<=";
	const LIKE = "LIKE";
	const NOT_LIKE = "NOT LIKE";
	const CUSTOM = "CUSTOM";
	const DISTINCT = "DISTINCT";
	const IN = "IN";
	const NOT_IN = "NOT IN";
	const ALL = "ALL";
	const IS_NULL = "IS NULL";
	const IS_NOT_NULL = "IS NOT NULL";
	const BETWEEN = "BETWEEN";

	//Comparison type for update
	const CUSTOM_EQUAL = "CUSTOM_EQUAL";

	//PostgreSQL comparison types
	const ILIKE = "ILIKE";
	const NOT_ILIKE = "NOT ILIKE";

	//JOIN TYPES
	const JOIN = "JOIN";
	const LEFT_JOIN = "LEFT JOIN";
	const RIGHT_JOIN = "RIGHT JOIN";
	const INNER_JOIN = "INNER JOIN";
	const OUTER_JOIN = "OUTER JOIN";

	//Binary AND
	const BINARY_AND = "&";

	//Binary OR
	const BINARY_OR = "|";

	//"Order by" qualifiers
	const ASC = "ASC";
	const DESC = "DESC";

	private $_action = self::ACTION_SELECT;
	private $_columns = array();
	private $_table;
	private $_joins = array();
	private $_where;
	private $_orders = array();
	private $_groups = array();
	private $_having;
	private $_limit;
	private $_offset;
	private $_distinct = false;

	/**
	 * Creates new instance of Query, parameters will be passed to the
	 * setTable() method.
	 * @return
	 * @param $tableName Mixed[optional]
	 * @param $alias String[optional]
	 */
	function __construct($tableName=null, $alias=null){
		$this->setWhere(new Condition);
		$this->setTable($tableName, $alias);
		return $this;
	}

	function __clone(){
		if($this->_where instanceof Condition)
			$this->_where = clone $this->_where;
		if($this->_having instanceof Condition)
			$this->_having = clone $this->_having;
	}

	/**
	 * @return Query
	 */
	function getInstance(){
		return new self;
	}

	/**
	 * Specify whether to select only distinct rows
	 * @param Bool $bool
	 */
	function setDistinct($bool){
		$this->_distinct = (bool) $bool;
	}

	/**
	 * Sets the action of the query.  Should be SELECT, DELETE, or COUNT.
	 * @return Query
	 * @param $action String
	 */
	function setAction($action){
		$this->_action = $action;
		return $this;
	}

	/**
	 * Returns the action of the query.  Should be SELECT, DELETE, or COUNT.
	 * @return String
	 */
	function getAction(){
		return $this->_action;
	}

	/**
	 * Add a column to the list of columns to select.  If unused, defaults to *.
	 * @param String $columnName
	 * @return Query
	 */
	function addColumn($columnName){
		$this->_columns[$columnName] = $columnName;
		return $this;
	}

	/**
	 * Sets the table to be queried. This can be a string table name or an
	 * instance of Query if you would like to nest queries.
	 * @return Query
	 * @param $tableName Mixed
	 * @param $alias String[optional]
	 */
	function setTable($tableName, $alias=null){
		if($tableName instanceof Query){
			if(!$alias)
				throw new Exception("The nested query must have an alias.");
			$tableName = "($tableName) $alias";
		}
		elseif($alias)
			$tableName = "$tableName $alias";
		$this->_table = $tableName;
		return $this;
	}

	/**
	 * Returns a String representation of the table being queried, including its
	 * alias if present.
	 * @return String
	 */
	function getTable(){
		return $this->_table;
	}

	/**
	 * Returns a String representation of the table being queried, not including
	 * its alias.
	 * @return String
	 */
	function getTableName(){
		$table = $this->_table;
		$table_parts = explode(' ', $table, 2);
		if(count($table_parts)==1)
			return $table;
		else
			return $table_parts[0];
	}

	/**
	 * Returns a String of the alias of the talbe being queried, if present
	 * @return String
	 */
	function getAlias(){
		$table = $this->_table;
		$table_parts = explode(' ', $table);
		if(count($table_parts)>1)
			return array_pop($table_parts);
	}

	/**
	 * Provide the Condition object to generate the WHERE clause of the query
	 * @return Query
	 * @param $w Condition
	 */
	function setWhere(Condition $w){
		$this->_where = $w;
		return $this;
	}

	/**
	 * Returns the Condition object that generates the WHERE clause of the query
	 * @return Condition
	 */
	function getWhere(){
		return $this->_where;
	}

	/**
	 * Short version of addJoin();
	 * @return Query
	 */
	function join($table, $onClause=null, $joinType=self::JOIN){
		return $this->addJoin($table, $onClause, $joinType);
	}

	/**
	 * Add a join to the query.  $table should be a String representation of
	 * the table to join, including an alias, if needed.  $onClause can be a
	 * String or Condition object.
	 * @return Query
	 * @param $table String
	 * @param $onClause Mixed[optional]
	 * @param $joinType String[optional]
	 */
	function addJoin($table, $onClause=null, $joinType=self::JOIN){
		if(!$onClause)
			$this->_joins[] = $table;
		else{
			if($onClause instanceof Condition)
				$onClause = $onClause->getClause();

			$conn = DBManager::getConnection();

			$table_parts = explode(' ', str_replace("`","",trim($table)));
			if(count($table_parts)==1){
				if($conn) $table = $conn->quoteIdentifier($table);
			}
			else{
				$table_name = $table_parts[0];
				if($conn) $table_name = $conn->quoteIdentifier($table_name);
				$alias = array_pop($table_parts);
				$table = "$table_name $alias";
			}
			$this->_joins[] = "$joinType $table ON ($onClause)";
		}
		return $this;
	}

	/**
	 * Short version of addAnd();
	 * @return Query
	 */
	function add($column, $value=null, $operator=self::EQUAL, $quote = null){
		return $this->addAnd($column, $value, $operator, $quote);
	}

	/**
	 * Shortcut to adding an AND statement to the Query's WHERE Condition.
	 * @return Query
	 * @param $column Mixed
	 * @param $value Mixed[optional]
	 * @param $operator String[optional]
	 * @param $quote Int[optional]
	 */
	function addAnd($column, $value=null, $operator=self::EQUAL, $quote = null){
		$this->_where->addAnd($column, $value, $operator, $quote);
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
	function addOr($column, $value=null, $operator=self::EQUAL, $quote = null){
		$this->_where->addOr($column, $value, $operator, $quote);
		return $this;
	}

	/**
	 * Shortcut to addGroup() method
	 * @return Query
	 */
	function group($column){
		return $this->addGroup($column);
	}

	/**
	 * Adds a clolumn to GROUP BY
	 * @return Query
	 * @param $column String
	 */
	function addGroup($column){
		$this->_groups[] = $column;
		return $this;
	}

	/**
	 * Provide the Condition object to generate the HAVING clause of the query
	 * @return Query
	 * @param $w Condition
	 */
	function setHaving(Condition $where){
		$this->_having=$where;
		return $this;
	}

	/**
	 * Returns the Condition object that generates the HAVING clause of the query
	 * @return Condition
	 */
	function getHaving(){
		return $this->_having;
	}

	/**
	 * Shortcut for addOrder()
	 * @return Query
	 */
	function order($column, $dir=null){
		return $this->addOrder($column, $dir);
	}

	/**
	 * Adds a column to ORDER BY in the form of "COLUMN DIRECTION"
	 * @return Query
	 * @param $column String
	 */
	function addOrder($column, $dir=null){
		$dir = strtoupper($dir);
		$allowed = array(self::DESC, self::ASC);
		if($dir && !in_array($dir, $allowed))
			throw new Exception("$dir is not a valid sorting direction.");
		$this->_orders[] = "$column $dir";
		return $this;
	}

	/**
	 * Sets the limit of rows that can be returned
	 * @return Query
	 * @param $limit Int
	 */
	function setLimit($limit){
		$this->_limit = (int)$limit;
		return $this;
	}

	function getLimit(){
		return $this->_limit;
	}

	/**
	 * Sets the offset for the rows returned.  Used to build
	 * the LIMIT part of the query.
	 * @return Query
	 * @param $offset Int
	 */
	function setOffset($offset){
		$this->_offset = (int)$offset;
		return $this;
	}

	/**
	 * Builds and returns the query string
	 * @return String
	 */
	function getQuery($conn = null){
		$table_name = $this->getTableName();

		if(!$table_name)
			throw new Exception("No table specified.");

		$alias = $this->getAlias();
		if(!$conn) $conn = DBManager::getConnection();
		$query_s = "";
		$statement = new QueryStatement($conn);

		if($this->_columns)
			$columns = implode(', ',$this->_columns);
		elseif($alias)$columns = "$alias.*";
		else $columns = "*";

		if($this->_distinct)
			$columns = "DISTINCT $columns";

		if($conn)
			$table = $alias ? $conn->quoteIdentifier($table_name)." $alias" : $conn->quoteIdentifier($table_name);
		else
			$table = $alias ? "`$table_name` $alias" : "`$table_name`";

		switch(strtoupper($this->getAction())){
			case self::ACTION_COUNT:
			case self::ACTION_SELECT:
				$query_s .="SELECT $columns \n FROM $table ";
				break;
			case self::ACTION_DELETE:
				$query_s .="DELETE \n FROM $table ";
				break;
			default:
				break;
		}

		if($this->_joins)
			$query_s .= "\n ".implode("\n ", $this->_joins).' ';

		$where_statement = $this->getWhere()->getClause();

		if($where_statement){
			$query_s .= "\n WHERE ".$where_statement->getString().' ';
			$statement->addParams($where_statement->getParams());
		}

		if($this->_groups)
			$query_s .= "\n GROUP BY ".implode(', ',$this->_groups).' ';

		if($this->getHaving()){
			$having_statement = $this->getHaving()->getClause();
			if($having_statement){
				$query_s .= "\n HAVING ".$having_statement->getString().' ';
				$statement->addParams($having_statement->getParams());
			}
		}

		if($this->getAction()!=self::ACTION_COUNT && $this->_orders)
			$query_s .= "\n ORDER BY ".implode(', ',$this->_orders).' ';

		if($this->_limit){
			if($conn)
				$conn->applyLimit($query_s, $this->_offset, $this->_limit);
			else
				$query_s .= "\n LIMIT ".($this->_offset ? $this->_offset.', ' : '').$this->_limit;
		}

		if($this->getAction()==self::ACTION_COUNT)
			$query_s = "SELECT count(0) FROM ($query_s) a";

		$statement->setString($query_s);
		return $statement;
	}

	/**
	 * @return String
	 */
	function __toString(){
		$q = clone $this;
		if(!$q->getTable())
			$q->setTable('{UNSPECIFIED-TABLE}');
		return (string)$q->getQuery();
	}

	/**
	 * Returns a count of rows for result
	 * @return int
	 * @param $conn PDO[optional]
	 */
	function doCount(PDO $conn = null){
		$q = clone $this;

		if(!$q->getTable())
			throw new Exception("No table specified.");

		$q->setAction(self::ACTION_COUNT);

		$qs = $q->getQuery($conn);
		$result = $qs->bindAndExecute();
		return $result->fetchColumn();
	}

	/**
	 * Executes DELETE query and returns count of
	 * rows deleted.
	 * @return int
	 * @param $conn PDO[optional]
	 */
	function doDelete(PDO $conn = null){
		$q = clone $this;

		if(!$q->getTable())
			throw new Exception("No table specified.");

		$q->setAction(self::ACTION_DELETE);

		$qs = $q->getQuery($conn);
		$result = $qs->bindAndExecute();
		return $result->rowCount();
	}

	/**
	 * Executes SELECT query and returns a result set.
	 * @return PDOStatement
	 * @param $conn PDO[optional]
	 */
	function doSelect(PDO $conn = null){
		$q = clone $this;
		if(!$q->getTable())
			throw new Exception("No table specified.");

		$q->setAction(self::ACTION_SELECT);

		$qs = $q->getQuery($conn);
		$result = $qs->bindAndExecute();
		return $result;
	}

}
