<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

if (!function_exists('h')) {

	/**
	 * As of PHP 5.3, the default htmlentities encoding is ISO-8859-1, which among other things, hammers smartquotes
	 * coming from UTF-8. And now we get a concise wrapper!
	 */
	function h($value) {
		return htmlentities($value, ENT_COMPAT, 'UTF-8');
	}

}