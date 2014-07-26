<?php

class DBPostgresTest extends PHPUnit_Framework_TestCase {

	function setUp() {
		$this->pdo = DBManager::getConnection();
		if (!($this->pdo instanceof DBPostgres)) {
			$this->markTestSkipped('Primary connection is not an instance of DBPostgres');
		}
		return parent::setUp();
	}

	function testHourStart() {
		$sql = 'SELECT ' . $this->pdo->hourStart("'2014-05-05 10:05:15'");
		$expected = '2014-05-05 10:00:00';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		$this->assertNotNull($this->pdo->query('SELECT ' . $this->pdo->hourStart("now()"))->fetchColumn());

//		$sql = 'SELECT ' . $this->pdo->hourStart("'bad date'");
//		$actual = $this->pdo->query($sql)->fetchColumn();
//		$this->assertNull($actual, $sql . ' should have returned null');
	}

	function testDayStart() {
		$sql = 'SELECT ' . $this->pdo->dayStart("'2014-05-05 10:05:15'");
		$expected = '2014-05-05';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		$this->assertNotNull($this->pdo->query('SELECT ' . $this->pdo->dayStart("now()"))->fetchColumn());

//		$sql = 'SELECT ' . $this->pdo->dayStart("'bad date'");
//		$actual = $this->pdo->query($sql)->fetchColumn();
//		$this->assertNull($actual, $sql . ' should have returned null');
	}

	function testWeekStart() {
		$sql = 'SELECT ' . $this->pdo->weekStart("'2014-07-25 15:01:19'");
		$expected = '2014-07-20';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		$this->assertNotNull($this->pdo->query('SELECT ' . $this->pdo->weekStart("now()"))->fetchColumn());

//		$sql = 'SELECT ' . $this->pdo->weekStart("'bad date'");
//		$actual = $this->pdo->query($sql)->fetchColumn();
//		$this->assertNull($actual, $sql . ' should have returned null');
	}

	function testMonthStart() {
		$sql = 'SELECT ' . $this->pdo->monthStart("'2014-07-25 15:01:19'");
		$expected = '2014-07-01';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		$this->assertNotNull($this->pdo->query('SELECT ' . $this->pdo->monthStart("now()"))->fetchColumn());

//		$sql = 'SELECT ' . $this->pdo->monthStart("'bad date'");
//		$actual = $this->pdo->query($sql)->fetchColumn();
//		$this->assertNull($actual, $sql . ' should have returned null');
	}

	function testConvertTimeZone() {
		// Implicitly TIMESTAMP
		$this->pdo->exec("SET SESSION TIME ZONE 'UTC'");
		$sql = 'SELECT ' . $this->pdo->convertTimeZone("'2014-07-25 15:01:19'", 'America/Los_Angeles');
		$expected = '2014-07-25 08:01:19';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		// Explicitly TIMESTAMPTZ, no offset
		$sql = 'SELECT ' . $this->pdo->convertTimeZone("TIMESTAMPTZ '2014-07-25 15:01:19'", 'America/Los_Angeles');
		$expected = '2014-07-25 08:01:19';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		// Explicitly TIMESTAMP
		$sql = 'SELECT ' . $this->pdo->convertTimeZone("TIMESTAMP '2014-07-25 15:01:19'", 'America/Los_Angeles');
		$expected = '2014-07-25 08:01:19';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		// Explicitly TIMESTAMPTZ with offset
		$sql = 'SELECT ' . $this->pdo->convertTimeZone("TIMESTAMPTZ '2014-07-25 15:01:19-5'", 'America/Chicago');
		$expected = '2014-07-25 15:01:19';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		// Implicitly TIMESTAMPTZ with offset
		$sql = 'SELECT ' . $this->pdo->convertTimeZone("'2014-07-25 15:01:19-7'", 'America/Chicago');
		$expected = '2014-07-25 17:01:19';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		$this->pdo->exec("SET SESSION TIME ZONE 'America/Chicago'");
		$sql = 'SELECT ' . $this->pdo->convertTimeZone("'2014-07-25 15:01:19'", 'America/Los_Angeles');
		$expected = '2014-07-25 13:01:19';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);

		$sql = 'SELECT ' . $this->pdo->convertTimeZone("TIMESTAMPTZ '2014-07-25 15:01:19'", 'America/Chicago', 'America/Los_Angeles');
		$expected = '2014-07-25 17:01:19';
		$actual = $this->pdo->query($sql)->fetchColumn();
		$this->assertEquals($expected, $actual, $sql . ' should have returned ' . $expected);
	}


}
