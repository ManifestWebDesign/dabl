<?php

/**
 *
 * @param string $label
 * @param mixed $data
 */
function set_persistant_value($label, $data){
	$_SESSION['__persisted'][$label] = $data;
}

/**
 *
 * @param mixed $data
 */
function set_persistant_values($data){
	$_SESSION['__persisted'] = $data;
}

/**
 *
 * @param string $label
 * @return mixed
 */
function get_persistant_value($label){
	return isset($_SESSION['__persisted'][$label]) ? $_SESSION['__persisted'][$label] : null;
}

/**
 *
 * @return array
 */
function get_persistant_values(){
	return isset($_SESSION['__persisted']) ? (array)$_SESSION['__persisted'] : array();
}

/**
 *
 * @return array
 */
function get_clean_persistant_values(){
	$values = get_persistant_values();
	clean_persistant_values();
	return $values;
}

/**
 *
 */
function clean_persistant_values(){
	if(isset($_SESSION['__persisted']))
		unset($_SESSION['__persisted']);
}