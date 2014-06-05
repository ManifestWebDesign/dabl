<?php

class TestModel extends Model {

	public static $_tableName = 'test_model';

	public static $_instancePool = array();

	public static $_instancePoolCount = 0;

	public static $_poolEnabled = true;

	public static $_insertBatch = array();

	public static $_insertBatchSize = 500;

	public static $_isAutoIncrement = true;

	protected static $_primaryKeys = array(
		'id',
	);

	protected static $_primaryKey = 'id';

	public static $_columns = array(
		'test_model.id',
		'test_model.true_false',
		'test_model.created',
		'test_model.updated'
	);

	public static $_columnNames = array(
		'id',
		'true_false',
		'created',
		'updated'
	);

	public static $_columnTypes = array(
		'id' => Model::COLUMN_TYPE_INTEGER,
		'true_false' => Model::COLUMN_TYPE_BOOLEAN,
		'created' => Model::COLUMN_TYPE_TIMESTAMP,
		'updated' => Model::COLUMN_TYPE_INTEGER_TIMESTAMP,
	);

	static function getConnection() {
		return DBManager::getConnection();
	}

	protected $id;

	protected $created;

	protected $true_false;

	protected $updated;

	public function setId($value) {
		return $this->setColumnValue('id', $value);
	}

	public function getId() {
		return $this->id;
	}

	public function settrue_false($value) {
		return $this->setColumnValue('true_false', $value, Model::COLUMN_TYPE_BOOLEAN);
	}

	public function gettrue_false() {
		return $this->true_false;
	}

	public function getTrueFalse() {
		return $this->true_false;
	}

	public function setCreated($value) {
		return $this->setColumnValue('created', $value, Model::COLUMN_TYPE_TIMESTAMP);
	}

	public function getCreated() {
		return $this->created;
	}

	public function setUpdated($value) {
		return $this->setColumnValue('updated', $value, Model::COLUMN_TYPE_INTEGER_TIMESTAMP);
	}

	public function getUpdated() {
		return $this->updated;
	}

}

class TestModel2 extends Model {

	protected $nullInteger;

	protected $emptyInteger = '';

	protected $integer = '6897';

	protected $boolean = false;

	protected $integerTimestamp = '3456789999';

	public static $_columnNames = array(
		'nullInteger',
		'emptyInteger',
		'integer',
		'boolean',
		'integerTimestamp',
	);

	public static $_columnTypes = array(
		'nullInteger' => Model::COLUMN_TYPE_INTEGER,
		'emptyInteger' => Model::COLUMN_TYPE_INTEGER,
		'integer' => Model::COLUMN_TYPE_INTEGER,
		'boolean' => Model::COLUMN_TYPE_BOOLEAN,
		'integerTimestamp' => Model::COLUMN_TYPE_INTEGER_TIMESTAMP
	);

	function getNullInteger() {
		return $this->nullInteger;
	}

	function getEmptyInteger() {
		return $this->emptyInteger;
	}

	function getInteger() {
		return $this->integer;
	}

	function getBoolean() {
		return $this->boolean;
	}

	function getIntegerTimestamp() {
		return $this->integerTimestamp;
	}
}

class ModelTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var TestModel
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->instance = new TestModel;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		TestModel::flushPool();
	}

	/**
	 * @covers Model::__toString
	 */
	public function test__toString() {
		$this->assertEquals('TestModel', $this->instance . '');

		$this->instance->setId('1');
		$this->assertEquals('TestModel1', $this->instance . '');
	}

	/**
	 * @covers Model::__set
	 * @covers Model::__get
	 */
	public function testMagicGettersAndSetters() {
		$this->assertNull($this->instance->true_false);

		$this->instance->true_false = '0';
		$this->assertSame(0, $this->instance->true_false);

		$this->instance->true_false = '1';
		$this->assertSame(1, $this->instance->true_false);

		$this->instance->true_false = 'false';
		$this->assertSame(0, $this->instance->true_false);

		$this->instance->true_false = 'true';
		$this->assertSame(1, $this->instance->true_false);

		$this->instance->true_false = 'off';
		$this->assertSame(0, $this->instance->true_false);

		$this->instance->true_false = 'on';
		$this->assertSame(1, $this->instance->true_false);

		$now = time();
		$now_string = date(TestModel::getConnection()->getTimestampFormatter(), $now);
		$this->instance->created = $now;
		$this->assertSame($now_string, $this->instance->created);

		$this->instance->updated = $now;
		$this->assertSame($now_string, $this->instance->created);

		// Todo: numeric, string, time, timestamp
	}

	/**
	 * @covers Model::create
	 */
	public function testCreate() {
		$instance = TestModel::create();
		$this->assertInstanceOf('TestModel', $instance);
	}

	/**
	 * @covers Model::isTemporalType
	 */
	public function testIsTemporalType() {
		$types = array(
			Model::COLUMN_TYPE_DATE,
			Model::COLUMN_TYPE_TIME,
			Model::COLUMN_TYPE_TIMESTAMP,
			Model::COLUMN_TYPE_BU_DATE,
			Model::COLUMN_TYPE_BU_TIMESTAMP,
			Model::COLUMN_TYPE_INTEGER_TIMESTAMP
		);

		foreach ($types as $type) {
			$this->assertTrue(TestModel::isTemporalType($type));
		}
	}

	/**
	 * @covers Model::isTextType
	 */
	public function testIsTextType() {
		$types = array(
			Model::COLUMN_TYPE_CHAR,
			Model::COLUMN_TYPE_VARCHAR,
			Model::COLUMN_TYPE_LONGVARCHAR,
			Model::COLUMN_TYPE_CLOB,
			Model::COLUMN_TYPE_DATE,
			Model::COLUMN_TYPE_TIME,
			Model::COLUMN_TYPE_TIMESTAMP,
			Model::COLUMN_TYPE_BU_DATE,
			Model::COLUMN_TYPE_BU_TIMESTAMP
		);

		foreach ($types as $type) {
			$this->assertTrue(TestModel::isTextType($type));
		}
	}

	/**
	 * @covers Model::isNumericType
	 */
	public function testIsNumericType() {
		$types = array(
			Model::COLUMN_TYPE_SMALLINT,
			Model::COLUMN_TYPE_TINYINT,
			Model::COLUMN_TYPE_INTEGER,
			Model::COLUMN_TYPE_BIGINT,
			Model::COLUMN_TYPE_FLOAT,
			Model::COLUMN_TYPE_DOUBLE,
			Model::COLUMN_TYPE_NUMERIC,
			Model::COLUMN_TYPE_DECIMAL,
			Model::COLUMN_TYPE_REAL,
			Model::COLUMN_TYPE_INTEGER_TIMESTAMP
		);

		foreach ($types as $type) {
			$this->assertTrue(TestModel::isNumericType($type));
		}
	}

	/**
	 * @covers Model::isIntegerType
	 */
	public function testIsIntegerType() {
		$types = array(
			Model::COLUMN_TYPE_SMALLINT,
			Model::COLUMN_TYPE_TINYINT,
			Model::COLUMN_TYPE_INTEGER,
			Model::COLUMN_TYPE_BIGINT,
			Model::COLUMN_TYPE_BOOLEAN,
			Model::COLUMN_TYPE_INTEGER_TIMESTAMP
		);

		foreach ($types as $type) {
			$this->assertTrue(TestModel::isIntegerType($type));
		}
	}

	/**
	 * @covers Model::isLobType
	 */
	public function testIsLobType() {
		$types = array(
			Model::COLUMN_TYPE_VARBINARY,
			Model::COLUMN_TYPE_LONGVARBINARY,
			Model::COLUMN_TYPE_BLOB
		);

		foreach ($types as $type) {
			$this->assertTrue(TestModel::isLobType($type));
		}
	}

	/**
	 * @covers Model::getTableName
	 */
	public function testGetTableName() {
		$this->assertEquals('test_model', TestModel::getTableName());
	}

	/**
	 * @covers Model::getColumnNames
	 */
	public function testGetColumnNames() {
		$this->assertEquals(TestModel::$_columnNames, TestModel::getColumnNames());
	}

	/**
	 * @covers Model::getColumns
	 */
	public function testGetColumns() {
		$this->assertEquals(TestModel::$_columns, TestModel::getColumns());
	}

	/**
	 * @covers Model::getColumnTypes
	 */
	public function testGetColumnTypes() {
		$this->assertEquals(TestModel::$_columnTypes, TestModel::getColumnTypes());
	}

	/**
	 * @covers Model::getColumnType
	 */
	public function testGetColumnType() {
		$this->assertEquals(Model::COLUMN_TYPE_INTEGER, TestModel::getColumnType('id'));
	}

	/**
	 * @covers Model::hasColumn
	 */
	public function testHasColumn() {
		$this->assertTrue(TestModel::hasColumn('id'));
		$this->assertTrue(TestModel::hasColumn('test_model.Id'));
		$this->assertFalse(TestModel::hasColumn('foo'));

		$this->assertFalse(TestModel2::hasColumn('id'));
		$this->assertTrue(TestModel2::hasColumn('Integer'));
	}

	/**
	 * @covers Model::normalizeColumnName
	 */
	public function testNormalizeColumnName() {
		$this->assertSame('bar', TestModel::normalizeColumnName('foo.bar'));
		$this->assertSame('bar', TestModel::normalizeColumnName('foo.foo.bar'));
		$this->assertSame('bar', TestModel::normalizeColumnName('bar'));
	}

	/**
	 * @covers Model::getPrimaryKey
	 */
	public function testGetPrimaryKey() {
		$this->assertSame('id', TestModel::getPrimaryKey());
	}

	/**
	 * @covers Model::getPrimaryKeys
	 */
	public function testGetPrimaryKeys() {
		$this->assertSame(array('id'), TestModel::getPrimaryKeys());
	}

	/**
	 * @covers Model::isAutoIncrement
	 */
	public function testIsAutoIncrement() {
		$this->assertTrue(TestModel::isAutoIncrement());
	}

	/**
	 * @covers Model::coerceTemporalValue
	 */
	public function testCoerceTemporalValue() {
		$this->assertSame('2014-05-25', TestModel::coerceTemporalValue('5/25/2014', Model::COLUMN_TYPE_DATE));
		$this->assertSame('2014-05-25 00:00:00', TestModel::coerceTemporalValue('5/25/2014', Model::COLUMN_TYPE_TIMESTAMP));
	}

	/**
	 * @covers Model::retrieveByColumn
	 * @todo   Implement testRetrieveByColumn().
	 */
	public function testRetrieveByColumn() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::fetchSingle
	 * @todo   Implement testFetchSingle().
	 */
	public function testFetchSingle() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::fetch
	 * @todo   Implement testFetch().
	 */
	public function testFetch() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::getQuery
	 */
	public function testGetQuery() {
		$q = TestModel::getQuery(array(
			'foo' => 'bar',
			'id' => 1,
			'limit' => 10
		));

		$con = TestModel::getConnection();

		$this->assertSame('test_model', $q->getTable());

		foreach ($q->getWhere()->getAnds() as $index => $and) {
			if ($index === 0) {
				$this->assertSame($con->quoteIdentifier('id') . ' = 1', $and . '');
			} else {
				$this->fail('There should only be 1 where condition.');
			}
		}

		$this->assertSame(10, $q->getLimit());
	}

	/**
	 * @covers Model::insertIntoPool
	 * @covers Model::retrieveFromPool
	 * @covers Model::removeFromPool
	 * @covers Model::flushPool
	 */
	public function testInstancePool() {
		TestModel::insertIntoPool($this->instance);

		$this->assertNull(TestModel::retrieveFromPool($this->instance->getId()));
		$this->assertEquals(0, TestModel::$_instancePoolCount);

		$this->instance->setId(1);
		TestModel::insertIntoPool($this->instance);

		$this->assertSame($this->instance, TestModel::retrieveFromPool($this->instance->getId()));

		TestModel::removeFromPool($this->instance);

		$this->assertEquals(0, TestModel::$_instancePoolCount);

		TestModel::insertIntoPool($this->instance);
		TestModel::insertIntoPool($this->instance);
		$this->assertEquals(1, TestModel::$_instancePoolCount);

		TestModel::flushPool();
		$this->assertNull(TestModel::retrieveFromPool($this->instance->getId()));

		$this->assertEquals(0, TestModel::$_instancePoolCount);
	}

	/**
	 * @covers Model::setPoolEnabled
	 * @covers Model::getPoolEnabled
	 */
	public function testGetSetPoolEnabled() {
		$this->assertTrue(TestModel::getPoolEnabled());
		TestModel::setPoolEnabled(false);

		$this->assertFalse(TestModel::getPoolEnabled());

		TestModel::setPoolEnabled(true);
		$this->assertTrue(TestModel::getPoolEnabled());
	}

	/**
	 * @covers Model::getAll
	 * @todo   Implement testGetAll().
	 */
	public function testGetAll() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::doCount
	 * @todo   Implement testDoCount().
	 */
	public function testDoCount() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::doDelete
	 * @todo   Implement testDoDelete().
	 */
	public function testDoDelete() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::doSelect
	 * @todo   Implement testDoSelect().
	 */
	public function testDoSelect() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::doSelectOne
	 * @todo   Implement testDoSelectOne().
	 */
	public function testDoSelectOne() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::doUpdate
	 * @todo   Implement testDoUpdate().
	 */
	public function testDoUpdate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::setInsertBatchSize
	 */
	public function testSetInsertBatchSize() {
		TestModel::setInsertBatchSize(111);
		$this->assertEquals(111, TestModel::$_insertBatchSize);
	}

	/**
	 * @covers Model::queueForInsert
	 */
	public function testQueueForInsert() {
		$this->instance->queueForInsert();

		$this->assertSame(array($this->instance), TestModel::$_insertBatch);
	}

	/**
	 * @covers Model::insertBatch
	 * @todo   Implement testInsertBatch().
	 */
	public function testInsertBatch() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::doSelectRS
	 * @todo   Implement testDoSelectRS().
	 */
	public function testDoSelectRS() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::fromResult
	 * @todo   Implement testFromResult().
	 */
	public function testFromResult() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::fromNumericResultArray
	 * @todo   Implement testFromNumericResultArray().
	 */
	public function testFromNumericResultArray() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::fromAssociativeResultArray
	 * @todo   Implement testFromAssociativeResultArray().
	 */
	public function testFromAssociativeResultArray() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::copy
	 */
	public function testCopy() {
		$this->instance->setColumnValue('id', 1);
		$this->instance->setColumnValue('created', time());

		$clone = $this->instance->copy();
		$this->assertFalse($clone->isColumnModified('id'));
		$this->assertTrue($clone->isColumnModified('created'));
		$this->assertNull($clone->getId());
	}

	/**
	 * @covers Model::isModified
	 */
	public function testIsModified() {
		$this->assertFalse($this->instance->isModified());
		$this->instance->setColumnValue('created', time());
		$this->assertTrue($this->instance->isModified());
	}

	/**
	 * @covers Model::isColumnModified
	 */
	public function testIsColumnModified() {
		$this->assertFalse($this->instance->isColumnModified('created'));
		$this->instance->setColumnValue('created', time());
		$this->assertTrue($this->instance->isColumnModified('created'));
	}

	/**
	 * @covers Model::getModifiedColumns
	 */
	public function testGetModifiedColumns() {
		$this->assertSame(array(), $this->instance->getModifiedColumns());
		$this->instance->setColumnValue('created', time());
		$this->assertSame(array('created'), $this->instance->getModifiedColumns());
	}

	/**
	 * @covers Model::setColumnValue
	 */
	public function testSetColumnValue() {
		$now = time();
		$this->instance->setColumnValue('created', $now);

		$this->assertSame(date('Y-m-d H:i:s', $now), $this->instance->getCreated());
	}

	/**
	 * @covers Model::resetModified
	 */
	public function testResetModified() {
		$this->assertFalse($this->instance->isModified());
		$this->instance->setColumnValue('created', time());
		$this->assertTrue($this->instance->isModified());
		$this->instance->resetModified();
		$this->assertFalse($this->instance->isModified());
	}

	/**
	 * @covers Model::fromArray
	 * @covers Model::toArray
	 */
	public function testToAndFromArray() {
		$this->assertSame(array(
			'id' => null,
			'true_false' => null,
			'created' => null,
			'updated' => null
		), $this->instance->toArray());

		$now = time();

		$this->instance->fromArray(array(
			'id' => '1',
			'true_false' => 'true',
			'created' => time(),
			'updated' => time()
		));

		$this->assertSame(1, $this->instance->getId());
		$this->assertSame(1, $this->instance->getTrueFalse());
		$this->assertSame(date('Y-m-d H:i:s', $now), $this->instance->getCreated());
		$this->assertSame($now, $this->instance->getUpdated(null));

		$this->assertSame(array(
			'id' => 1,
			'true_false' => 1,
			'created' => date('Y-m-d H:i:s', $now),
			'updated' => $now
		), $this->instance->toArray());
	}

	/**
	 * @covers Model::jsonSerialize
	 */
	public function testJsonSerialize() {
		$now = time();

		$this->instance
			->settrue_false(false)
			->setCreated($now)
			->setUpdated($now);

		$this->assertSame(array(
			'id' => null,
			'true_false' => false,
			'created' => date('c', $now),
			'updated' => date('c', $now)
		), $this->instance->jsonSerialize());
	}

	/**
	 * @covers Model::setCacheResults
	 * @covers Model::getCacheResults
	 */
	public function testGetAndSetCacheResults() {
		$this->instance->setCacheResults(true);
		$this->assertSame(true, $this->instance->getCacheResults());

		$this->instance->setCacheResults(false);
		$this->assertSame(false, $this->instance->getCacheResults());
	}

	/**
	 * @covers Model::hasPrimaryKeyValues
	 */
	public function testHasPrimaryKeyValues() {
		$this->assertFalse($this->instance->hasPrimaryKeyValues());
		$this->instance->setId(1);
		$this->assertTrue($this->instance->hasPrimaryKeyValues());
	}

	/**
	 * @covers Model::getPrimaryKeyValues
	 */
	public function testGetPrimaryKeyValues() {
		$this->assertSame(array(0 => null), $this->instance->getPrimaryKeyValues());
		$this->instance->setId(1);
		$this->assertSame(array(1), $this->instance->getPrimaryKeyValues());
	}

	/**
	 * @covers Model::validate
	 * @todo   Implement testValidate().
	 */
	public function testValidate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::getValidationErrors
	 * @todo   Implement testGetValidationErrors().
	 */
	public function testGetValidationErrors() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::delete
	 * @todo   Implement testDelete().
	 */
	public function testDelete() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::save
	 * @todo   Implement testSave().
	 */
	public function testSave() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::archive
	 * @todo   Implement testArchive().
	 */
	public function testArchive() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Model::isNew
	 * @covers Model::setNew
	 */
	public function testNew() {
		$this->assertTrue($this->instance->isNew());
		$this->instance->setColumnValue('id', 1);
		$this->assertTrue($this->instance->isNew());
		$this->instance->setNew(false);
		$this->assertFalse($this->instance->isNew());
	}

	/**
	 * @covers Model::setDirty
	 * @covers Model::isDirty
	 */
	public function testDirty() {
		$this->assertEquals(false, $this->instance->isDirty());
		$this->instance->setDirty(true);
		$this->assertTrue($this->instance->isDirty());
	}

	/**
	 * @covers Model::castInts
	 */
	public function testCastInts() {
		$instance = new TestModel2();
		$instance->castInts();

		$this->assertSame(null, $instance->getNullInteger());
		$this->assertSame(null, $instance->getEmptyInteger());
		$this->assertSame(6897, $instance->getInteger());
		$this->assertSame(0, $instance->getBoolean());
		$this->assertSame(3456789999, $instance->getIntegerTimestamp());
	}

}
