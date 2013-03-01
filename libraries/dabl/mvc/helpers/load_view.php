<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

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