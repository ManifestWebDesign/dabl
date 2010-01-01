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
function object_to_array($var) {
	$result = array();
	$references = array();

	//use toArray() if it exists so object can control array conversion if it wants to
	if(is_object($var)){
		if(method_exists($var, 'toArray'))
			$var = $var->toArray();
		else
			$var = get_object_vars($var);
	}

	// loop over elements/properties
	foreach ($var as $key => $value) {
		// recursively convert objects
		if (is_object($value) || is_array($value)) {
			// but prevent cycles
			if (!in_array($value, $references)) {
				$result[$key] = object_to_array($value);
				$references[] = $value;
			}
		}
		else {
			// simple values are untouched
			$result[$key] = $value;
		}
	}
	return $result;
}

/**
 * Convert a value to JSON
 *
 * This function returns a JSON representation of $param. It uses json_encode
 * to accomplish this, but converts objects and arrays containing objects to
 * associative arrays first. This way, objects that do not expose (all) their
 * properties directly but only through an Iterator interface are also encoded
 * correctly.
 */
function json_encode_all($param) {
	if (is_object($param) || is_array($param))
		$param = object_to_array($param);
	return json_encode($param);
}