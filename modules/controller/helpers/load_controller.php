<?php

/**
 * @param string $route
 */
function load_controller($route){
	$render_partial = false;
	$route = str_replace('\\', '/', $route);
	$route = trim($route, '/');

	$params = explode('/', $route);

	if(@$params[0]=='partial'){
		$render_partial=true;
		array_shift($params);
	}

	$last = array_pop($params);
	$extension = 'html';
	if($last!==null){
		$file_parts = explode('.', $last);
		if(count($file_parts) > 1)
			$extension = array_pop($file_parts);
		$params[] = implode('.', $file_parts);
	}

	// directory where controllers are found
	$c_dir = CONTROLLERS_DIR;
	$view_prefix = '';
	$view_dir = '';
	$instance = null;

	while ($segment = array_shift($params)) {
		$c_class = str_replace(array('_', '-'), ' ', $segment);
		$c_class = ucwords($c_class);
		$c_class = str_replace(' ', '', $c_class).'Controller';
		$c_class_file = $c_dir.$c_class.'.php';

		//check if file exists
		if(is_file($c_class_file)){
			require_once $c_class_file;
			$instance = new $c_class;
			$view_dir = strtolower($segment);
			break;
		}

		//check if the segment matches directory name
		$t_dir = $c_dir.$segment.DIRECTORY_SEPARATOR;
		if(is_dir($t_dir)){
			$c_dir = $t_dir;
			$view_prefix .= $segment.DIRECTORY_SEPARATOR;
			continue;
		}

		// file and directory not found; use default controller
		array_unshift($params, $segment);
		break;
	}

	if (!$instance) {
		//fallback check if default index exists in directory
		$alternate_c_class = ucwords(DEFAULT_CONTROLLER).'Controller';
		$alternate_c_class_file = $c_dir.$alternate_c_class.'.php';
		if(is_file($alternate_c_class_file)){
			require_once $alternate_c_class_file;
			$instance = new $alternate_c_class;
		}
	}

	//if no instance of a Controller, 404
	if(!$instance)
		file_not_found($route);

	$view_dir = $view_dir ? $view_prefix.$view_dir.DIRECTORY_SEPARATOR : $view_prefix;

	if(!$instance->viewDir)
		$instance->viewDir = $view_dir;

	if($render_partial)
		$instance->renderPartial = $render_partial;

	$instance->outputFormat = $extension;

	//Restore Flash params
	$instance->setParams(
		array_merge_recursive(
			get_clean_persistant_values(),
			$instance->getParams()
	));

	$instance->doAction(array_shift($params), $params);
}
