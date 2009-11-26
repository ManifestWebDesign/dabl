<?php

function load_view($view, $params = array(), $layout = null){
	foreach($params as $var => $value)
		$$var = $value;

	if(substr($view, -1)=='/')
		$view = substr($view, 0, -1);

	if(is_dir(ROOT."views/$view"))
		$view = "$view/index";

	$view = ROOT."views/$view.php";

	if(!file_exists($view)){
		throw new Exception("$view not found");
		if(!headers_sent())
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		die("$view not found");
	}

	if($layout)ob_start();
	require $view;
	if($layout){
		$params['content'] = ob_get_clean();
		load_view($layout, $params);
	}

}