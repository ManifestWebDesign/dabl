<?php

class TestModel extends Model {

	protected static $_primaryKeys = array(
		'id',
	);

	protected static $_primaryKey = 'id';

	protected static $_columnTypes = array(
		'id' => Model::COLUMN_TYPE_INTEGER,
		'true_false' => Model::COLUMN_TYPE_BOOLEAN,
		'created' => Model::COLUMN_TYPE_TIMESTAMP,
		'updated' => Model::COLUMN_TYPE_INTEGER_TIMESTAMP,
	);

	protected static $_columnNames = array(
		'id',
		'true_false',
		'created',
		'updated'
	);

	protected $id;

	protected $created;

	protected $true_false;

	protected $updated;

	function getId() {
		return $this->id;
	}

	function settrue_false($value) {
		$this->setColumnValue('true_false', $value, Model::COLUMN_TYPE_BOOLEAN);
	}

	function gettrue_false() {
		return $this->true_false;
	}

	function getTrueFalse() {
		return $this->true_false;
	}

	function setCreated($value) {
		$this->setColumnValue('created', $value, Model::COLUMN_TYPE_TIMESTAMP);
	}

	function getCreated() {
		return $this->created;
	}

	function setUpdated($value) {
		$this->setColumnValue('updated', $value, Model::COLUMN_TYPE_INTEGER_TIMESTAMP);
	}

	function getUpdated() {
		return $this->updated;
	}

	public function castInts() {
	}

	static function getColumnType($column_name) {
		return self::$_columnTypes[$column_name];
	}

	static function getConnection() {
		return DBManager::getConnection();
	}

	static function getColumnNames() {
		return self::$_columnNames;
	}

	static function hasColumn($column_name) {
		static $columns_cache = null;
		if (null === $columns_cache) {
			$columns_cache = array_map('strtolower', self::$_columnNames);
		}
		return in_array(strtolower(self::normalizeColumnName($column_name)), $columns_cache);
	}

	static function getPrimaryKeys() {
		return self::$_primaryKeys;
	}
}

class ModelTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var DABLPDO
	 */
	protected $pdo = null;

	/**
	 * @var TestModel
	 */
	protected $instance;

	protected function setUp() {
		$this->instance = new TestModel();
		return parent::setUp();
	}

	function testBooleanHandlesDifferentTypes() {
		$this->instance->setColumnValue('true_false', 'On');
		$this->assertSame(1, $this->instance->getTrueFalse());
		$this->instance->setColumnValue('true_false', 1);
		$this->assertSame(1, $this->instance->getTrueFalse());
		$this->instance->setColumnValue('true_false', 'ON');
		$this->assertSame(1, $this->instance->getTrueFalse());
		$this->instance->setColumnValue('true_false', true);
		$this->assertSame(1, $this->instance->getTrueFalse());

		$this->instance->setColumnValue('true_false', 'FALSE');
		$this->assertSame(0, $this->instance->getTrueFalse());
		$this->instance->setColumnValue('true_false', '0');
		$this->assertSame(0, $this->instance->getTrueFalse());
		$this->instance->setColumnValue('true_false', 0);
		$this->assertSame(0, $this->instance->getTrueFalse());
		$this->instance->setColumnValue('true_false', false);
		$this->assertSame(0, $this->instance->getTrueFalse());
	}

	/**
	 * @covers Model::normalizeColumnName
	 */
	function testNormalizeColumnName() {
		$this->assertSame('bar', Model::normalizeColumnName('foo.bar'));
		$this->assertSame('bar', Model::normalizeColumnName('foo.foo.bar'));
		$this->assertSame('bar', Model::normalizeColumnName('bar'));
	}

	/**
	 * @covers Model::__set
	 * @covers Model::__get
	 */
	function testMagicGettersAndSetters() {
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
	 * @covers Model::save
	 * @covers Model::insert
	 */
	function testSave() {
//		foreach ($this->getModelClasses() as $model_name) {
//			$instance = new $model_name();
//			$this->pdo = $instance->getConnection();
//			$this->pdo->beginTransaction();
//
//			foreach ($instance->getColumnNames() as $column) {
//				$this->setValueByType($instance, $column);
//			}
//
//			try {
//				$result = $instance->save();
//				$this->assertSame(1, $result);
//				$this->pdo->rollback();
//			} catch (Exception $e) {
//				$this->pdo->rollback();
//				$this->fail($e);
//			}
//		}
	}

	/**
	 * @covers Model::save
	 * @covers Model::update
	 */
	function testUpdate() {
//		foreach ($this->getModelClasses() as $model_name) {
//			$instance = new $model_name();
//			$this->pdo = $instance->getConnection();
//			$this->pdo->beginTransaction();
//
//			try {
//				foreach ($instance->getColumnNames() as $column) {
//					$this->setValueByType($instance, $column);
//				}
//
//				// initial save should alter 1 row
//				$result = $instance->save();
//				$this->assertSame(1, $result);
//
//				// save again with no changes should alter 0 rows
//				$result = $instance->save();
//				$this->assertSame(0, $result);
//
//				foreach ($instance->getColumnNames() as $column) {
//					$this->setValueByType($instance, $column, true);
//				}
//
//				// save again with changes should alter 1 row
//				$result = $instance->save();
//				$this->assertSame(1, $result);
//				$this->pdo->rollback();
//			} catch (Exception $e) {
//				$this->pdo->rollback();
//				$this->fail($e);
//			}
//		}
	}

	/**
	 * @covers Model::delete
	 */
	function testDelete() {
//		foreach ($this->getModelClasses() as $model_name) {
//			$instance = new $model_name();
//			$this->pdo = $instance->getConnection();
//			$this->pdo->beginTransaction();
//
//			try {
//				foreach ($instance->getColumnNames() as $column) {
//					$this->setValueByType($instance, $column);
//				}
//
//				// initial save should alter 1 row
//				$result = $instance->save();
//				$this->assertSame(1, $result);
//
//				$result = $instance->delete();
//				$this->assertSame(1, $result);
//
//				$this->pdo->rollback();
//			} catch (Exception $e) {
//				$this->pdo->rollback();
//				$this->fail($e);
//			}
//		}
	}

	/**
	 * @covers Model::isNew
	 */
	function testIsNew() {
		$this->assertTrue($this->instance->isNew());
		$this->instance->setColumnValue('id', 1);
		$this->assertTrue($this->instance->isNew());
	}

	/**
	 * @covers Model::copy
	 */
	function testCopy() {
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
	function testIsModified() {
		$this->assertFalse($this->instance->isModified());
		$this->instance->setColumnValue('created', time());
		$this->assertTrue($this->instance->isModified());
	}

	/**
	 * @covers Model::isColumnModified
	 */
	function testIsColumnModified() {
		$this->assertFalse($this->instance->isColumnModified('created'));
		$this->instance->setColumnValue('created', time());
		$this->assertTrue($this->instance->isColumnModified('created'));
	}

	/**
	 * @covers Model::getModifiedColumns
	 */
	function testGetModifiedColumns() {
		$this->assertSame(array(), $this->instance->getModifiedColumns());
		$this->instance->setColumnValue('created', time());
		$this->assertSame(array('created'), $this->instance->getModifiedColumns());
	}

	/**
	 * @covers Model::resetModified
	 */
	function testResetModified() {
		$this->assertFalse($this->instance->isModified());
		$this->instance->setColumnValue('created', time());
		$this->assertTrue($this->instance->isModified());
		$this->instance->resetModified();
		$this->assertFalse($this->instance->isModified());
	}
}