<?php

abstract class ApplicationController extends BaseController {
	function __construct(){
		$this['title'] = "Site Title";

		$this['actions'] = array();
		foreach(glob(ROOT.'controllers/*.php') as $controller_file){
			$action = str_replace(array('Controller','.php'), '', basename($controller_file));
			if($action == 'Application' || $action == 'Index' || $action == 'Generator') continue;
			$action = preg_replace('/([a-z])([A-Z])/', '$1-$2', $action);
			$this['actions'][ucwords(str_replace('-', ' ', $action))] = site_url(strtolower($action));
		}
	}
}