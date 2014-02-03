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
		'json',
		'jsonp'
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
	public $flash = array();

	/**
	 * @deprecated use $this->flash instead
	 */
	public $persistant;

	/**
	 * @var ControllerRoute
	 */
	protected $route;

	function __destruct() {
		// store flash params in session
		$this->writeFlashValues();
	}

	function __construct(ControllerRoute $route = null) {
		$this->setRoute($route);

		$this->loadFlashValues();
	}

	/**
	 * @param string|ControllerRoute $route
	 * @param string $headers ControllerRoute construct arg.  Use only if $route is a string.
	 * @param string $request_params ControllerRoute construct arg.  Use only if $route is a string.
	 * @return Controller
	 */
	static function load($route, array $headers = array(), array $request_params = array()) {
		if (!($route instanceof ControllerRoute)) {
			$route = new ControllerRoute($route, $headers, $request_params);
		}
		$controller = $route->getController();

		if (null === $controller) {
			file_not_found($route);
		}

		$controller->doAction($route->getAction(), $route->getParams());
		return $controller;
	}

	/**
	 * Recover flash params from session
	 */
	private function loadFlashValues() {
		foreach ($this->flash as $key => $value) {
			$this[$key] = $value;
		}
		$this->flash = array();
		$this->persistant = &$this->flash;

		foreach (Flash::getCleanAll() as $key => $value) {
			$this[$key] = $value;
		}
	}

	/**
	 * Stores $this->flash in the the session for the next page view
	 */
	private function writeFlashValues() {
		foreach ($this->flash as $key => $value) {
			Flash::set($key, $value);
		}
		$this->flash = array();
		$this->persistant = &$this->flash;
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
	protected function setRoute(ControllerRoute $route = null) {
		$this->route = $route;
		if (null !== $route) {
			if ($route->getViewDir()) {
				$this->viewDir = $route->getViewDir();
			}

			$this->renderPartial = $route->isPartial();

			if ($route->getExtension()) {
				$this->outputFormat = $route->getExtension();
			}
		}
		return $this;
	}

	/**
	 * Returns an array with the response/view parameters
	 * @return array
	 */
	function getValues() {
		return $this->getArrayCopy();
	}

	/**
	 * @deprecated use getValues
	 */
	function getParams() {
		return getValues();
	}

	/**
	 * Replaces the response/view parameters with the given array
	 * @param array $array
	 */
	function setValues($array) {
		$this->exchangeArray($array);
	}

	/**
	 * @deprecated use setValues
	 */
	function setParams($array) {
		return $this->setValues($array);
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
					header('Content-type: application/json; charset=utf-8');
					if ($this->isRestful() && !empty($this['errors'])) {
						header($_SERVER['SERVER_PROTOCOL'] . ' 400 Error');
						$params = array('errors' => $this['errors']);
					}
				}
				echo json_encode_all($params);
				break;

			case 'jsonp':
				if (!headers_sent()) {
					header('Content-type: application/javascript; charset=utf-8');
					if ($this->isRestful() && !empty($this['errors'])) {
						header($_SERVER['SERVER_PROTOCOL'] . ' 400 Error');
						$params = array('errors' => $this['errors']);
					}
				}
				$callback = 'callback';
				if ($this->route) {
					$callback = $this->route->getJsonPCallback();
				}
				echo $callback . '(' . json_encode_all($params) . ')';
				break;

			case 'xml':
				if (!headers_sent()) {
					header('Content-type: application/xml');
					if ($this->isRestful() && !empty($this['errors'])) {
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
			$_REQUEST = $_POST = $_FILES = $_GET = array();
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

		if ($this->isRestful() && strpos($url, 'http') !== 0) {
			$url = '/rest/' . ltrim($url, '/');
			$this->writeFlashValues();
			$this->flash = array();
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

		// todo: this probably belongs somewhere else
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