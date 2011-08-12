<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../../../../../config.php';

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

}