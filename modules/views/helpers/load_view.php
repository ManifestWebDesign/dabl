<?php

/**
 * @package helpers
 * @param string $view
 * @param array $params
 * @param bool $return_output
 * @param string $output_format
 * @return string
 */
function load_view($view = null, $params = array(), $return_output = false, $output_format = 'html') {

	$_ = array(
		'view' => $view,
		'params' => $params,
		'return_output' => $return_output,
		'output_format' => $output_format
	);

	if ($_['return_output']) {
		ob_start();
	}

	switch ($_['output_format']) {
		case 'json':
			if (!$_['return_output'] && !headers_sent()) {
				header('Content-type: application/json');
			}
			echo json_encode_all($_['params']);
			break;

		case 'xml':
			if (!$_['return_output'] && !headers_sent()) {
				header('Content-type: application/xml');
			}
			echo xml_encode_all($_['params']);
			break;

		case '':
		case 'html':
			foreach ($_['params'] as $_var => $_value) {
				if ('_' === $_var) {
					throw new Exception('Attempting to overwrite $_ with view parameter');
				}
				$$_var = $_value;
			}

			$_['orig_view'] = $_['view'];
			$_['view'] = str_replace('\\', '/', $_['view']);
			$_['view'] = trim($view, '/');
			$_['view'] = str_replace('/', DIRECTORY_SEPARATOR, $_['view']);

			if (is_dir(VIEWS_DIR . $_['view']))
				$_['view'] = $_['view'] . DIRECTORY_SEPARATOR . "index";

			$_['view'] = VIEWS_DIR . "{$_['view']}.php";

			if (!is_file($_['view']))
				file_not_found($_['orig_view']);

			require $_['view'];
			break;

		default:
			throw new exception("The extension {$_['output_format']} is not yet supported.");
			file_not_found($_['view']);
			break;
	}

	if ($_['return_output']) {
		return ob_get_clean();
	}
}
