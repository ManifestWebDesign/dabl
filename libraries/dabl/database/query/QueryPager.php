<?php

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

	/**
	 * @param Query $q
	 * @param int $limit
	 * @param int $page
	 * @param string $class_name
	 */
	function __construct(Query $q, $limit=null, $page=null, $class_name = null) {
		$this->setQuery($q);
		if ($class_name)
			$this->setClass($class_name);
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
	 * Sets the current page number
	 * @param int $page_number
	 */
	function setPageNum($page_number) {
		if (!is_numeric($page_number) || $page_number < 1)
			$page_number = 1;

		if ($page_number > $this->getPageCount())
			$this->page = $this->getPageCount();
		else
			$this->page = $page_number;
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
		if (!$this->isFirstPage())
			return $this->page - 1;
		return 1;
	}

	/**
	 * @return int
	 */
	function getNextPageNum() {
		if (!$this->isLastPage())
			return $this->page + 1;
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
		if (!is_numeric($per_page) || $per_page < 1)
			$per_page = 50;
		$this->limit = $per_page;
	}

	/**
	 * Gets the offset for a query
	 */
	function getOffset() {
		$offset = ($this->getPageNum() * $this->limit) - $this->limit;
		if ($offset < 0)
			$offset = 0;
		return $offset;
	}

	/**
	 * Gets the count of the results of the query
	 */
	function getTotal() {
		if ($this->total !== null)
			return $this->total;
		if ($this->className)
			$total = call_user_func_array(array($this->className, 'doCount'), array($this->query));
		else
			$total = $this->query->doCount();
		$this->total = $total;
		return $total;
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
		$q = clone $this->query;
		$q->setLimit($this->getLimit());
		$q->setOffset($this->getOffset());

		if ($this->className)
			return call_user_func_array(array($this->className, 'doSelect'), array($q));
		else
			return $q->doSelect();
	}

	function getPagerLinks($url_format, $limit = 20, $css_class='', $css_class_current='', $link_text=array()) {
		throw new Exception(__METHOD__ . ' is deprectated');
	}

	/**
	 * Generates pagination html in the form of <a> tags
	 * @param string $url_format The url to format paging links with, ie: /actu/index.html?page=$page_num. $page_num to be replaced with page number.
	 * @param int $page_limit For limiting the number of page number links
	 * @param array $labels ie: array("first" => "go to first page", "prev" => "previous", "next" => "next", "first" => "go to last page")
	 * @return string Html paging content
	 */
	function getLinks($url_format, $page_limit = null, $labels=array()) {
		$default_labels = array(
			'first' => '&laquo;',
			'prev' => '&lsaquo;',
			'next' => '&rsaquo;',
			'last' => '&raquo;'
		);
		$labels = array_merge($default_labels, $labels);

		$page_limit = intval($page_limit);
		if (!$page_limit)
			$page_limit = 9;
		$mid_page_limit = $page_limit >> 1;
		$page = $this->getPageNum();
		$count = $this->getPageCount();
		$start = max(1, min($count - $page_limit, $page - $mid_page_limit));
		$end = min($count, max($page_limit, $page + $mid_page_limit));

		$str = '';
		if ($count !== 1):
			$str .= <<<EOF
		Page:
EOF;
			if ($page > 1):
				$link = site_url(str_replace('$page_num', 1, $url_format));
				$str .= <<<EOF
			<a href="{$link}">{$labels['first']}</a>
EOF;

				$link = site_url(str_replace('$page_num', $page - 1, $url_format));
				$str .= <<<EOF
			<a href="{$link}">{$labels['prev']}</a>
EOF;
			endif;

			for ($i = $start; $i <= $end; ++$i):
				$str .= <<<EOF
EOF;
 if ($i == $page):
					$str .= <<<EOF
			<span>{$i}</span>
EOF;
				else:
					$link = site_url(str_replace('$page_num', $i, $url_format));
					$str .= <<<EOF
			<a href="{$link}">{$i}</a>
EOF;
				endif;
				$str .= <<<EOF
EOF;
 endfor;

			if ($page < $count):
				$link = site_url(str_replace('$page_num', $page + 1, $url_format));
				$str .= <<<EOF
			<a href="{$link}">{$labels['next']}</a>
EOF;

				$link = site_url(str_replace('$page_num', $count, $url_format));
				$str .= <<<EOF
			<a href="{$link}">{$labels['last']}</a>
EOF;
			endif;
		endif;

		return $str;
	}

}
