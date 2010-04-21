<?php

abstract class BaseController extends ArrayObject {

	/**
	 * @var string
	 */
	public $layout = 'layouts/main';
	public $viewPrefix = '';
	public $viewDir = '';
	public $outputFormat = 'html';
	public $loadView = true;
	public $renderPartial = false;
	public $persistant = array();

	function  __destruct() {
		set_persistant_values(array_merge_recursive(get_persistant_values(), $this->persistant));
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
		$controller_view_dir = $this->viewDir ? $this->viewDir : str_replace('controller', '', strtolower(get_class($this)));
		$view = str_replace('\\', '/', $this->viewPrefix);
		$view = trim($view, '/').'/';
		if($controller_view_dir != DEFAULT_CONTROLLER)
			$view .= $controller_view_dir.'/';

		return $view;
	}

	function renderView($view){
		return $this->loadView();
	}

	function loadView($view){
		$output_format = $this->outputFormat;
		$params = $this->getParams();

		$use_layout = ($this->layout && $this->renderPartial===false && $output_format == 'html');
		$params['content'] = load_view($view, $params, $use_layout, $output_format);

		if($use_layout)
			load_view($this->layout, $params, false, $output_format);

		$this->loadView = false;
	}

	/**
	 * @param string $action_name
	 * @param array $params
	 */
	function doAction($action_name, $params = array()){
		$view = $this->getViewPath($action_name).$action_name;

		method_exists($this, $action_name) || file_not_found($view);

		call_user_func_array(array($this, $action_name), $params);

		if(!$this->loadView)return;
		$this->loadView($view);
	}

}