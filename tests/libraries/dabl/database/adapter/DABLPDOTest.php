<?php

class DABLPDOTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var DBSQLite
	 */
	protected $pdoSQLite;

	function setUp() {
		$this->pdoSQLite = DABLPDO::factory(array(
			'driver' => 'sqlite',
			'dbname' => ':memory:'
		));
		return parent::setUp();
	}

	function testQuoteIdentifierCowardlySkip() {
		$string = 'foo bar';
		$expected = 'foo bar';
		$actual = $this->pdoSQLite->quoteIdentifier($string);
		$this->assertEquals($expected, $actual);
	}

	function testQuoteIdentifierForce() {
		$string = 'foo bar';
		$expected = '[foo bar]';
		$actual = $this->pdoSQLite->quoteIdentifier($string, true);
		$this->assertEquals($expected, $actual);
	}

	function testQuoteIdentifierArray() {
		$values = array('foo_bar');
		$expected = array('[foo_bar]');
		$actual = $this->pdoSQLite->quoteIdentifier($values);
		$this->assertEquals($expected, $actual);
	}
}
