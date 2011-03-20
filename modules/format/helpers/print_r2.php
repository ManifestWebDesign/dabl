<?php

/**
 * Wraps output of print_r in <pre> tags to make it
 * readable in a browser
 * @param mixed $array
 * @param bool $return
 * @return string
 */
function print_r2($array, $return = false){
	$string = '<pre>'.print_r($array, true).'</pre>';
	if($return) return $string;
	echo $string;
}