<?php

class QueryJoin {

	/**
	 * @var mixed
	 */
	private $_table;
	
	/**
	 * @var string
	 */
	private $_alias;
	
	/**
	 * @var mixed
	 */
	private $_onClause;
	
	/**
	 * @var string
	 */
	private $_joinType = Query::JOIN;

	function __construct($table_name, $on_clause=null, $join_type=Query::JOIN) {
		$this->setTable($table_name)
			->setOnClause($on_clause)
			->setJoinType($join_type);
	}

	function __clone() {
		if ($this->_onClause instanceof Condition) {
			$this->_onClause = clone $this->_onClause;
		}
		if ($this->_table instanceof Query) {
			$this->_table = clone $this->_table;
		}
	}

	/**
	 * @return string
	 */
	function __toString() {
		$j = clone $this;
		if (!$j->getTable())
			$j->setTable('{UNSPECIFIED-TABLE}');
		return (string) $j->getQueryStatement();
	}
	
	/**
	 * @param type $table_name
	 * @param type $on_clause
	 * @param type $join_type
	 * @return QueryJoin
	 */
	static function create($table_name, $on_clause=null, $join_type=Query::JOIN) {
		return new self($table_name, $on_clause, $join_type);
	}
	
	/**
	 * @param mixed $table_name
	 * @return QueryJoin
	 */
	function setTable($table_name) {
		if ($table_name instanceof Query) {
			$table_name = clone $table_name;
		} else {
			$space = strrpos($table_name, ' ');
			if ($space) {
				$table_name = substr($table_name, 0, $space + 1);
				$this->_alias = substr($table_name, $space);
			}
		}
		$this->_table = $table_name;
		return $this;
	}
	
	/**
	 * @param string $alias
	 * @return QueryJoin 
	 */
	function setAlias($alias) {
		$this->_alias = $alias;
		return $this;
	}
	
	/**
	 * @param Condition $on_clause
	 * @return QueryJoin
	 */
	function setOnClause($on_clause) {
		if ($on_clause instanceof Condition) {
			$this->_onClause = clone Condition;
		} else {
			$this->_onClause = $on_clause;
		}
		return $this;
	}
	
	/**
	 * @param string $join_type
	 * @return QueryJoin 
	 */
	function setJoinType($join_type) {
		$this->_joinType = $join_type;
		return $this;
	}
	
	/**
	 * @param DABLPDO $conn
	 * @return QueryStatement
	 */
	function getQueryStatement(DABLPDO $conn = null) {
		$statement = new QueryStatement;
		$table = $this->_table;
		$on_clause = $this->_onClause;
		$join_type = $this->_joinType;
		$alias = $this->_alias;

		if ($table instanceof Query) {
			$table_statement = $table->getQuery($conn);
			$table = '(' . $table_statement->getString() . ')';
			$statement->addParams($table_statement->getParams());
		} elseif ($conn) {
			$table = $conn->quoteIdentifier($table);
		}

		if ($alias) {
			$table .= " $alias";
		}

		if (null === $on_clause) {
			$on_clause = '1 = 1';
		} elseif ($on_clause instanceof Condition) {
			$on_clause_statement = $on_clause->getClause();
			$on_clause = $on_clause_statement->getString();
			$statement->addParams($on_clause_statement->getParams());
		}

		if ('' !== $on_clause) {
			$on_clause = "ON ($on_clause)";
		}

		$statement->setString("$join_type $table $on_clause");
		return $statement;
	}

	/**
	 * @return mixed
	 */
	function getTable() {
		return $this->_table;
	}

	/**
	 * @return string
	 */
	function getAlias() {
		return $this->_alias;
	}

	/**
	 * @return mixed
	 */
	function getOnClause() {
		return $this->_onClause;
	}
	
	/**
	 * @return string
	 */
	function getJoinType() {
		return $this->_joinType;
	}

}
