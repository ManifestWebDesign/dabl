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
	 * @return string
	 */
	private static function processCondition($left = null, $right = null, $operator = Query::EQUAL, $quote = null) {
		if (1 === func_num_args() && $left instanceof QueryStatement) {
			return $left;
		}

		$statement = new QueryStatement;

		// Left can be a Condition
		if ($left instanceof self) {
			$clause_statement = $left->getQueryStatement();
			if (!$clause_statement) {
				return null;
			}
			$clause_statement->setString('(' . $clause_statement->getString() . ')');
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

		// Get rid of white-space on sides of $operator
		$operator = trim($operator);

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

		$is_array = false;
		if (is_array($right) || ($right instanceof Query && $right->getLimit() !== 1)) {
			$is_array = true;
		}

		// Right can be a Query, if you're trying to nest queries, like "WHERE MyColumn = (SELECT OtherColumn From MyTable LIMIT 1)"
		if ($right instanceof Query) {
			if (!$right->getTable()) {
				throw new Exception("$right does not have a table, so it cannot be nested.");
			}

			$clause_statement = $right->getQuery();
			if (!$clause_statement) {
				return null;
			}

			$right = '(' . $clause_statement->getString() . ')';
			$statement->addParams($clause_statement->getParams());
			$statement->addIdentifiers($clause_statement->getIdentifiers());
			if ($quote != self::QUOTE_LEFT) {
				$quote = self::QUOTE_NONE;
			}
		}

		// $right can be an array
		if ($is_array) {
			// BETWEEN
			if (is_array($right) && count($right) === 2 && $operator === Query::BETWEEN) {
				$statement->setString("$left $operator " . QueryStatement::PARAM . ' AND ' . QueryStatement::PARAM);
				$statement->addParams($right);
				return $statement;
			}

			// Convert any sort of equal operator to something suitable
			// for arrays
			switch ($operator) {
				//Various forms of equal
				case Query::IN:
				case Query::EQUAL:
					$operator = Query::IN;
					break;
				//Various forms of not equal
				case Query::NOT_IN:
				case Query::NOT_EQUAL:
				case Query::ALT_NOT_EQUAL:
					$operator = Query::NOT_IN;
					break;
				default:
					throw new Exception("$operator unknown for comparing an array.");
			}

			// Handle empty arrays
			if (is_array($right) && !$right) {
				if ($operator == Query::IN) {
					$statement->setString('(0=1)');
					$statement->setParams(array());
					$statement->setIdentifiers(array());
					return $statement;
				} elseif ($operator == Query::NOT_IN) {
					return null;
				}
			}

			// IN or NOT_IN
			if ($quote === self::QUOTE_RIGHT || $quote === self::QUOTE_BOTH) {
				$statement->addParams($right);
				$placeholders = array();
				foreach ($right as &$r) {
					$placeholders[] = QueryStatement::PARAM;
				}
				$right = '(' . implode(',', $placeholders) . ')';
			}
		} else {
			// IS NOT NULL
			if ($right === null && ($operator === Query::NOT_EQUAL || $operator === Query::ALT_NOT_EQUAL)) {
				$operator = Query::IS_NOT_NULL;
			}

			// IS NULL
			elseif ($right === null && $operator === Query::EQUAL) {
				$operator = Query::IS_NULL;
			}

			if ($operator === Query::IS_NULL || $operator === Query::IS_NOT_NULL) {
				$right = null;
			} elseif ($quote === self::QUOTE_RIGHT || $quote === self::QUOTE_BOTH) {
				$statement->addParam($right);
				$right = QueryStatement::PARAM;
			}
		}
		$statement->setString("$left $operator $right");

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
		throw new Exception("Condition::getAnds() can't do what you want anymore...");
//		$ors = array();
//		foreach ($this->conds as $cond) {
//			if ('AND' === $cond[0]) {
//				$ors[] = call_user_func_array(array('self', 'processCondition'), $cond[1]);
//			}
//		}
//		return $ors;
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
		throw new Exception("Condition::getOrs() can't do what you want anymore...");
//		$ands = array();
//		foreach ($this->conds as $cond) {
//			if ('AND' === $cond[0]) {
//				$ands[] = call_user_func_array(array('self', 'processCondition'), $cond[1]);
//			}
//		}
//		return $ands;
	}

	/**
	 * Builds and returns a string representation of $this Condition
	 * @return QueryStatement
	 */
	function getQueryStatement() {
		if (0 === count($this->conds)) {
			return null;
		}

		$statement = new QueryStatement;
		$string = '';

		foreach ($this->conds as $num => $cond) {
			if (0 !== $num) {
				$sep = ((1 === $num && 'OR' === $this->conds[0][0]) ? 'OR' : $cond[0]) . ' ';
			} else {
				$sep = '';
			}
			// avoid call_user_func_array for better stack traces
			switch (count($cond[1])) {
				case 1:
					$cond = self::processCondition($cond[1][0]);
					break;
				case 2:
					$cond = self::processCondition($cond[1][0], $cond[1][1]);
					break;
				case 3:
					$cond = self::processCondition($cond[1][0], $cond[1][1], $cond[1][2]);
					break;
				case 4:
					$cond = self::processCondition($cond[1][0], $cond[1][1], $cond[1][2], $cond[1][3]);
					break;
			}
			$string .= "\n\t$sep" . $cond->getString();
			$statement->addParams($cond->getParams());
			$statement->addIdentifiers($cond->getIdentifiers());
		}
		$statement->setString($string);
		return $statement;
	}

	/**
	 * Builds and returns a string representation of $this Condition
	 * @return string
	 */
	function __toString() {
		return (string) $this->getQueryStatement();
	}

}