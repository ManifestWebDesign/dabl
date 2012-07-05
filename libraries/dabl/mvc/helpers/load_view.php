<?php

/**
 * @package helpers
 * @param string $view
 * @param array $params
 * @param bool $return_output
 * @deprecated
 * @return View|null
 */
function load_view($view = null, $params = array(), $return_output = false) {
	return View::load($view, $params, $return_output);
}