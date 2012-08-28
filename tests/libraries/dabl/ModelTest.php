<?php

class ModelTest extends PHPUnit_Framework_TestCase {

	/**
	 * Return all known models that have a single primary key
	 * @return array
	 */
	private function getModelClasses() {
		$classes = array();
		$model_files = glob(MODELS_DIR . '*.php');
		foreach ($model_files as $model_file) {
			$model_name = str_replace('.php', '', basename($model_file));
			if ($model_name == 'ApplicationModel') {
				continue;
			}
			$instance = new $model_name;
			if (count($instance->getPrimaryKeys()) !== 1) {
				continue;
			}
			$classes[] = $model_name;
		}
		return $classes;
	}

	private function setValueByType($instance, $column, $variant = false) {
		if (
			$column == $instance->getPrimaryKey()
			|| in_array(strtolower($column), array('created', 'updated'))
		) {
			return false;
		}

		$column_type = $instance->getColumnType($column);
		if (Model::COLUMN_TYPE_BOOLEAN === $column_type) {
			$instance->$column = !$variant;
		} elseif ($instance->isNumericType($column_type)) {
			if ($variant) {
				$instance->$column = 6;
			} else {
				$instance->$column = '5';
			}
		} elseif ($instance->isTemporalType($column_type)) {
			if ($variant) {
				$instance->$column = time() + 10;
			} else {
				$instance->$column = time();
			}
		} else {
			if ($variant) {
				$instance->$column = 'another test value';
			} else {
				$instance->$column = 'test value';
			}
		}
		return true;
	}

	/**
	 * @covers Model::__set
	 * @covers Model::__get
	 */
	function testMagicGettersAndSetters() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();
			foreach ($instance->getColumnNames() as $column) {
				if ($column == $instance->getPrimaryKey()) {
					continue;
				}
				$column_type = $instance->getColumnType($column);
				if (Model::COLUMN_TYPE_BOOLEAN === $column_type) {
					$instance->$column = true;
					$this->assertEquals(1, $instance->$column);
					$instance->$column = false;
					$this->assertEquals(0, $instance->$column);
					$instance->$column = 'true';
					$this->assertEquals(1, $instance->$column);
					$instance->$column = 'false';
					$this->assertEquals(0, $instance->$column);
					$instance->$column = 'on';
					$this->assertEquals(1, $instance->$column);
					$instance->$column = 'off';
					$this->assertEquals(0, $instance->$column);
				} elseif ($instance->isNumericType($column_type)) {
					$instance->$column = '5';
					$this->assertEquals($instance->$column, 5);
				} elseif ($instance->isTemporalType($column_type)) {
					$instance->$column = time();
					switch ($column_type) {
						case Model::COLUMN_TYPE_TIME:
							$value = date($instance->getConnection()->getTimeFormatter());
							break;
						case Model::COLUMN_TYPE_TIMESTAMP:
							$value = date($instance->getConnection()->getTimestampFormatter());
							break;
						default:
							$value = date($instance->getConnection()->getDateFormatter());
							break;
					}
					$this->assertEquals($instance->$column, $value);
				} else {
					$instance->$column = 'test-value';
					$this->assertEquals($instance->$column, 'test-value');
				}
			}
		}
	}

	/**
	 * @covers Model::save
	 * @covers Model::insert
	 */
	function testSave() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();
			$con = $instance->getConnection();
			$con->beginTransaction();

			foreach ($instance->getColumnNames() as $column) {
				$this->setValueByType($instance, $column);
			}

			$result = $instance->save();
			$this->assertEquals(1, $result);
			$con->rollback();
		}
	}

	/**
	 * @covers Model::save
	 * @covers Model::update
	 */
	function testUpdate() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();
			$con = $instance->getConnection();
			$con->beginTransaction();

			foreach ($instance->getColumnNames() as $column) {
				$this->setValueByType($instance, $column);
			}

			// initial save should alter 1 row
			$result = $instance->save();
			$this->assertEquals(1, $result);

			// save again with no changes should alter 0 rows
			$result = $instance->save();
			$this->assertEquals(0, $result);

			foreach ($instance->getColumnNames() as $column) {
				$this->setValueByType($instance, $column, true);
			}

			// save again with changes should alter 1 row
			$result = $instance->save();
			$this->assertEquals(1, $result);

			$con->rollback();
		}
	}

	/**
	 * @covers Model::delete
	 */
	function testDelete() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();
			$con = $instance->getConnection();
			$con->beginTransaction();

			foreach ($instance->getColumnNames() as $column) {
				$this->setValueByType($instance, $column);
			}

			// initial save should alter 1 row
			$result = $instance->save();
			$this->assertEquals(1, $result);

			$result = $instance->delete();
			$this->assertEquals(1, $result);

			$con->rollback();
		}
	}

	/**
	 * @covers Model::isModified
	 */
	function testIsModified() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();

			foreach ($instance->getColumnNames() as $column) {
				if ($this->setValueByType($instance, $column)) {
					break;
				}
			}
			$this->assertTrue($instance->isModified());
		}
	}

	/**
	 * @covers Model::isColumnModified
	 */
	function testIsColumnModified() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();

			foreach ($instance->getColumnNames() as $column) {
				if ($this->setValueByType($instance, $column)) {
					$this->assertTrue($instance->isColumnModified($column));
					break;
				}
			}
		}
	}

	/**
	 * @covers Model::isNew
	 */
	function testIsNew() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();
			$con = $instance->getConnection();
			$con->beginTransaction();

			foreach ($instance->getColumnNames() as $column) {
				$this->setValueByType($instance, $column);
			}

			$this->assertTrue($instance->isNew());

			$instance->save();

			$this->assertFalse($instance->isNew());

			$con->rollback();
		}
	}

	/**
	 * @covers Model::getModifiedColumns
	 */
	function testGetModifiedColumns() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();

			$modified_columns = array();
			foreach ($instance->getColumnNames() as $column) {
				if ($this->setValueByType($instance, $column)) {
					$modified_columns[] = $column;
				}
			}
			$this->assertEquals($modified_columns, $instance->getModifiedColumns());
		}
	}

	/**
	 * @covers Model::resetModified
	 */
	function testResetModified() {
		foreach ($this->getModelClasses() as $model_name) {
			$instance = new $model_name();

			$modified = false;
			foreach ($instance->getColumnNames() as $column) {
				if ($this->setValueByType($instance, $column)) {
					$modified = true;
				}
			}
			$this->assertTrue($instance->isModified());
			$instance->resetModified();
			$this->assertFalse($instance->isModified());
		}
	}
}