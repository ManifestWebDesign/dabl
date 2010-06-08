<?php

require_once 'config.php';

require_once 'PHPUnit/Framework.php';

class TestDBMySQL extends PHPUnit_Framework_TestCase {

	const CONNECTION_NAME = 'my_connection_name';

	function testBeginTransaction() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$this->assertEquals(1, $pdo->getTransactionCount());
		$this->assertFalse($pdo->getRollbackImminent());
		$pdo->rollback();
	}

	/**
	 * @expectedException Exception
	 */
	function testRollbackOutsideTransaction() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->rollback();
	}

	/**
	 * @expectedException Exception
	 */
	function testCommitOutsideTransaction() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->commit();
	}

	function testNestedRollback() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$pdo->beginTransaction();
		$pdo->rollback();
		$this->assertTrue($pdo->getRollbackImminent());
		$pdo->rollback();
	}

	function testNestedCommit() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$pdo->beginTransaction();
		$pdo->commit();
		$this->assertEquals(1, $pdo->getTransactionCount());
		$pdo->commit();
	}

	function testCommitBeforeRollback() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$pdo->beginTransaction();
		$pdo->commit();
		$pdo->rollback();
		$this->assertTrue($pdo->getRollbackImminent());
	}

	/**
	 * @expectedException Exception
	 */
	function testRollbackBeforeCommit() {
		$pdo = DBManager::getConnection(self::CONNECTION_NAME);
		$pdo->beginTransaction();
		$pdo->beginTransaction();
		$pdo->rollback();
		$pdo->commit();
	}
}
