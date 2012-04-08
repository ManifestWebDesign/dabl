<?php

/**
 * @package helpers
 * @param string $view
 * @param array $params
 * @param bool $return_output
 * @return string
 */
function load_view($view = null, $params = array(), $return_output = false) {

	$_ = array(
		'view' => &$view,
		'params' => &$params,
		'return_output' => &$return_output,
	);

	foreach ($_['params'] as $_var => &$_value) {
		if ('_' === $_var) {
			throw new RuntimeException('Attempting to overwrite $_ with view parameter');
		}
		$$_var = &$_value;
	}

	$_['view'] = str_replace('\\', '/', $_['view']);
	$_['view'] = trim($view, '/');

	if (!is_file(VIEWS_DIR . $_['view'] . '.php') && is_dir(VIEWS_DIR . $_['view'])) {
		$_['view'] = $_['view'] . '/index';
	}

	$_['view'] = VIEWS_DIR . "{$_['view']}.php";

	if (!is_file($_['view'])) {
		file_not_found($_['view']);
	}

	if ($_['return_output']) {
		ob_start();
	}

	require $_['view'];

	if ($_['return_output']) {
		return ob_get_clean();
	}
}