<?php

/**
 * @param string $view
 * @param array $params
 * @param bool $return_output
 * @return string
 */
function load_view($view, $params = array(), $return_output = false){
	foreach($params as $var => $value)
		$$var = $value;

	$view = trim($view, "/".DIRECTORY_SEPARATOR);

	if(is_dir(ROOT."views".DIRECTORY_SEPARATOR."$view"))
		$view = "$view".DIRECTORY_SEPARATOR."index";

	$view = ROOT."views".DIRECTORY_SEPARATOR."$view.php";

	if(!file_exists($view))
		file_not_found($view);

	if($return_output)
		ob_start();

	require $view;

	if($return_output)
		return ob_get_clean();
}