<?php

class QueryJoinTest extends PHPUnit_Framework_TestCase {

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

}