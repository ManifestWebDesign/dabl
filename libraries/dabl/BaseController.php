<?php

abstract class BaseController extends ArrayObject {

	/**
	 * @var string
	 */
	public $layout = 'layouts/main';
	public $view_prefix = '';
	public $view_dir = '';
	public $output_format = 'html';
	public $render_view = true;
	public $render_partial = false;

	/**
	 * @param string $var
	 * @return mixed
	 */
	function __get($var){
		return $this[$var];
	}

	/**
	 * @param string $var
	 * @param mixed $value
	 */
	function __set($var, $value){
		$this[$var] = $value;
	}

	/**
	 * Returns an array with the view parameters
	 * @return array
	 */
	function getParams(){
		return $this->getArrayCopy();
	}

	/**
	 * Replaces the view parameters with the given array
	 * @param array $array
	 */
	function setParams($array){
		$this->exchangeArray($array);
	}

	/**
	 * @return string
	 */
	function getViewPath(){
		$controller_view_dir = $this->view_dir ? $this->view_dir : str_replace('controller', '', strtolower(get_class($this)));
		$view = str_replace('\\', '/', $this->view_prefix);
		$view = trim($view, '/').'/';
		if($controller_view_dir != DEFAULT_CONTROLLER)
			$view .= $controller_view_dir.'/';

		return $view;
	}

	function renderView($view){
		$output_format = $this->output_format;
		$params = $this->getParams();

		$use_layout = ($this->layout && $this->render_partial===false && $output_format == 'html');
		$params['content'] = load_view($view, $params, $use_layout, $output_format);

		if($use_layout)
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