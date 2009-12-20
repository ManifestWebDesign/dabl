<?php

/**
 * @param string $route
 */
function load_controller($route){
	$segments = explode('/', $route);
	$last = array_pop($segments);
	$ext = "html";
	if($last!==null){
		$file_parts = explode('.', $last);
		if(count($file_parts) > 1)
			$ext = array_pop($file_parts);
		$segments[] = implode('.', $file_parts);
	}

	//get controller instance
	$c_dir = ROOT.'controllers'.DIRECTORY_SEPARATOR;
	$view_prefix = '';

	foreach($segments as $key => $segment){
		$c_class = ucfirst($segment).'Controller';
		$c_class_file = $c_dir.DIRECTORY_SEPARATOR.$c_class.'.php';
		if(file_exists($c_class_file)){
			require_once $c_class_file;
			unset($segments[$key]);
			$instance = new $c_class;
			break;
		}
		if(is_dir($c_dir.$segment)){
			$c_dir .= $segment.DIRECTORY_SEPARATOR;
			$view_prefix .= $segment.DIRECTORY_SEPARATOR;
			unset($segments[$key]);
			continue;
		}
		file_not_found($route);
	}

	$action = $segments ? array_shift($segments) : DEFAULT_CONTROLLER;
	$instance->view_prefix = $view_prefix;
	$instance->doAction($action, $segments, $ext);
}