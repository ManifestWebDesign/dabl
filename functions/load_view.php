<?php

/**
 * @param string $view
 * @param array $params
 * @param bool $return_output
 * @param string $output_format
 * @return string
 */
function load_view($view = null, $params = array(), $return_output = false, $output_format = 'html'){
	if($return_output)
		ob_start();

	switch($output_format){
		case 'json':
			echo json_encode_all($params);
			break;
		case 'html':
			foreach($params as $var => $value)
				$$var = $value;

			$view = trim($view, "/".DIRECTORY_SEPARATOR);

			if(is_dir(ROOT."views".DIRECTORY_SEPARATOR."$view"))
				$view = "$view".DIRECTORY_SEPARATOR."index";

			$view = ROOT."views".DIRECTORY_SEPARATOR."$view.php";

			if(!file_exists($view))
				file_not_found($view);

			require $view;
			break;
		default:
			file_not_found($view);
			break;
	}

	if($return_output)
		return ob_get_clean();
}