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

	private $ands = array();
	private $ors = array();

	function __construct($left = null, $right=null, $operator=Query::EQUAL, $quote = null) {
		if ($left !== null) {
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
	static function create($left = null, $right=null, $operator=Query::EQUAL, $quote = null) {
		return new self($left, $right, $operator, $quote);
	}

	/**
	 * @return string
	 */
	private function processCondition($left = null, $right=null, $operator=Query::EQUAL, $quote = null) {
		if ($left === null) {
			return null;
		}

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

		if ($quote === null) {
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

		// Escape $left
		if ($quote == self::QUOTE_LEFT || $quote == self::QUOTE_BOTH) {
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
			if (is_array($right) && count($right) == 2 && $operator == Query::BETWEEN) {
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
			if ($quote == self::QUOTE_RIGHT || $quote == self::QUOTE_BOTH) {
				$statement->addParams($right);
				$placeholders = array();
				foreach ($right as &$r) {
					$placeholders[] = QueryStatement::PARAM;
				}
				$right = '(' . implode(',', $placeholders) . ')';
			}
		} else {
			// IS NOT NULL
			if ($right === null && ($operator == Query::NOT_EQUAL || $operator == Query::ALT_NOT_EQUAL)) {
				$operator = Query::IS_NOT_NULL;
			}

			// IS NULL
			elseif ($right === null && $operator == Query::EQUAL) {
				$operator = Query::IS_NULL;
			}

			if ($operator == Query::IS_NULL || $operator == Query::IS_NOT_NULL) {
				$right = null;
			} elseif ($quote == self::QUOTE_RIGHT || $quote == self::QUOTE_BOTH) {
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
	function add($left, $right=null, $operator=Query::EQUAL, $quote = null) {
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
	function addAnd($left, $right=null, $operator=Query::EQUAL, $quote = null) {
		if (is_array($left)) {
			foreach ($left as $key => &$value) {
				$this->addAnd($key, $value);
			}
			return $this;
		}

		if (func_num_args() === 1) {
			$condition = $this->processCondition($left);
		} else {
			$condition = $this->processCondition($left, $right, $operator, $quote);
		}

		if ($condition) {
			$this->ands[] = $condition;
		}

		return $this;
	}

	/**
	 * @return QueryStatement[]
	 */
	function getAnds() {
		return $this->ands;
	}

	/**
	 * Adds an "OR" condition to the array of conditions
	 * @param $left mixed
	 * @param $right mixed[optional]
	 * @param $operator string[optional]
	 * @param $quote int[optional]
	 * @return Condition
	 */
	function addOr($left, $right=null, $operator=Query::EQUAL, $quote = null) {
		if (is_array($left)) {
			foreach ($left as $key => &$value) {
				$this->addOr($key, $value);
			}
			return $this;
		}

		if (func_num_args() === 1) {
			$condition = $this->processCondition($left);
		} else {
			$condition = $this->processCondition($left, $right, $operator, $quote);
		}
		if ($condition) {
			$this->ors[] = $condition;
		}
		return $this;
	}

	/**
	 * @return QueryStatement[]
	 */
	function getOrs() {
		return $this->ors;
	}

	/**
	 * Builds and returns a string representation of $this Condition
	 * @return QueryStatement
	 */
	function getQueryStatement() {
		$statement = new QueryStatement;
		$string = '';

		$and_strings = array();
		foreach ($this->ands as $and_statement) {
			$and_strings[] = $and_statement->getString();
			$statement->addParams($and_statement->getParams());
			$statement->addIdentifiers($and_statement->getIdentifiers());
		}
		if ($and_strings) {
			$AND = implode("\n\tAND ", $and_strings);
		}

		$or_strings = array();
		foreach ($this->ors as $or_statement) {
			$or_strings[] = $or_statement->getString();
			$statement->addParams($or_statement->getParams());
			$statement->addIdentifiers($or_statement->getIdentifiers());
		}
		if ($or_strings) {
			$OR = implode("\n\tOR ", $or_strings);
		}

		if ($and_strings || $or_strings) {
			if ($and_strings && $or_strings) {
				$string .= "\n\t$AND\n\tOR $OR";
			} elseif ($and_strings) {
				$string .= "\n\t$AND";
			} elseif ($or_strings) {
				$string .= "\n\t$OR";
			}
			$statement->setString($string);
			return $statement;
		}
		return null;
	}
	
	/**
	 * Builds and returns a string representation of $this Condition
	 * @return string
	 */
	function __toString() {
		return (string) $this->getQueryStatement();
	}

}
