<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

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
		if ($var instanceof JsonSerializable) {
			return $var->jsonSerialize();
		}

		if (in_array($var, $loop_exclude, true)) {
			return '*RECURSION*';
		}
		$loop_exclude[] = $var;

		if ($var instanceof ArrayObject) {
			$var = $var->getArrayCopy();
		} elseif (method_exists($var, 'toArray')) {
			$var = $var->toArray();
		} elseif ($var instanceof Traversable) {
			$var = iterator_to_array($var, true);
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