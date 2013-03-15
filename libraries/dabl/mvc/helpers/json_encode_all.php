<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

/**
 * Convert a value to JSON
 *
 * This function returns a JSON representation of $param. It uses json_encode
 * to accomplish this, but converts objects and arrays containing objects to
 * associative arrays first. This way, objects that do not expose (all) their
 * properties directly but only through an Iterator interface are also encoded
 * correctly.
 */
function json_encode_all(&$param) {

	if (is_object($param) || is_array($param)) {
		return json_encode(object_to_array($param));
	}

	return json_encode($param);
}