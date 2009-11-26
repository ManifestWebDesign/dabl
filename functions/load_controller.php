<?php

function load_controller($route){
	$segments = explode('/', $route);

	//get controller instance
	$c_dir = ROOT.'controllers/';
	foreach($segments as $key => $segment){
		$c_class = ucfirst($segment).'Controller';
		$c_class_file = $c_dir.'/'.$c_class.'.php';
		if(file_exists($c_class_file)){
			require_once $c_class_file;
			unset($segments[$key]);
			$instance = new $c_class;
			break;
		}
		if(is_dir($c_dir.$segment)){
			$c_dir .= $segment."/";
			unset($segments[$key]);
			continue;
		}
		if(!headers_sent())
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		throw new Exception("$route not found");
	}
	$action = $segments ? array_shift($segments) : DEFAULT_CONTROLLER;
//		echo get_class($instance)."/$action";
	call_user_func_array(array($instance, $action), $segments);
}