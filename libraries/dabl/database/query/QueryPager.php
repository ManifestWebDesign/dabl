<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

/**
 * Handles all of your paginating needs.
 *
 * Example without className:
 *
 * {@example libraries/dabl/database/query/QueryPager_description_1.php}
 *
 * Example with className:
 *
 * {@example libraries/dabl/database/query/QueryPager_description_2.php}
 *
 */
class QueryPager {

	private $page = 1;
	private $limit = 50;
	private $total;

	/**
	 * @var Query
	 */
	private $query;
	private $className;
	private $methodName;

	function toArray() {
		return array(
			'total' => $this->getTotal(),
			'limit' => $this->getLimit(),
			'pageCount' => $this->getPageCount(),
			'page' => $this->getPageNum(),
			'offset' => $this->getOffset(),
			'start' => $this->getStart(),
			'end' => $this->getEnd()
		);
	}

	/**
	 * @param Query $q
	 * @param int $limit
	 * @param int $page
	 * @param string $class_name
	 */
	function __construct(Query $q, $limit = null, $page = null, $class_name = null, $select_method_name = 'doSelect') {
		$this->setQuery($q);
		if ($class_name) {
			$this->setClass($class_name);
		} elseif (func_num_args() < 4 && ($table = $q->getTable()) && !($table instanceof Query)) {
			$table_parts = explode('.', $table);
			$table_name = array_pop($table_parts);
			$this->setClass(StringFormat::className($table_name));
		}
		if ($select_method_name) {
			$this->setMethod($select_method_name);
		}
		$this->setLimit($limit);
		$this->setPageNum($page);
	}

	/**
	 * @param Query $q
	 */
	function setQuery(Query $q) {
		$this->query = $q;
		$this->total = null;
	}

	/**
	 * Sets the name of the class to use for counting and selecting results
	 * @param string $class_name
	 */
	function setClass($class_name) {
		$this->className = $class_name;
	}

	/**
	 * Returns the name of the class to use for counting and selecting results
	 * @return string
	 */
	function getClass() {
		return $this->className;
	}

	/**
	 * Sets the name of the method to use for selecting results
	 * @param string $method_name
	 */
	function setMethod($select_method_name) {
		$this->methodName = $select_method_name;
	}

	/**
	 * Sets the current page number
	 * @param int $page_number
	 */
	function setPageNum($page_number) {
		$this->page = $page_number;

		if (!is_numeric($this->page) || $this->page < 1) {
			$this->page = 1;
		}

		if ($this->total !== null) {
			$this->sanitizePageNum();
		}
	}

	/**
	 * Ensure page num does not exceed page
	 * count. Lazy sanitized because page count is not known
	 * until after limit is set and count query is run.
	 */
	function sanitizePageNum() {
		if ($this->page > $this->getPageCount()) {
			$this->page = $this->getPageCount();
		}
	}

	/**
	 * Gets the current page number
	 */
	function getPageNum() {
		return $this->page;
	}

	/**
	 * @return int
	 */
	function getPrevPageNum() {
		if (!$this->isFirstPage()) {
			return $this->page - 1;
		}
		return 1;
	}

	/**
	 * @return int
	 */
	function getNextPageNum() {
		if (!$this->isLastPage()) {
			return $this->page + 1;
		}
		return 1;
	}

	/**
	 * Returns the maximum number of results per page
	 */
	function getLimit() {
		return $this->limit;
	}

	/**
	 * Sets the maximum number of results per page
	 * @param int $per_page
	 */
	function setLimit($per_page) {
		if (!is_numeric($per_page) || $per_page < 1) {
			$per_page = 50;
		}
		$this->limit = $per_page;
	}

	/**
	 * Gets the offset for a query
	 */
	function getOffset() {
		$offset = ($this->getPageNum() * $this->limit) - $this->limit;
		if ($offset < 0) {
			$offset = 0;
		}
		return $offset;
	}

	/**
	 * Gets the count of the results of the query
	 */
	function getTotal() {
		if ($this->total === null) {
			$q = clone $this->query;
			$q->setLimit(null);
			$q->setOffset(null);
			if ($this->className) {
				$total = call_user_func(array($this->className, 'doCount'), $q);
			} else {
				$total = $q->doCount();
			}
			$this->total = $total;
		}
		return $this->total;
	}

	/**
	 * Gets the number of the first record on the page
	 */
	function getStart() {
		if ($this->getTotal() == 0)
			return 0;
		return $this->getOffset() + 1;
	}

	/**
	 * Gets the number of the last record on the page
	 */
	function getEnd() {
		$end = $this->getPageNum() * $this->limit;
		if ($end > $this->getTotal())
			return $this->getTotal();
		return $end;
	}

	/**
	 * Returns true if the current page is equal to
	 * the total number of pages
	 */
	function isLastPage() {
		return ($this->getPageCount() == $this->getPageNum());
	}

	/**
	 * Returns true if the current page is 1
	 */
	function isFirstPage() {
		return ($this->getPageNum() == 1);
	}

	/**
	 * Gets the total number of pages
	 */
	function getPageCount() {
		$rem = ($this->getTotal() % $this->limit);
		$rem = ($rem >= 0) ? $rem : 0;
		$total_pages = ($this->getTotal() - $rem) / $this->limit;
		if ($rem)
			$total_pages++;
		return $total_pages;
	}

	/**
	 * Executes the query for the current page and returns either
	 * an array of DABL objects (if a classname was provided) or a PDOStatement
	 */
	function fetchPage() {
		$this->sanitizePageNum();
		$q = clone $this->query;
		$q->setLimit($this->getLimit());
		$q->setOffset($this->getOffset());

		if ($this->className) {
			return call_user_func(array($this->className, $this->methodName), $q);
		} else {
			return $q->doSelect();
		}
	}

}