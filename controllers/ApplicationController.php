<?php

abstract class ApplicationController extends BaseController {
	function __construct(){
		$this['title'] = "Site Title";

		$this['actions'] = array();
		foreach(glob(ROOT.'controllers/*.php') as $controller_file){
			$action = str_replace(array('Controller','.php'), '', basename($controller_file));
			if($action == 'Application' || $action == 'Index' || $action == 'Generator') continue;
			$action = preg_replace('/([a-z])([A-Z])/', '$1_$2', $action);
			$this['actions'][str_replace('_', ' ', ucwords($action))] = site_url(strtolower($action));
		}
	}
}