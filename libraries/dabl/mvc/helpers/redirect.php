<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

/**
 * Redirect to another location.
 * This location will be run through {@see site_url()}.
 *
 * @param string $url Path to redirect to
 * @param bool $die Whether or not to kill the script
 * @return void
 */
function redirect($url = '', $die = true) {
	header('Location: ' . site_url($url));
	if ($die) {
		die();
	}

	header('Content-Length: 0', true);
	header('Content-Type: text/html', true);
	header('Connection: close');
	flush();
	session_write_close();
	set_time_limit(0);
}