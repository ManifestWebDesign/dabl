<?php

class ConditionTest extends PHPUnit_Framework_TestCase {

	/**
	 * @group count
	 * @covers Query::getQuery
	 */
	function testCondition() {
		$c = new Condition();
		$c->add('fun', 'good');
		$c->addOr('foo', 'bar');
		$c->add('awesome', array('good', 'awesome', 'nice'));
		$c->addOr('do', 'stuff');

		$c2 = new Condition('bah', 'blacksheep');
		$c2->addOr('empty', null);
		$c->addOr($c2);

		// shouldn't do anything
		$c->add(null);

		$stmnt = $c->getQueryStatement();
		$stmnt->setConnection(DBManager::getConnection());

		$q = "
	`fun` = 'good'
	OR `foo` = 'bar'
	AND `awesome` IN ('good','awesome','nice')
	OR `do` = 'stuff'
	OR (
	`bah` = 'blacksheep'
	OR `empty` IS NULL )";
		$this->assertEquals($q, $stmnt->__toString());
	}

	function testBeginsWith() {
		$c = new Condition;
		$c->add('my_column', 'value', Query::BEGINS_WITH);
		$stmnt = $c->getQueryStatement();
		$stmnt->setConnection(DBManager::getConnection());

		$q = "
	`my_column` LIKE 'value%'";
		$this->assertEquals($q, $stmnt->__toString());
	}

	function testEndsWith() {
		$c = new Condition;
		$c->add('my_column', 'value', Query::ENDS_WITH);
		$stmnt = $c->getQueryStatement();
		$stmnt->setConnection(DBManager::getConnection());

		$q = "
	`my_column` LIKE '%value'";
		$this->assertEquals($q, $stmnt->__toString());
	}

	function testContains() {
		$c = new Condition;
		$c->add('my_column', 'value', Query::CONTAINS);
		$stmnt = $c->getQueryStatement();
		$stmnt->setConnection(DBManager::getConnection());

		$q = "
	`my_column` LIKE '%value%'";
		$this->assertEquals($q, $stmnt->__toString());
	}

	function testEmptyCondition() {
		$c = new Condition();
		$c2 = new Condition();

		$c->add($c2);
		$c->add('my_column', 'value');

		$stmnt = $c->getQueryStatement();
		$stmnt->setConnection(DBManager::getConnection());

		$q = "
	`my_column` = 'value'";

		$this->assertEquals($q, $stmnt->__toString());

	}

	function testValidateOperator() {
		$c = new Condition();
		$c->add('foo', 'bar', 'SQL injection!');

		$this->setExpectedException('InvalidArgumentException');

		$stmnt = $c->getQueryStatement();
		$stmnt->setConnection(DBManager::getConnection());
	}

}