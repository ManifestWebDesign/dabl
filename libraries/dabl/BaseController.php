<?php

abstract class BaseController {

	/**
	 * @var string
	 */
	public $layout = 'layouts/main';
	public $view_prefix = '';

	/**
	 * @param string $action_name
	 * @param array $params
	 * @param string $output_format
	 */
	function doAction($action_name, $params = array(), $output_format = 'html'){
		$controller_view_dir = str_replace('controller', '', strtolower(get_class($this)));
		$view = $this->view_prefix;
		if($controller_view_dir == DEFAULT_CONTROLLER)
			$view .= $action_name;
		else
			$view .= $controller_view_dir.DIRECTORY_SEPARATOR.$action_name;

		method_exists($this, $action_name) || file_not_found($view);

		call_user_func_array(array($this, $action_name), $params);

		if(headers_sent())
			return;

		$vars = get_object_vars($this);

		switch($output_format){
			case 'json':
				die(json_encode($vars));
				break;
			case 'html':
				$has_layout = (bool)$this->layout;

				$vars['content'] = load_view($view, $vars, $has_layout);
				if($has_layout)
					load_view($this->layout, $vars);
				break;
			default:
				file_not_found($view);
				break;
		}
	}

}