<?php

/**
 * Calls print_r, encodes html entities, and wraps the resulting string in <pre>
 * tags to make it readable in a browser.
 * @param mixed $array
 * @param bool $return
 * @return string
 */
function print_r2($array, $return = false) {
	$string = '<pre>' . htmlentities(print_r($array, true)) . '</pre>';
	if ($return) {
		return $string;
	}
	echo $string;
}