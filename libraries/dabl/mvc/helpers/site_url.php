<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

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
	if (is_array($url)) {
		$url = implode('/', $url);
	}

	if (
		false !== strpos($url, ':')
		|| 0 === strpos($url, '#')
		|| 0 === strpos($url, '//')
	) {
		return $url;
	}

	if ($version) {
		$path = PUBLIC_DIR . $url;
		if (!is_file($path)) {
			file_not_found($url);
		}
		$char = (strpos($url, '?') === false) ? '?' : '&';
		$url .= $char . 'v=' . filemtime($path);
	}

	$url = trim($url, '/');

	return BASE_URL . $url;
}