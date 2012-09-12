<?php

abstract class Controller extends ArrayObject {

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
	 * XML and JSON must be explicitly enabled
	 * @var array Allowed output formats(extensions)
	 */
	public $allowedFormats = array(
		'html',
		'xml',
		'json'
	);

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

		return $view;
	}

	/**
	 * Appends the given $action_name to the viewDir and appends the resulting string
	 * @param string $action_name
	 */
	protected function getView($action_name) {
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

		if (!in_array($this->outputFormat, $this->allowedFormats)) {
			throw new RuntimeException("The extension '{$this->outputFormat}' is not supported.");
		}

		switch ($this->outputFormat) {
			case 'html':
				if ($this->layout && $this->renderPartial === false) {
					$this['content_view'] = $view;
					$view = $this->layout;
				}
				View::load($view, $this, false);
				break;

			case 'json':
				if (!headers_sent()) {
					header('Content-type: application/json');
				}
				echo json_encode_all($this);
				break;

			case 'xml':
				if (!headers_sent()) {
					header('Content-type: application/xml');
				}
				echo xml_encode_all($this);
				break;

			default:
				throw new RuntimeException("The extension '{$this->outputFormat}' is not supported.");
				break;
		}

		$this->loadView = false;
	}

	/**
	 * Redirect to another location, preserving the partial and
	 * output format settings.
	 *
	 * @param string $url Path to redirect to
	 * @param bool $die Whether or not to kill the script
	 * @return void
	 */
	function redirect($url = '', $die = true) {
		if ($this->renderPartial) {
			$url = '/partial/' . ltrim($url, '/');
		}

		if ('html' !== $this->outputFormat) {
			if (strpos($url, '?') !== false) {
				$parts = explode('?', $url, 2);
				$url = array_shift($parts);
				$url .= '.' . $this->outputFormat;
				array_unshift($parts, $url);
				$url = implode('?', $parts);
			} else {
				$url .= '.' . $this->outputFormat;
			}
		}

		redirect($url, $die);
	}

	/**
	 * @param string $action_name
	 * @param array $args
	 */
	function doAction($action_name = null, $args = array()) {

		if (!$action_name) {
			$action_name = DEFAULT_CONTROLLER;
		}
		$method_name = str_replace(array('-', '_', ' '), '', $action_name);
		$view = $this->getView($action_name);

		if (!is_array($args) && !($args instanceof ArrayObject)) {
			$args = array($args);
		}

		if ((!method_exists($this, $method_name) && !method_exists($this, '__call')) || strpos($action_name, '_') === 0) {
			file_not_found($view);
		}

		call_user_func_array(array($this, $method_name), $args);

		if (!$this->loadView) {
			return;
		}
		$this->loadView($view);
	}

}