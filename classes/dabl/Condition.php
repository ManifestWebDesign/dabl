<?
/**
 * Last Modified June 19th 2009
 */

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
class Condition{
	const QUOTE_LEFT = 1;
	const QUOTE_RIGHT = 2;
	const QUOTE_BOTH = 3;
	const QUOTE_NONE = 4;

	private $ands = array();
	private $ors = array();

	/**
	 * Used by the addAnd and addOr methods to translate the arguments into a condition String
	 * @param $left Mixed
	 * @param $right Mixed[optional]
	 * @param $operator String[optional]
	 * @param $quote Int[optional]
	 * @return String
	 */
	private function processCondition($left, $right=null, $operator=Query::EQUAL, $quote = self::QUOTE_RIGHT){
		//Left can be a Condition
		if($left instanceof self){
			if(!$left->getClause())
				return null;
			return "(".$left->getClause().")";
		}

		//Right can be a Query, if you're trying to nest queries, like "WHERE MyColumn = (SELECT OtherColumn From MyTable LIMIT 1)"
		if($right instanceof Query){
			if(!$right->getTable())
				throw new Exception("$right does not have a table, so it cannot be nested.");
			$right = "($right)";
		}

		//You can skip $operator and specify $quote with parameter 3
		if(is_int($operator) && !$quote){
			$quote = $operator;
			$operator = Query::EQUAL;
		}

		//Get rid of white-space on sides of $operator
		$operator = trim($operator);

		//Escape $left
		if($quote == self::QUOTE_LEFT || $quote == self::QUOTE_BOTH)
			$left = DB::checkInput($left);

		//$right can be an array
		if(is_array($right)){
			//Escape $right (recursive)
			if($quote == self::QUOTE_RIGHT || $quote == self::QUOTE_BOTH)
				$right = DB::checkInput($right);

			//BETWEEN
			if(count($right)==2 && $operator==Query::BETWEEN)
				return "$left $operator ".$right[0]." AND ".$right[1];

			//IN or NOT_IN
			$right = "(".implode(',',$right).")";

			switch($operator){
				//Various forms of equal
				case Query::IN:
				case Query::EQUAL:
					$operator=Query::IN;
					break;
				//Various forms of not equal
				case Query::NOT_IN:
				case Query::NOT_EQUAL:
				case Query::ALT_NOT_EQUAL:
					$operator=Query::NOT_IN;
					break;
				default:
					throw new Exception("$operator unknown for comparing an array.");
			}
			return "$left $operator $right";
		}

		//IS NOT NULL
		if($right===null && ($operator==Query::NOT_EQUAL || $operator==Query::ALT_NOT_EQUAL))
			$operator=Query::IS_NOT_NULL;

		//IS NULL
		elseif($right===null && $operator=="=")
			$operator=Query::IS_NULL;

		if($operator==Query::IS_NULL || $operator==Query::IS_NOT_NULL)
			$right=null;
		elseif($quote == self::QUOTE_RIGHT || $quote == self::QUOTE_BOTH)
			$right = DB::checkInput($right);

		return "$left $operator $right";
	}

	/**
	 * Alias of addAnd
	 * @return Condition
	 */
	public function add($left, $right=null, $operator=Query::EQUAL, $quote = self::QUOTE_RIGHT){
		return $this->addAnd($left, $right, $operator, $quote);
	}

	/**
	 * Adds an "AND" condition to the array of conditions.
	 * @param $left Mixed
	 * @param $right Mixed[optional]
	 * @param $operator String[optional]
	 * @param $quote Int[optional]
	 * @return Condition
	 */
	public function addAnd($left, $right=null, $operator=Query::EQUAL, $quote = self::QUOTE_RIGHT){
		$condition = $this->processCondition($left, $right, $operator, $quote);
		if($condition)
			$this->ands[] = $condition;
		return $this;
	}

	/**
	 * Adds an "OR" condition to the array of conditions
	 * @param $left Mixed
	 * @param $right Mixed[optional]
	 * @param $operator String[optional]
	 * @param $quote Int[optional]
	 * @return Condition
	 */
	public function addOr($left, $right=null, $operator=Query::EQUAL, $quote = self::QUOTE_RIGHT){
		$condition = $this->processCondition($left, $right, $operator, $quote);
		if($condition)
			$this->ors[] = $condition;
		return $this;
	}

	/**
	 * Builds and returns a String representation of $this Condition
	 * @return String
	 */
	public function getClause(){
		$where = null;

		$ands = $this->ands;
		$ors = $this->ors;

		if($ands || $ors){
			if($ands) $AND = implode(" AND ", $ands);
			if($ors) $OR = implode(" OR ", $ors);
			if($ands && $ors)
				$where .= " $AND OR $OR ";
			elseif($ands)
				$where .= " $AND ";
			elseif($ors)
				$where .= " $OR ";
		}

		return $where;
	}

	/**
	 * Builds and returns a String representation of $this Condition
	 * @return String
	 */
	public function __toString(){
		return $this->getClause();
	}
}
?>