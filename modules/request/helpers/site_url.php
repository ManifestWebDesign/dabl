<?php

/**
 * Return a rooted path to the given URL.
 * If the URL is already a complete URL (prefixed with http:// or
 * https://), then the URL will be returned unmodified.
 * If $url is an array, then the contents will be imploded.
 *
 * @param mixed $url URL or array of URL segments
 * @param bool $version Append the modification time of the file to the URL
 * @return string
 * @author Dan Blaisdell
 */
function site_url($url = '', $version = false) {
	if (is_array($url))
		$url = implode('/', $url);

	if (strpos($url, 'https://') === 0 || strpos($url, 'http://') === 0)
		return $url;

	if ($version) {
		$path = PUBLIC_DIR . $url;
		if (!is_file($path))
			throw new Exception('File ' . $url . ' not found.');
		$char = (strpos($url, '?') === false) ? '?' : '&';
		$url .= $char . 'v=' . filemtime($path);
	}

	$url = trim($url, '/');

	return BASE_URL . $url;
}
