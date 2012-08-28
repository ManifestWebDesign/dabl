<?php

class DBManagerTest extends PHPUnit_Framework_TestCase {

	/**
	 * @covers DBManager::getConnections
	 */
	public function testGetConnections() {
		$connections = DBManager::getConnections();
		$this->assertInternalType('array', $connections);
		foreach ($connections as $connection) {
			$this->assertInstanceOf('DABLPDO', $connection);
		}
	}

	/**
	 * @covers DBManager::getConnectionNames
	 */
	public function testGetConnectionNames() {
		$connection_names = DBManager::getConnectionNames();
		$this->assertInternalType('array', $connection_names);
		foreach ($connection_names as $connection_name) {
			$this->assertInternalType('string', $connection_name);
		}
	}

	/**
	 * @covers DBManager::getConnection
	 */
	public function testGetConnectionNoArgument() {
		$connection = DBManager::getConnection();

		$this->assertInstanceOf('DABLPDO', $connection);

		// verify that $connection is the first connection
		$this->assertEquals(array_shift(DBManager::getConnections()), $connection);
	}

	/**
	 * @covers DBManager::addConnection
	 * @todo Implement testAddConnection().
	 */
	public function testAddConnection() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers DBManager::getParameter
	 * @todo Implement testGetParameter().
	 */
	public function testGetParameter() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers DBManager::disconnect
	 * @todo Implement testDisconnect().
	 */
	public function testDisconnect() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}