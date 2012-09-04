<?php

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
	protected $partial = false;

	/**
	 * @var string
	 */
	protected $viewDir;

	function __construct($route) {
		$this->setRoute($route);
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
			if (count($file_parts) > 1)
				$this->extension = array_pop($file_parts);
			$segments[] = implode('.', $file_parts);
		}

		if (@$segments[0] == 'partial') {
			$this->partial = true;
			array_shift($segments);
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

	static function load($route) {
		$controller_route = new self($route);

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
		$partial = $this->partial ? 'partial/' : '';
		$segments = implode('/', $this->segments);
		$extension = ($this->extension ? '.' . $this->extension : '');
		return $partial . $segments . $extension;
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
		$this->partial = (bool) $bool;
	}

	/**
	 * @return bool
	 */
	function isPartial() {
		return $this->partial;
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
	 * @return Controller
	 */
	function getController() {
		if (null === $this->controllerDir || null === $this->controllerClass) {
			return null;
		}

		require_once $this->controllerDir . '/' . $this->controllerClass . '.php';
		$instance = new $this->controllerClass;

		if (!$instance->viewDir) {
			$instance->viewDir = $this->viewDir;
		}

		if ($this->partial) {
			$instance->renderPartial = true;
		}

		if (null !== $this->extension) {
			$instance->outputFormat = $this->extension;
		}

		// Restore Flash params
		$instance->setParams(array_merge_recursive(get_clean_persistant_values(), $instance->getParams()));
		return $instance;
	}

}