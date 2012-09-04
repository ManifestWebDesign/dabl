<?php

/**
 * Test class for function object_to_array.
 */
class ObjectToArrayTest extends PHPUnit_Framework_TestCase {

	function testObjectRecursionDetection() {
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
			json_encode(object_to_array($class)),
			'[[{"a":{"foo":1},"b":[{"foo":1}],"c":{"foo":1},"d":"*RECURSION*"}],[{"a":{"foo":1},"b":[{"foo":1}],"c":{"foo":1},"d":"*RECURSION*"}],[[{"a":{"foo":1},"b":[{"foo":1}],"c":{"foo":1},"d":"*RECURSION*"}]]]'
		);
	}

	function testNonDestructive() {
		$prop = new stdClass;
		$prop->foo = 1;

		$prop2 = new ArrayObject;
		$prop2[] = $prop;

		$foo = new stdClass;
		$foo->a = $prop;

		$class = array(
			$foo
		);

		$print_r = print_r($class, true);
		object_to_array($class);
		$this->assertEquals(print_r($class, true), $print_r);
	}

}