<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

class View {

	/**
	 * @var string
	 */
	protected $viewFile;

	/**
	 * @var array
	 */
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

	/**
	 * @param string $view
	 * @param array|ArrayObject $params
	 * @param boolean $return_output
	 * @return string|null
	 */
	public static function load($view = null, $params = array(), $return_output = false) {
		return self::create($view, $params)->render($return_output);
	}

	/**
	 * @param string $view_file
	 * @return View
	 */
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

	/**
	 * @return string
	 */
	public function getFile() {
		return $this->viewFile;
	}

	/**
	 * @param array $params
	 * @return View
	 */
	public function setParams($params = null) {
		$this->params = $params;
		return $this;
	}

	/**
	 * @return array|ArrayObject
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @param boolean $return_output
	 * @return string|null
	 */
	public function render($return_output = false) {
		$params = &$this->params;

		if ($return_output) {
			ob_start();
		}

		// $params['my_var'] shows up as $my_var
		foreach ($params as $_var => &$_value) {
			$$_var = &$_value;
		}

		require $this->viewFile;

		if ($return_output) {
			return ob_get_clean();
		}
	}

}