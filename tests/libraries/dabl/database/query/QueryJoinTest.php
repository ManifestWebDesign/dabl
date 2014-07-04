<?php

class QueryJoinTest extends PHPUnit_Framework_TestCase {

	function testIsQualifiedColumn() {
		$join = new QueryJoin('');

		$this->assertFalse($join->isQualifiedColumn(new Condition));
		$this->assertFalse($join->isQualifiedColumn(new Query));
		$this->assertFalse($join->isQualifiedColumn('foo = bar'));
		$this->assertFalse($join->isQualifiedColumn('1=1'));
		$this->assertFalse($join->isQualifiedColumn('table AS alias'));

		$this->assertTrue($join->isQualifiedColumn('foo.bar'));
		$this->assertTrue($join->isQualifiedColumn('db.foo.bar'));
	}

	function testNormalJoin() {
		$join = QueryJoin::create('database.table', 'othertable.column = database.table.column');
		$this->assertEquals(
			(string) $join->getQueryStatement(DBManager::getConnection()),
			'JOIN `database`.`table` ON (othertable.column = database.table.column)'
		);
	}

	function testPropelJoin() {
		$join = QueryJoin::create('foo.bar_id', 'foo2.bar_id');
		$this->assertEquals(
			(string) $join->getQueryStatement(DBManager::getConnection()),
			'JOIN `foo2` ON (`foo`.`bar_id` = `foo2`.`bar_id`)'
		);
	}

	function testPropelJoinWithAlias() {
		$join = QueryJoin::create('foo.bar_id', 'foo2.bar_id')
			->setAlias('f');
		$this->assertEquals(
			(string) $join->getQueryStatement(DBManager::getConnection()),
			'JOIN `foo2` AS f ON (`foo`.`bar_id` = `foo2`.`bar_id`)'
		);
	}

	function testPropelJoinWithDatabasePrefix() {
		$join = QueryJoin::create('db.foo.bar_id', 'foo2.bar_id')
			->setAlias('f');
		$this->assertEquals(
			(string) $join->getQueryStatement(DBManager::getConnection()),
			'JOIN `foo2` AS f ON (`db`.`foo`.`bar_id` = `foo2`.`bar_id`)'
		);
	}

}