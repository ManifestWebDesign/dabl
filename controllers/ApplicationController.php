<?php

abstract class ApplicationController extends Controller {

	function __construct() {
		$this['title'] = 'Site Title';

		$this['actions'] = array(
			'Home' => site_url()
		);

		$current_controller = str_replace('Controller', '', get_class($this));

		if ('Index' == $current_controller){
			$this['current_page'] = 'Home';
		} else {
			$this['current_page'] = StringFormat::titleCase($current_controller, ' ');
		}

		foreach (glob(CONTROLLERS_DIR . '*.php') as $controller_file) {
			$controller = str_replace('Controller.php', '', basename($controller_file));
			if ($controller == 'Application' || $controller == 'Index') {
				continue;
			}
			$this['actions'][StringFormat::titleCase($controller, ' ')] = site_url(StringFormat::url($controller));
		}
	}

	public function doAction($action_name = null, $params = array()) {
		if($this->outputFormat != 'html') {
			unset($this['title'], $this['current_page'], $this['actions']);
		}

		return parent::doAction($action_name, $params);
	}

}