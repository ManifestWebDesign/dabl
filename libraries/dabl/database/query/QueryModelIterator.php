<?php

/**
 * This Iterator returns instantiated DABL Model classes. The objects are instantiated as the
 * collection is iterated over, keeping things spritely and memory-friendly.
 */
class QueryModelIterator implements Iterator, JsonSerializable {

	/**
	 * @var Query
	 */
	protected $query;

	/**
	 * @var string
	 */
	protected $class;

	/**
	 * @var Model
	 */
	protected $current = null;

	/**
	 * @var int
	 */
	protected $position = 0;

	/**
	 * @var PDOStatement
	 */
	protected $pdoStatement;

	/**
	 * Throws UnexpectedValueException if the row class, either gleaned from Query::getTable() or
	 * provided directly, does not exist.
	 * Throws UnexpectedValueException if the above class does not have Model in its ancestry.
	 *
	 * @param Query $query
	 * @param string $class = null
	 */
	public function __construct(Query $query, $class = null) {
		if ($class) {
			$this->class = trim($class);
		} else {
			$this->class = trim(StringFormat::className($query->getTable()));
		}

		if (!class_exists($this->class)) {
			throw new UnexpectedValueException(sprintf('Class "%s" does not exist.', $this->class));
		}

		if (!is_a($this->class, 'Model', true)) {
			throw new UnexpectedValueException(sprintf('Class "%s" does not inherit from Model.', $this->class));
		}

		$this->query = $query;
	}

	protected function initQuery() {
		$this->pdoStatement = call_user_func(array($this->class, 'doSelectRS'), $this->query);
		$this->pdoStatement->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->class);
		$this->position = 0;
	}

	protected function fetchRow() {
		$this->current = $this->pdoStatement->fetch();

		if ($this->current) {
			$this->current->castInts();
			$this->current->setNew(false);
		}
	}

	/**
	 * @return string
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * @return Query
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @return null
	 */
	public function rewind() {
		$this->initQuery();
	}

	/**
	 * @return boolean
	 */
	public function valid() {
		if (null === $this->current) {
			$this->fetchRow();
		}
		return ($this->current !== false);
	}

	/**
	 * @return mixed
	 */
	public function current() {
		if (null === $this->current) {
			$this->fetchRow();
		}

		return $this->current;
	}

	/**
	 * @return scalar
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * @return null
	 */
	public function next() {
		$this->fetchRow();
		++$this->position;
	}

	/**
	 * WARNING: will not work correctly with all PDO drivers!
	 * @see http://www.php.net/manual/en/pdostatement.rowcount.php
	 * @return int
	 */
	public function count() {
		return $this->pdoStatement->rowCount();
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		$array = array();

		foreach ($this as $object) {
			$array[] = $object->jsonSerialize();
		}

		return $array;
	}

}
