<?php

/**
 * Description of View
 *
 * @author Dan
 */
class View {

	protected $viewFile;
	protected $params = array();

	public function __toString() {
		return $this->render(true);
	}

	public function __construct($view_file = null, $params = array()) {
		$this->setFile($view_file);
		$this->setParams($params);
	}

	/**
	 * @param type $view_file
	 * @param type $params
	 * @param type $return_output
	 * @return BaseView
	 */
	public static function create($view_file = null, $params = array()) {
		return new self($view_file, $params);
	}

	public static function load($view = null, $params = array(), $return_output = false) {
		$view = self::create($view, $params);
		if ($return_output) {
			return $view;
		}
		$view->render();
	}

	public function setFile($view_file = null) {

		// normalize slashes
		$view_file = str_replace('\\', '/', $view_file);
		$view_file = trim($view_file, '/');

		// indexes
		if (!is_file(VIEWS_DIR . $view_file . '.php') && is_dir(VIEWS_DIR . $view_file)) {
			$view_file = $view_file . '/index';
		}

		// php extension
		$view_file = VIEWS_DIR . "{$view_file}.php";

		// raise error if file doesn't exist
		if (!is_file($view_file)) {
			file_not_found($view_file);
		}

		$this->viewFile = $view_file;

		return $this;
	}

	public function getFile() {
		return $this->viewFile;
	}

	public function setParams($params = null) {
		$this->params = $params;
		return $this;
	}

	public function getParams() {
		return $this->params;
	}

	public function render($return_output = false) {
		$params = &$this->params;

		if ($return_output) {
			ob_start();
		}

		// $params['my_var'] = $my_var
		foreach ($params as $_var => &$_value) {
			$$_var = &$_value;
		}

		require $this->viewFile;

		if ($return_output) {
			return ob_get_clean();
		}
	}

}