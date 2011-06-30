<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserTest
 *
 * @author Dan
 */
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../../config.php';

class UserTest extends PHPUnit_Framework_TestCase {

	/**
	 * @group count
	 * @covers BaseModel::__get, BaseModel::__set
	 */
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
				if ($instance->isNumericType($column_type)) {
					$instance->$column = '5';
					$this->assertEquals($instance->$column, 5);
				} elseif ($instance->isTemporalType($column_type)) {
					$instance->$column = time();
					switch ($column_type) {
						case BaseModel::COLUMN_TYPE_TIME:
							$value = date($instance->getConnection()->getTimeFormatter());
							break;
						case BaseModel::COLUMN_TYPE_TIMESTAMP:
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