<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

/**
 * Represents a url string for loading a controller action.
 */
class ControllerRoute {

	/**
	 * @var string
	 */
	protected $route;

	/**
	 * @var array
	 */
	protected $segments = array();

	/**
	 * @var string
	 */
	protected $controllerDir;

	/**
	 * @var string
	 */
	protected $controllerClass;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var array
	 */
	protected $params = array();

	/**
	 * @var string
	 */
	protected $extension;

	/**
	 * @var bool
	 */
	protected $isPartial = false;

	/**
	 * @var bool
	 */
	protected $isRestful = false;

	protected $httpVerb;

	protected $headers = array();

	/**
	 * @var string
	 */
	protected $viewDir;

	function __construct($route, $headers = array(), $http_verb = null) {
		$this->setHeaders($headers);
		if (null !== $http_verb) {
			$this->httpVerb = strtoupper($http_verb);
		}
		$this->setRoute($route);
	}

	function setHeaders($headers) {
		$this->headers = $headers;
		if (!empty($headers['Accept'])) {
			if (strpos($headers['Accept'], 'application/json') !== false) {
				$this->extension = 'json';
			} elseif ($headers['Accept'] == 'application/xml') {
				$this->extension = 'xml';
			}
		}
		if (!empty($headers['X-HTTP-Method'])) {
			$this->httpVerb = strtoupper($headers['X-HTTP-Method']);
		}
	}

	function setRoute($route) {
		$this->route = $route;

		$route = str_replace('\\', '/', $route);
		$route = trim($route, '/');
		$parts = explode('?', $route, 2);
		$route = array_shift($parts);

		if (is_array($route)) {
			$this->route = implode('/', $route);
			$segments = $route;
		} else {
			$this->route = $route;
			if ($route === '') {
				$segments = array();
			} else {
				$segments = explode('/', $route);
			}
		}

		$last = array_pop($segments);
		if (null !== $last) {
			$file_parts = explode('.', $last);
			if (count($file_parts) > 1) {
				$this->extension = array_pop($file_parts);
			}
			$segments[] = implode('.', $file_parts);
		}

		while (in_array(@$segments[0], array('partial', 'rest'))){
			if (@$segments[0] == 'partial') {
				$this->isPartial = true;
				array_shift($segments);
			} elseif (@$segments[0] == 'rest') {
				$this->isRestful = true;
				array_shift($segments);
			}
		}

		$this->segments = $segments;

		// directory where controllers are found
		$c_dir = CONTROLLERS_DIR;
		$view_prefix = '';
		$view_dir = '';
		$found = false;

		foreach ($segments as $key => &$segment) {
			$c_class = str_replace(array('_', '-'), ' ', $segment);
			$c_class = ucwords($c_class);
			$c_class = str_replace(' ', '', $c_class) . 'Controller';
			$c_class_file = $c_dir . $c_class . '.php';

			// check if file exists
			if (is_file($c_class_file)) {
				unset($segments[$key]);
				$view_dir = strtolower($segment);
				$this->controllerClass = $c_class;
				$this->controllerDir = $c_dir;
				$found = true;
				break;
			}

			// check if the segment matches directory name
			$t_dir = $c_dir . $segment . '/';
			if (is_dir($t_dir)) {
				unset($segments[$key]);
				$c_dir = $t_dir;
				$view_prefix .= $segment . '/';
				continue;
			}
			break;
		}

		if (!$found) {
			//fallback check if default index exists in directory
			$alternate_c_class = ucwords(DEFAULT_CONTROLLER) . 'Controller';
			$alternate_c_class_file = $c_dir . $alternate_c_class . '.php';

			if (is_file($alternate_c_class_file)) {
				$this->controllerClass = $alternate_c_class;
				$this->controllerDir = $c_dir;
				$found = true;
			}
			if (!$found) {
				$this->controllerClass = '';
				$this->controllerDir = '';
				return;
			}
		}

		$this->viewDir = $view_dir ? $view_prefix . $view_dir . '/' : $view_prefix;
		$this->action = array_shift($segments);
		$this->params = $segments;
	}

	/**
	 * @param string $route
	 * @param array $headers
	 * @param string $http_verb
	 */
	static function load($route, $headers = array(), $http_verb = null) {
		$controller_route = new self($route, $headers, $http_verb);

		$controller = $controller_route->getController();

		if (null === $controller) {
			file_not_found($route);
		}

		$controller->doAction($controller_route->getAction(), $controller_route->getParams());
	}

	/**
	 * @return string
	 */
	function getRoute() {
		$partial = $this->isPartial ? 'partial/' : '';
		$rest = $this->isRestful ? 'rest/' : '';
		$segments = implode('/', $this->segments);
		$extension = ($this->extension ? '.' . $this->extension : '');
		return $rest . $partial . $segments . $extension;
	}

	/**
	 * @return array
	 */
	function getSegments() {
		return $this->segments;
	}

	/**
	 * @return string
	 */
	function getExtension() {
		return $this->extension;
	}

	/**
	 * @return string
	 */
	function getAction() {
		return $this->action;
	}

	/**
	 * @return array
	 */
	function getParams() {
		return $this->params;
	}

	/**
	 * @param bool $bool
	 */
	function setPartial($bool) {
		$this->isPartial = (bool) $bool;
	}

	/**
	 * @return bool
	 */
	function isPartial() {
		return $this->isPartial;
	}

	/**
	 * @param bool $bool
	 */
	function setRestful($bool) {
		$this->isRestful = (bool) $bool;
	}

	/**
	 * @return bool
	 */
	function isRestful() {
		return $this->isRestful;
	}

	function getHttpVerb() {
		return $this->httpVerb;
	}

	/**
	 * @return string
	 */
	function getControllerDir() {
		return $this->controllerDir;
	}

	/**
	 * @return string
	 */
	function getControllerClass() {
		return $this->controllerClass;
	}

	/**
	 * @return string
	 */
	function getViewDir() {
		return $this->viewDir;
	}

	/**
	 * @return Controller
	 */
	function getController() {
		if (null === $this->controllerDir || null === $this->controllerClass) {
			return null;
		}

		require_once $this->controllerDir . '/' . $this->controllerClass . '.php';
		$instance = new $this->controllerClass($this);

		// Restore Flash params
		$instance->setParams(array_merge_recursive(get_clean_persistant_values(), $instance->getParams()));

		return $instance;
	}

}