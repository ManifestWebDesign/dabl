<?php

abstract class BaseController extends ArrayObject {

	/**
	 * @var string The layout to render around the view if renderPartial is false
	 */
	public $layout = 'layouts/main';
	
	/**
	 * @var string Path where the view should reside
	 */
	public $viewDir = '';
	
	/**
	 * @var string Inticates how to render the view
	 */
	public $outputFormat = 'html';
	
	/**
	 * @var bool Whether or not to automatically load the view after the action has been called
	 */
	public $loadView = true;
	
	/**
	 * @var bool Whether to skip loading the layout and only load the view
	 */
	public $renderPartial = false;
	
	/**
	 * @var array containing controller params that should persist until the next request
	 */
	public $persistant = array();

	function __destruct() {
		set_persistant_values(array_merge_recursive(get_persistant_values(), $this->persistant));
	}

	/**
	 * Returns an array with the view parameters
	 * @return array
	 */
	function getParams() {
		return $this->getArrayCopy();
	}

	/**
	 * Replaces the view parameters with the given array
	 * @param array $array
	 */
	function setParams($array) {
		$this->exchangeArray($array);
	}

	/**
	 * @return string
	 */
	function getViewDir() {
		$view = str_replace('\\', '/', $this->viewDir);
		$view = trim($view, '/');

		if ($view === DEFAULT_CONTROLLER) {
			$view = '';
		}
		
		$index_view = '/' . DEFAULT_CONTROLLER;
		
		$pos = strrpos($view, $index_view);
		
		if ($pos !== false && strlen($view) === ($pos + strlen($index_view))) {
			$view = substr($view, 0, $pos);
		}
		
		$view .= '/';

		return str_replace('/', DIRECTORY_SEPARATOR, $view);
	}
	
	/**
	 * Appends the given $action_name to the viewDir and appends the resulting string
	 * @param string $action_name
	 */
	function getView($action_name) {
		return $this->getViewDir() . $action_name;
	}

	/**
	 * @deprecated use loadView instead
	 * @param string $view
	 * @see loadView
	 */
	function renderView($view) {
		return $this->loadView($view);
	}

	/**
	 * Loads the given view using the layout and parameters in $this.
	 * @param string $view The view to load.  This should be a full view.  It will not be appended to
	 * the viewDir
	 */
	function loadView($view) {
		$output_format = $this->outputFormat;
		$params = $this->getParams();

		$return_output = $use_layout = ($this->layout && $this->renderPartial === false && $output_format == 'html');
		$params['content'] = load_view($view, $params, $return_output, $output_format);

		if ($use_layout) {
			load_view($this->layout, $params, false, $output_format);
		}

		$this->loadView = false;
	}

	/**
	 * @param string $action_name
	 * @param array $params
	 */
	function doAction($action_name = null, $params = array()) {
		
		$action_name = $action_name ? $action_name : DEFAULT_CONTROLLER;
		$method_name = str_replace(array('-', '_', ' '), '', $action_name);
		$view = $this->getView($action_name);
		
		if ((!method_exists($this, $method_name) && !method_exists($this, '__call')) || strpos($action_name, '_') === 0) {
			file_not_found($view);
		}

		call_user_func_array(array($this, $method_name), $params);

		if (!$this->loadView) {
			return;
		}
		$this->loadView($view);
	}

}
