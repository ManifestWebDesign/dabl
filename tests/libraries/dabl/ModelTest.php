<?php

class ModelTest extends PHPUnit_Framework_TestCase {

	function testMagicGettersAndSetters() {
		$model_files = glob(MODELS_DIR . '*.php');
		foreach ($model_files as $model_file) {
			$model_name = str_replace('.php', '', basename($model_file));
			if ($model_name == 'ApplicationModel') {
				continue;
			}
			$instance = new $model_name();
			foreach ($instance->getColumnNames() as $column) {
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

}