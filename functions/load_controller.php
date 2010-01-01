<?php

/**
 * @param string $route
 */
function load_controller($route){
	$render_partial = false;
	$params = explode('/', $route);

	if(@$params[0]=='partial'){
		$render_partial=true;
		array_shift($params);
	}

	$last = array_pop($params);
	$extension = "html";
	if($last!==null){
		$file_parts = explode('.', $last);
		if(count($file_parts) > 1)
			$extension = array_pop($file_parts);
		$params[] = implode('.', $file_parts);
	}

	//get controller instance
	$c_dir = ROOT.'controllers'.DIRECTORY_SEPARATOR;
	$view_prefix = '';

	foreach($params as $key => $segment){
		$c_class = ucfirst($segment).'Controller';
		$c_class_file = $c_dir.DIRECTORY_SEPARATOR.$c_class.'.php';
		if(file_exists($c_class_file)){
			require_once $c_class_file;
			unset($params[$key]);
			$instance = new $c_class;
			break;
		}
		if(is_dir($c_dir.$segment)){
			$c_dir .= $segment.DIRECTORY_SEPARATOR;
			$view_prefix .= $segment.DIRECTORY_SEPARATOR;
			unset($params[$key]);
			continue;
		}
		file_not_found($route);
	}

	$action = $params ? array_shift($params) : DEFAULT_CONTROLLER;
	$instance->view_prefix = $view_prefix;
	$instance->output_format = $extension;
	$instance->render_partial = $render_partial;
	$instance->doAction($action, $params);
}