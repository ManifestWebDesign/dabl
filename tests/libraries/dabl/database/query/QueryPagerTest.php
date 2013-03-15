<?php

class SelectableTable {

	static function doCount() {
	}

	static function doSelect() {
	}

}

class QueryPagerTest extends PHPUnit_Framework_TestCase {

	public function testConstructorAutoDetectsClassName() {
		$q = new Query('my_schema.selectable_table');
		$qp = new QueryPager($q);
		$this->assertEquals('SelectableTable', $qp->getClass());
	}

	/**
	 * @covers QueryPager::setQuery
	 * @todo Implement testSetQuery().
	 */
	public function testSetQuery() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::setClass
	 * @todo Implement testSetClass().
	 */
	public function testSetClass() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getClass
	 * @todo Implement testGetClass().
	 */
	public function testGetClass() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::setMethod
	 * @todo Implement testSetMethod().
	 */
	public function testSetMethod() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::setPageNum
	 * @todo Implement testSetPageNum().
	 */
	public function testSetPageNum() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getPageNum
	 * @todo Implement testGetPageNum().
	 */
	public function testGetPageNum() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getPrevPageNum
	 * @todo Implement testGetPrevPageNum().
	 */
	public function testGetPrevPageNum() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getNextPageNum
	 * @todo Implement testGetNextPageNum().
	 */
	public function testGetNextPageNum() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getLimit
	 * @todo Implement testGetLimit().
	 */
	public function testGetLimit() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::setLimit
	 * @todo Implement testSetLimit().
	 */
	public function testSetLimit() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getOffset
	 * @todo Implement testGetOffset().
	 */
	public function testGetOffset() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getTotal
	 * @todo Implement testGetTotal().
	 */
	public function testGetTotal() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getStart
	 * @todo Implement testGetStart().
	 */
	public function testGetStart() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getEnd
	 * @todo Implement testGetEnd().
	 */
	public function testGetEnd() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::isLastPage
	 * @todo Implement testIsLastPage().
	 */
	public function testIsLastPage() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::isFirstPage
	 * @todo Implement testIsFirstPage().
	 */
	public function testIsFirstPage() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getPageCount
	 * @todo Implement testGetPageCount().
	 */
	public function testGetPageCount() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::fetchPage
	 * @todo Implement testFetchPage().
	 */
	public function testFetchPage() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers QueryPager::getPagerLinks
	 * @todo Implement testGetPagerLinks().
	 */
	public function testGetPagerLinks() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}