<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

if (!function_exists('get_request_headers')) {

	function get_request_headers() {
		static $headers = array();

		if (!empty($headers)) {
			return $headers;
		}
		if (!function_exists('getallheaders')) {
			foreach ($_SERVER as $key => $value) {
				if (substr($key, 0, 5) == 'HTTP_') {
					$key = substr($key, 5);
				} elseif (array_key_exists($key, $headers)) {
					continue;
				}
				$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
				$headers[$key] = $value;
			}
		} else {
			$headers = getallheaders();
		}
		return $headers;
	}

}