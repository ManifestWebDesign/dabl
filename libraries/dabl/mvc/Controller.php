<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

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

	/**
	 * @var ControllerRoute
	 */
	protected $route;

	function __destruct() {
		$this->storePeristant();
	}

	function __construct(ControllerRoute $route = null) {
		$this->setRoute($route);
	}

	/**
	 * @param string|ControllerRoute $route
	 * @return Controller
	 */
	static function load($route, $headers = array(), $http_verb = null) {
		if (!($route instanceof ControllerRoute)) {
			$route = new ControllerRoute($route, $headers, $http_verb);
		}
		$controller = $route->getController();

		if (null === $controller) {
			file_not_found($route);
		}

		$controller->doAction($route->getAction(), $route->getParams());
		return $controller;
	}

	/**
	 * @return ControllerRoute
	 */
	function getRoute() {
		return $this->route;
	}

	protected function isRestful() {
		return ($this->route && $this->route->isRestful());
	}

	/**
	 * @param ControllerRoute $route
	 * @return Controller
	 */
	function setRoute(ControllerRoute $route = null) {
		$this->route = $route;
		if (null !== $route) {
			if ($route->getViewDir()) {
				$this->viewDir = $route->getViewDir();
			}

			$this->renderPartial = $route->isPartial();

			if ($route->getExtension()) {
				$this->outputFormat = $route->getExtension();
			}

			// Restore Flash params
			$this->setParams(array_merge_recursive(get_clean_persistant_values(), $route->getParams()));
		}
		return $this;
	}

	/**
	 * Stores $this->persistant in the the session for the next page view
	 */
	private function storePeristant() {
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
		// normalize slashes
		$view = trim(str_replace('\\', '/', $this->viewDir), '/');

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
	function loadView($view, $params = null) {

		if (!in_array($this->outputFormat, $this->allowedFormats)) {
			throw new RuntimeException("The extension '{$this->outputFormat}' is not supported.");
		}

		if (func_num_args() == 1) {
			$params = $this;
		}

		switch ($this->outputFormat) {
			case 'html':
				if ($this->layout && $this->renderPartial === false) {
					$this['content_view'] = $view;
					$view = $this->layout;
				}
				View::load($view, $params, false);
				break;

			case 'json':
				if (!headers_sent()) {
					header('Content-type: application/json');
					if ($this->isRestful() && @$this['errors']) {
						header($_SERVER['SERVER_PROTOCOL'] . ' 400 Error');
						$params = array('errors' => $this['errors']);
					}
				}
				echo json_encode_all($params);
				break;

			case 'xml':
				if (!headers_sent()) {
					header('Content-type: application/xml');
					if ($this->isRestful() && @$this['errors']) {
						header($_SERVER['SERVER_PROTOCOL'] . ' 400 Error');
						$params = array('errors' => $this['errors']);
					}
				}
				echo xml_encode_all($params);
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

		if ($this->isRestful()) {
			unset($_GET);
			unset($_FILES);
			unset($_POST);
			unset($_REQUEST);
		}

		if ('html' !== $this->outputFormat) {
			if (strpos($url, '?') !== false) {
				$parts = explode('?', $url, 2);
				$url = array_shift($parts);
				$url .= '.' . $this->outputFormat;

				if ($this->isRestful()) {
					$params = array();
					parse_str(array_shift($parts), $params);
					$_GET = $params;
					$_REQUEST = $params;
				}

				array_unshift($parts, $url);
				$url = implode('?', $parts);
			} else {
				$url .= '.' . $this->outputFormat;
			}
		}

		if ($this->isRestful()) {
			$url = '/rest/' . ltrim($url, '/');
			$this->storePeristant();
			$this->persistant = array();
			Controller::load($url);
			die;
		}

		redirect($url, $die);
	}

	/**
	 * @param string $action_name
	 * @param array $args
	 */
	function doAction($action_name = null, $args = array()) {

		if ($this->isRestful()) {
			$has_id = false;
			if ((string) (int) $action_name === (string) $action_name) {
				array_unshift($args, $action_name);
				$action_name = null;
				$has_id = true;
			}
			if (!$action_name) {
				switch ($this->route->getHttpVerb()) {
					case 'POST':
					case 'PUT':
						$action_name = 'save';
						break;
					case 'GET':
						if ($has_id) {
							$action_name = 'show';
						}
						break;
					case 'DELETE':
						if ($has_id) {
							$action_name = 'delete';
						}
						break;
				}
			}
		}

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

		$result = call_user_func_array(array($this, $method_name), $args);

		if (!$this->loadView) {
			return;
		}
		if ($this->isRestful()) {
			$this->loadView($view, $result);
		} else {
			$this->loadView($view);
		}
	}

}