<?php

/**
 * Convert an object into an associative array
 *
 * This function converts an object into an associative array by iterating
 * over its public properties. Because this function uses the foreach
 * construct, Iterators are respected. It also works on arrays of objects.
 *
 * @return array
 */
function object_to_array($var, $loop_exclude = array()) {
	if (is_object($var)) {
		if (in_array($var, $loop_exclude, true)) {
			return '*RECURSION*';
		}
		$loop_exclude[] = $var;

		if ($var instanceof ArrayObject) {
			$var = $var->getArrayCopy();
		} elseif (method_exists($var, 'toArray')) {
			// use toArray() if it exists so object can control array conversion if it wants to
			$var = $var->toArray();
		} elseif ($var instanceof Traversable) {
			$_var = array();
			foreach ($var as $key => $val) {
				$_var[$key] = $val;
			}
			$var = $_var;
		} else {
			$var = get_object_vars($var);
		}
	} elseif (!is_array($var)) {
		throw new InvalidArgumentException('object_to_array can only convert arrays and objects');
	}

	// loop over elements/properties
	foreach ($var as &$value) {
		// recursively convert objects
		if (is_object($value) || is_array($value)) {
			$value = object_to_array($value, $loop_exclude);
		}
	}
	return $var;
}