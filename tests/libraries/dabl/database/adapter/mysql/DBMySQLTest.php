<?php

class DBMySQLTest extends PHPUnit_Framework_TestCase {

	const CONNECTION_NAME = 'default_connection';

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::beginTransaction
	 */
	function testBeginTransaction() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$this->assertEquals(1, $pdo->getTransactionCount());
		$this->assertFalse($pdo->getRollbackImminent());
		$pdo->rollback();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::rollback
	 * @expectedException ErrorException
	 */
	function testRollbackOutsideTransaction() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->rollback();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::commit
	 * @expectedException ErrorException
	 */
	function testCommitOutsideTransaction() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->commit();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::rollback
	 */
	function testNestedRollback() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$pdo->beginTransaction();
		$pdo->rollback();
		$this->assertTrue($pdo->getRollbackImminent());
		$pdo->rollback();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::commit
	 */
	function testNestedCommit() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$pdo->beginTransaction();
		$pdo->commit();
		$this->assertEquals(1, $pdo->getTransactionCount());
		$pdo->commit();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::rollback
	 */
	function testCommitBeforeRollback() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$pdo->beginTransaction();
		$pdo->commit();
		$pdo->rollback();
		$this->assertTrue($pdo->getRollbackImminent());
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::commit
	 * @expectedException ErrorException
	 */
	function testRollbackBeforeCommit() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$pdo->beginTransaction();
		$pdo->rollback();
		$pdo->commit();
	}
}
