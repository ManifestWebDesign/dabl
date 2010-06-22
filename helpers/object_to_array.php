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
	$references = array();

	//use toArray() if it exists so object can control array conversion if it wants to
	if(is_object($var)){
		if(method_exists($var, 'toArray'))
			$var = $var->toArray();
		else
			$var = get_object_vars($var);
	}

	// loop over elements/properties
	foreach ($var as $key => &$value) {
		// recursively convert objects
		if (is_object($value) || is_array($value)) {
			// but prevent cycles
			if (!in_array($value, $references)) {
				$value = object_to_array($value);
				$references[] = &$value;
			}
		}
	}
	return $var;
}