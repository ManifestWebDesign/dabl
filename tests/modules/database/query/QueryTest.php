<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../../../../config.php';

class QueryTest extends PHPUnit_Framework_TestCase {

	/**
	 * @group subquery
	 * @covers Query::getTable
	 */
	function testGetTableSingleWordWithAlias() {
		$q = new Query('testing', 'alias');
		$this->assertEquals('testing', $q->getTable());
	}
	
	/**
	 * @group subquery
	 * @covers Query::getTable
	 */
	function testGetTableOneParamSingleWordWithAlias() {
		$q = new Query('testing AS alias');
		$this->assertEquals('testing', $q->getTable());
		$this->assertEquals('alias', $q->getAlias());
	}
	
	/**
	 * @group subquery
	 * @covers Query::getTable
	 */
	function testAddJoinWithAlias() {
		$q = new Query('table');
		$q->addJoin('testing AS alias', 'alias.column = table.column');
		foreach ($q->getJoins() as $join) {
			$this->assertEquals('testing', $join->getTable());
			$this->assertEquals('alias', $join->getAlias());
			break;
		}
		$this->assertEquals("SELECT `table`.*\nFROM `table`\n\tJOIN `testing` AS alias ON (alias.column = table.column)", (string) $q->getQuery());
	}
	
	/**
	 * @group subquery
	 * @covers Query::getTable
	 */
	function testGetTableTwoWordsWithAlias() {
		$q = new Query('SELECT testing', 'alias');
		$this->assertEquals('SELECT testing', $q->getTable());
	}

	/**
	 * @group subquery
	 * @covers Query::getQuery
	 */
	function testGetQueryTwoWordsWithAlias() {
		$q = new Query('SELECT testing', 'alias');
		$this->assertEquals("SELECT alias.*\nFROM SELECT testing AS alias", (string) $q->getQuery());
	}
	
	/**
	 * @group subquery
	 * @covers Query::getTable
	 */
	function testGetTableSingleWordNoAlias() {
		$q = new Query('testing');
		$this->assertEquals('testing', $q->getTable());
	}
	
	/**
	 * @group subquery
	 * @covers Query::getAlias
	 */
	function testGetAliasSingleWordWithAlias() {
		$q = new Query('testing', 'alias');
		$this->assertEquals('alias', $q->getAlias());
	}

	/**
	 * @group subquery
	 * @covers Query::getAlias
	 */
	function testGetAliasTwoWordsWithAlias() {
		$q = new Query('SELECT testing', 'alias');
		$this->assertEquals('alias', $q->getAlias());
	}

	/**
	 * @group subquery
	 * @covers Query::getAlias
	 */
	function testGetAliasSingleWordNoAlias() {
		$q = new Query('testing');
		$this->assertNull($q->getAlias());
	}
}
