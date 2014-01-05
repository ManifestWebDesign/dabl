<?php

class DBMySQLTest extends PHPUnit_Framework_TestCase {

	function setUp() {
		$this->pdo = DBManager::getConnection();
		if (!($this->pdo instanceof DBMySQL)) {
			$this->markTestSkipped('Primary connection is not an instance of DBMySQL');
		}
		return parent::setUp();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::beginTransaction
	 */
	function testBeginTransaction() {
		$this->pdo->beginTransaction();
		$this->assertEquals(1, $this->pdo->getTransactionDepth());
		$this->pdo->rollback();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::rollback
	 * @expectedException PDOException
	 */
	function testRollbackOutsideTransaction() {
		$this->pdo->rollback();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::commit
	 * @expectedException PDOException
	 */
	function testCommitOutsideTransaction() {
		$this->pdo->commit();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::rollback
	 */
	function testNestedRollback() {
		$this->pdo->beginTransaction();
		$this->pdo->beginTransaction();
		$this->pdo->rollback();
		$this->pdo->rollback();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::commit
	 */
	function testNestedCommit() {
		$this->pdo->beginTransaction();
		$this->pdo->beginTransaction();
		$this->pdo->commit();
		$this->assertEquals(1, $this->pdo->getTransactionDepth());
		$this->pdo->commit();
	}

	/**
	 * @group NestedTransaction
	 * @group bug1355
	 * @covers DBMySQL::commit
	 */
	function testRollbackBeforeCommit() {
		$this->pdo->beginTransaction();
		$this->pdo->beginTransaction();
		$this->pdo->rollback();
		$this->pdo->commit();
	}
}
