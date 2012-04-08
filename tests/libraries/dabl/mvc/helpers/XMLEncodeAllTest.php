<?php

class XMLEncodeAllTest extends PHPUnit_Framework_TestCase {

	function testOutput() {
		$prop = new stdClass;
		$prop->foo = 1;

		$prop2 = new ArrayObject;
		$prop2[] = $prop;

		$foo = new stdClass;
		$foo->a = $prop;
		$foo->b = $prop2;
		$foo->c = $prop;
		$foo->d = $foo;

		$class = array(
			array($foo),
			array($foo),
			array(array($foo)),
		);

		$this->assertEquals(
			xml_encode_all($class),
			'<?xml version="1.0" encoding="utf-8"?>
<data><item><item><a><foo>1</foo></a><b><item><foo>1</foo></item></b><c><foo>1</foo></c><d>*RECURSION*</d></item></item><item><item><a><foo>1</foo></a><b><item><foo>1</foo></item></b><c><foo>1</foo></c><d>*RECURSION*</d></item></item><item><item><item><a><foo>1</foo></a><b><item><foo>1</foo></item></b><c><foo>1</foo></c><d>*RECURSION*</d></item></item></item></data>
'
		);
	}

}