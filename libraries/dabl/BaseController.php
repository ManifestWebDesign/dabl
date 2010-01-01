<?php

abstract class BaseController {

	/**
	 * @var string
	 */
	public $layout = 'layouts/main';
	public $view_prefix = '';
	public $output_format = 'html';
	public $render_view = true;
	public $render_partial = false;
	private $params = array();

	function __get($var){
		return $this->params[$var];
	}

	function __set($var, $value){
		$this->params[$var] = $value;
	}

	function getParams(){
		return $this->params;
	}

	function setParams($params){
		$this->params = $params;
	}

	function getViewPath(){
		$controller_view_dir = str_replace('controller', '', strtolower(get_class($this)));
		$view = trim($this->view_prefix, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		if($controller_view_dir != DEFAULT_CONTROLLER)
			$view .= $controller_view_dir.DIRECTORY_SEPARATOR;
		return $view;
	}

	function renderView($view){
		$output_format = $this->output_format;
		$params = $this->getParams();

		$has_layout = ($this->layout && !$this->render_partial && $output_format == 'html');

		$params['content'] = load_view($view, $params, $has_layout, $output_format);

		if($has_layout)
			load_view($this->layout, $params, false, $output_format);
	}

	/**
	 * @param string $action_name
	 * @param array $params
	 */
	function doAction($action_name, $params = array()){
		$view = $this->getViewPath($action_name).$action_name;

		method_exists($this, $action_name) || file_not_found($view);

		call_user_func_array(array($this, $action_name), $params);

		if(!$this->render_view)return;
		$this->renderView($view);
	}

}