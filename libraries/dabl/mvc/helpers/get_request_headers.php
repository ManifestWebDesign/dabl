<?php

if (!function_exists('get_request_headers')) {

	function get_request_headers() {
		static $headers = array();

		if (!empty($headers)) {
			return $headers;
		}
		if (!function_exists('getallheaders')) {
			foreach ($_SERVER as $key => $value) {
				if (substr($key, 0, 5) == 'HTTP_') {
					$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
					$headers[$key] = $value;
				} else {
					$headers[$key] = $value;
				}
			}
		} else {
			$headers = getallheaders();
		}
		return $headers;
	}

}