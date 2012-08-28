<?php

/**
 * Represents/contains "AND" or "OR" statements
 *
 * $q = new Query("table");
 * $q->setAction("SELECT");
 *
 * Example:
 *
 * $c = new Condition;
 * $c->addAnd('Column',$value);			- $c statement = "Column=$value"
 * $c->addOr('Column2',$value2,"<"); 	- $c statement = "Column=$value OR Column2<$value2"
 *
 * ..could also be written like this:
 * $c->addAnd('Column',$value)->addOr('Column2',$value2,"<");
 *
 * $c2 = new Condition;
 * $c2->addAnd('Column3',$value3);	- $c2 statement = "Column3=$value3"
 * $c2->addAnd('Column4',$value4);	- $c2 statement = "Column3=$value3 AND Column4=$value4"
 *
 * $c->addOr($c2);					- $c statement = "Column=$value OR Column2<$value2 OR (Column3=$value3 AND Column4=$value4)"
 *
 * $q->addAnd($c);					- $q string = "SELECT * FROM table WHERE Column=$value OR Column2<$value2 OR (Column3=$value3 AND Column4=$value4)"
 */
class Condition {
	/**
	 * escape only the first parameter
	 */
	const QUOTE_LEFT = 1;

	/**
	 * escape only the second param
	 */
	const QUOTE_RIGHT = 2;

	/**
	 * escape both params
	 */
	const QUOTE_BOTH = 3;

	/**
	 * escape no params
	 */
	const QUOTE_NONE = 4;

	private $conds = array();

	function __construct($left = null, $right = null, $operator = Query::EQUAL, $quote = null) {
		if (func_num_args() > 0) {
			$this->add($left, $right, $operator, $quote);
		}
	}

	/**
	 * Returns new instance of self by passing arguments directly to constructor.
	 * @param $left mixed
	 * @param $right mixed[optional]
	 * @param $operator string[optional]
	 * @param $quote int[optional]
	 * @return Condition
	 */
	static function create($left = null, $right = null, $operator=Query::EQUAL, $quote = null) {
		return new self($left, $right, $operator, $quote);
	}

	/**
	 * @return QueryStatement
	 */
	private static function processCondition($left = null, $right = null, $operator = Query::EQUAL, $quote = null) {
		if ($left instanceof QueryStatement && 1 === func_num_args()) {
			return $left;
		}

		$statement = new QueryStatement;

		// Left can be a Condition
		if ($left instanceof self) {
			$clause_statement = $left->getQueryStatement();
			if (null === $clause_statement) {
				return null;
			}
			$clause_statement->string = '(' . $clause_statement->string . ')';
			return $clause_statement;
		}

		if (null === $quote) {
			// You can skip $operator and specify $quote with parameter 3
			if (is_int($operator)) {
				$quote = $operator;
				$operator = Query::EQUAL;
			} else {
				$quote = self::QUOTE_RIGHT;
			}
		}

		if (Query::BEGINS_WITH === $operator) {
			$right .= '%';
			$operator = Query::LIKE;
		} elseif (Query::ENDS_WITH === $operator) {
			$right = '%' . $right;
			$operator = Query::LIKE;
		} elseif (Query::CONTAINS === $operator) {
			$right = '%' . $right . '%';
			$operator = Query::LIKE;
		}

		// Escape $left
		if ($quote === self::QUOTE_LEFT || $quote === self::QUOTE_BOTH) {
			$statement->addParam($left);
			$left = QueryStatement::PARAM;
		} else {
			$statement->addIdentifier($left);
			$left = QueryStatement::IDENTIFIER;
		}

		$is_query = $right instanceof Query;
		$is_array = false === $is_query && is_array($right);

		if ($is_array || $is_query) {
			if (false === $is_query || 1 !== $right->getLimit()) {
				// Convert any sort of equality operator to something suitable for arrays
				switch ($operator) {
					// Various forms of equal
					case Query::IN:
						break;
					case Query::EQUAL:
						$operator = Query::IN;
						break;
					case Query::BETWEEN:
						break;
					// Various forms of not equal
					case Query::NOT_IN:
						break;
					case Query::NOT_EQUAL:
					case Query::ALT_NOT_EQUAL:
						$operator = Query::NOT_IN;
						break;
					default:
						throw new Exception($operator . ' unknown for comparing an array.');
				}
			}

			// Right can be a Query, if you're trying to nest queries, like "WHERE MyColumn = (SELECT OtherColumn From MyTable LIMIT 1)"
			if ($is_query) {
				if (!$right->getTable()) {
					throw new Exception('right does not have a table, so it cannot be nested.');
				}

				$clause_statement = $right->getQuery();
				if (null === $clause_statement) {
					return null;
				}

				$right = '(' . $clause_statement->string . ')';
				$statement->addParams($clause_statement->params);
				$statement->addIdentifiers($clause_statement->identifiers);
				if ($quote !== self::QUOTE_LEFT) {
					$quote = self::QUOTE_NONE;
				}
			} elseif ($is_array) {
				$array_len = count($right);
				// BETWEEN
				if (2 === $array_len && $operator === Query::BETWEEN) {
					$statement->string = $left . ' ' . $operator . ' ' . QueryStatement::PARAM . ' AND ' . QueryStatement::PARAM;
					$statement->addParams($right);
					return $statement;
				} elseif (0 === $array_len) {
					// Handle empty arrays
					if ($operator === Query::IN) {
						$statement->string = '(0 = 1)';
						return $statement;
					} elseif ($operator === Query::NOT_IN) {
						return null;
					}
				} elseif ($quote === self::QUOTE_RIGHT || $quote === self::QUOTE_BOTH) {
					$statement->addParams($right);
					$r_string = '(';
					for ($x = 0; $x < $array_len; ++$x) {
						if (0 < $x) {
							$r_string .= ',';
						}
						$r_string .= QueryStatement::PARAM;
					}
					$right = $r_string . ')';
				}
			}
		} else {
			if (null === $right) {
				if ($operator === Query::NOT_EQUAL || $operator === Query::ALT_NOT_EQUAL) {
					// IS NOT NULL
					$operator = Query::IS_NOT_NULL;
				} elseif ($operator === Query::EQUAL) {
					// IS NULL
					$operator = Query::IS_NULL;
				}
			}

			if ($operator === Query::IS_NULL || $operator === Query::IS_NOT_NULL) {
				$right = null;
			} elseif ($quote === self::QUOTE_RIGHT || $quote == self::QUOTE_BOTH) {
				$statement->addParam($right);
				$right = QueryStatement::PARAM;
			}
		}
		$statement->string = $left . ' ' . $operator . ' ' . $right;

		return $statement;
	}

	/**
	 * Alias of addAnd
	 * @return Condition
	 */
	function add($left, $right = null, $operator = Query::EQUAL, $quote = null) {
		if (func_num_args() === 1) {
			return $this->addAnd($left);
		}
		return $this->addAnd($left, $right, $operator, $quote);
	}

	/**
	 * Adds an "AND" condition to the array of conditions.
	 * @param $left mixed
	 * @param $right mixed[optional]
	 * @param $operator string[optional]
	 * @param $quote int[optional]
	 * @return Condition
	 */
	function addAnd($left, $right = null, $operator = Query::EQUAL, $quote = null) {
		if (null === $left) {
			return $this;
		}

		if (is_array($left)) {
			foreach ($left as $key => &$value) {
				$this->addAnd($key, $value);
			}
			return $this;
		}

		$this->conds[] = array('AND', func_get_args());
		return $this;
	}

	/**
	 * @return QueryStatement[]
	 */
	function getAnds() {
		$ors = array();
		foreach ($this->conds as $cond) {
			if ('AND' === $cond[0]) {
				$ors[] = call_user_func_array(array('self', 'processCondition'), $cond[1]);
			}
		}
		return $ors;
	}

	/**
	 * Adds an "OR" condition to the array of conditions
	 * @param $left mixed
	 * @param $right mixed[optional]
	 * @param $operator string[optional]
	 * @param $quote int[optional]
	 * @return Condition
	 */
	function addOr($left, $right = null, $operator = Query::EQUAL, $quote = null) {
		if (null === $left) {
			return $this;
		}

		if (is_array($left)) {
			foreach ($left as $key => &$value) {
				$this->addOr($key, $value);
			}
			return $this;
		}

		$this->conds[] = array('OR', func_get_args());
		return $this;
	}

	/**
	 * @return QueryStatement[]
	 */
	function getOrs() {
		$ors = array();
		foreach ($this->conds as $cond) {
			if ('OR' === $cond[0]) {
				$ors[] = call_user_func_array(array('self', 'processCondition'), $cond[1]);
			}
		}
		return $ors;
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andNot($column, $value) {
		return $this->addAnd($column, $value, Query::NOT_EQUAL);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andLike($column, $value) {
		return $this->addAnd($column, $value, Query::LIKE);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andNotLike($column, $value) {
		return $this->addAnd($column, $value, Query::NOT_LIKE);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andGreater($column, $value) {
		return $this->addAnd($column, $value, Query::GREATER_THAN);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andGreaterEqual($column, $value) {
		return $this->addAnd($column, $value, Query::GREATER_EQUAL);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andLess($column, $value) {
		return $this->addAnd($column, $value, Query::LESS_THAN);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andLessEqual($column, $value) {
		return $this->addAnd($column, $value, Query::LESS_EQUAL);
	}

	/**
	 * @param mixed $column
	 * @return Condition
	 */
	function andNull($column) {
		return $this->addAnd($column, null);
	}

	/**
	 * @param mixed $column
	 * @return Condition
	 */
	function andNotNull($column) {
		return $this->addAnd($column, null, Query::NOT_EQUAL);
	}

	/**
	 * @param mixed $column
	 * @param mixed $from
	 * @param mixed $to
	 * @return Condition
	 */
	function andBetween($column, $from, $to) {
		return $this->addAnd($column, array($from, $to), Query::BETWEEN);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andBeginsWith($column, $value) {
		return $this->addAnd($column, $value, Query::BEGINS_WITH);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andEndsWith($column, $value) {
		return $this->addAnd($column, $value, Query::ENDS_WITH);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function andContains($column, $value) {
		return $this->addAnd($column, $value, Query::CONTAINS);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orNot($column, $value) {
		return $this->addOr($column, $value, Query::NOT_EQUAL);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orLike($column, $value) {
		return $this->addOr($column, $value, Query::LIKE);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orNotLike($column, $value) {
		return $this->addOr($column, $value, Query::NOT_LIKE);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orGreater($column, $value) {
		return $this->addOr($column, $value, Query::GREATER_THAN);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orGreaterEqual($column, $value) {
		return $this->addOr($column, $value, Query::GREATER_EQUAL);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orLess($column, $value) {
		return $this->addOr($column, $value, Query::LESS_THAN);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orLessEqual($column, $value) {
		return $this->addOr($column, $value, Query::LESS_EQUAL);
	}

	/**
	 * @param mixed $column
	 * @return Condition '
	 */
	function orNull($column) {
		return $this->addOr($column, null);
	}

	/**
	 * @param mixed $column
	 * @return Condition
	 */
	function orNotNull($column) {
		return $this->addOr($column, null, Query::NOT_EQUAL);
	}

	/**
	 * @param mixed $column
	 * @param mixed $from
	 * @param mixed $to
	 * @return Condition
	 */
	function orBetween($column, $from, $to) {
		return $this->addOr($column, array($from, $to), Query::BETWEEN);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orBeginsWith($column, $value) {
		return $this->addOr($column, $value, Query::BEGINS_WITH);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orEndsWith($column, $value) {
		return $this->addOr($column, $value, Query::ENDS_WITH);
	}

	/**
	 * @param mixed $column
	 * @param mixed $value
	 * @return Condition
	 */
	function orContains($column, $value) {
		return $this->addOr($column, $value, Query::CONTAINS);
	}

	/**
	 * Builds and returns a string representation of $this Condition
	 * @return QueryStatement
	 */
	function getQueryStatement(DABLPDO $conn = null) {
		if (0 === count($this->conds)) {
			return null;
		}

		$stmnt = new QueryStatement($conn);

		$is_first = true;
		$is_second = false;
		foreach ($this->conds as &$cond) {
			$cond_stmnt = null;

			// avoid call_user_func_array for better stack traces
			switch (count($cond[1])) {
				case 1:
					$cond_stmnt = self::processCondition($cond[1][0]);
					break;
				case 2:
					$cond_stmnt = self::processCondition($cond[1][0], $cond[1][1]);
					break;
				case 3:
					$cond_stmnt = self::processCondition($cond[1][0], $cond[1][1], $cond[1][2]);
					break;
				case 4:
					$cond_stmnt = self::processCondition($cond[1][0], $cond[1][1], $cond[1][2], $cond[1][3]);
					break;
			}

			if (null === $cond_stmnt) {
				continue;
			}

			if ($is_first) {
				$sep = '';
				$is_first = false;
				$is_second = true;
			} else {
				$sep = (($is_second && 'OR' === $this->conds[0][0]) ? 'OR' : $cond[0]) . ' ';
				$is_second = false;
			}

			$stmnt->string .= "\n\t$sep" . $cond_stmnt->string;
			$stmnt->addParams($cond_stmnt->params);
			$stmnt->addIdentifiers($cond_stmnt->identifiers);
		}
		return $stmnt;
	}

	/**
	 * Builds and returns a string representation of $this Condition
	 * @return string
	 */
	function __toString() {
		return (string) $this->getQueryStatement();
	}

}