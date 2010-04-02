<?php

/**
 * Handles all of your paginating needs.
 * 
 * Example without className:
 * 
 * //New query with table name
 * $q = new Query('Client');
 * $q->add('Column','Value');
 * 
 * //Limit results per page
 * $limit = 50;
 * 
 * //Specify the current page
 * $page = 3;
 * 
 * //Create instance of pager
 * $pager = new QueryPager($q, $limit, $page);
 * 
 * //Retrieve PDOStatement with results for that page
 * $resultSet = $pager->fetchPage();
 * 
 * 
 * 
 * Example with className:
 * 
 * //New query
 * $q = new Query;
 * $q->add('Column','Value');
 * 
 * //Limit results per page
 * $limit = 50;
 * 
 * //Specify the current page
 * $page = 2;
 * 
 * //Create instance of pager, provide the name of the DABL class
 * $pager = new QueryPager($q, $limit, $page, 'Inspection');
 * 
 * //Retrieve an array of Objects from the DABL class for that page
 * $inspections = $pager->fetchPage();
 */
class QueryPager{
	
	private $page = 1;
	private $limit = 50;
	private $total;
	
	private $query;
	private $classInstance;

	function __construct(Query $q, $limit=null, $page=null, $className = null){
		$this->setQuery($q);
		if($className)
			$this->setClass($className);
		$this->setLimit($limit);
		$this->setPageNum($page);
	}
	
	function setQuery(Query $q){
		$this->query = $q;
		$this->total = null;
	}
	
	/**
	 * Sets the name of the class to use for counting and selecting
	 * results
	 */
	function setClass($className){
		$this->classInstance = new $className;
	}

	/**
	 * Sets the current page number
	 */
	function setPageNum($pageNumber){
		if(!is_numeric($pageNumber) || $pageNumber<1)
			$pageNumber = 1;
		
		if($pageNumber>$this->getPageCount())
			$this->page = $this->getPageCount();
		else
			$this->page = $pageNumber;
	}
	
	/**
	 * Gets the current page number
	 */
	function getPageNum(){
		return $this->page;
	}
	
	function getPrevPageNum(){
		if(!$this->isFirstPage())
			return $this->page-1;
		return 1;
	}
	
	function getNextPageNum(){
		if(!$this->isLastPage())
			return $this->page+1;
		return 1;
	}
	
	/**
	 * Returns the maximum number of results per page
	 */
	function getLimit(){
		return $this->limit;
	}
	
	/**
	 * Sets the maximum number of results per page
	 */
	function setLimit($per_page){
		if(!is_numeric($per_page) || $per_page<1)
			$per_page = 50;
		$this->limit = $per_page;
	}

	/**
	 * Gets the offset for a query
	 */
	function getOffset(){
		$offset = ($this->getPageNum() * $this->limit) - $this->limit;
		if($offset<0)$offset = 0;
		return $offset;
	}

	/**
	 * Gets the count of the results of the query
	 */
	function getTotal(){
		if($this->total!==null)return $this->total;
		if($this->classInstance)
			$total = $this->classInstance->doCount($this->query);
		else
			$total = $this->query->doCount();
		$this->total = $total;
		return $total;
	}
	
	/**
	 * Gets the number of the first record on the page
	 */
	function getStart(){
		if($this->getTotal()==0)return 0;
		return $this->getOffset()+1;
	}

	/**
	 * Gets the number of the last record on the page
	 */
	function getEnd(){
		$end = $this->getPageNum() * $this->limit;
		if($end>$this->getTotal())
			return $this->getTotal();
		return $end;
	}

	/**
	 * Returns true if the current page is equal to
	 * the total number of pages
	 */
	function isLastPage(){
		return ($this->getPageCount()==$this->getPageNum());
	}
	
	/**
	 * Returns true if the current page is 1
	 */
	function isFirstPage(){
		return ($this->getPageNum()==1);
	}

	/**
	 * Gets the total number of pages
	 */
	function getPageCount(){
		$rem = ($this->getTotal()%$this->limit);
		$rem = ($rem>=0) ? $rem : 0;
		$total_pages = ($this->getTotal()-$rem)/$this->limit;
		if($rem)$total_pages++;
		return $total_pages;
	}

	/**
	 * Executes the query for the current page and returns either 
	 * an array of DABL objects (if a classname was provided) or a PDOStatement
	 */
	function fetchPage(){
		$q = clone $this->query;
		$q->setLimit($this->getLimit());
		$q->setOffset($this->getOffset());
		if($this->classInstance)
			return $this->classInstance->doSelect($q);
		else return $q->doSelect();
	}

	/**
	 * Generates pagination html in the form of <a> tags
	 * @param string $urlFormat The url to format paging links with, ie: /actu/index.html?page=$pageNum. $pageNum to be replaced with page number.
	 * @param int $limit For limiting the number of page number links
	 * @param string $CSSClass The link tag of the others
	 * @param string $CSSClassCurrent The link tag css class where we are
	 * @param array  $linkText Ie: array("first" => "go to first page", "prev" => "previous", "next" => "next", "first" => "go to last page")
	 * @return string Html paging content
	 */
	function getPagerLinks($urlFormat, $limit = 20, $CSSClass='', $CSSClassCurrent='', $linkText=array()){
		$currentpage = $this->getPageNum();
		if($limit)
			$nbpages = min(array($this->getPageCount(), $limit));
		$str = '';

		if (@$linkText['first'] && !$this->isFirstPage())
			$str .= '<a class="'.$CSSClass.'" href="'.str_replace('$pageNum', 1, $urlFormat).'">'.$linkText['first'].'</a>';

		if (@$linkText['prev'] && !$this->isFirstPage())
			$str .= '<a class="'.$CSSClass.'" href="'.str_replace('$pageNum', $this->getPrevPageNum(), $urlFormat).'">'.$linkText['prev'].'</a>';

		for($i=1; $i<=$nbpages; $i++)
			$str .= '<a class="'.($i==$currentpage ? $CSSClassCurrent : $CSSClass).'" href="'.str_replace('$pageNum', $i, $urlFormat).'">'.$i.'</a>';

		if (@$linkText['next'] && !$this->isLastPage())
			$str .= '<a class="'.$CSSClass.'" href="'.str_replace('$pageNum', $this->getNextPageNum(), $urlFormat).'">'.$linkText['next'].'</a>';

		if (@$linkText['last'] && !$this->isLastPage())
			$str .= '<a class="'.$CSSClass.'" href="'.str_replace('$pageNum', $nbpages, $urlFormat).'">'.$linkText['last'].'</a>';

		return $str;
	}

}
?>