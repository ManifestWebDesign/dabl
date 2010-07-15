<?php

require_once 'PHPUnit/Framework.php';
require_once '../config.php';

class TestQuery extends PHPUnit_Framework_TestCase {

	/**
	 * @group subquery
	 * @covers Query::getTableName
	 */
	function testGetTableNameSingleWordWithAlias() {
		$q = new Query('testing', 'alias');
		$this->assertEquals('testing', $q->getTableName());
	}

	/**
	 * @group subquery
	 * @covers Query::getTableName
	 */
	function testGetTableNameTwoWordsWithAlias() {
		$q = new Query('SELECT testing', 'alias');
		$this->assertEquals('SELECT testing', $q->getTableName());
	}

	/**
	 * @group subquery
	 * @covers Query::getTableName
	 */
	function testGetTableNameSingleWordNoAlias() {
		$q = new Query('testing');
		$this->assertEquals('testing', $q->getTableName());
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
