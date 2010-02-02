<?php

/**
 * Description of GenerateController
 *
 */
class GeneratorController extends ApplicationController {

	function  __construct() {
		parent::__construct();
		$this->render_partial = true;

		//see DABLGenerator::construct() for default options
		$options = array(
			'title_case' => true,

			//target directory for generated base table classes
			'base_model_path' => ROOT."models/base/",

			//target directory for generated table classes
			'model_path' => ROOT."models/",

			//set to true to generate views
			'view_path' => ROOT."views/",

			//directory to save controller files in
			'controller_path' => ROOT."controllers/",
		);

		Module::import('ROOT:libraries:dabl:generators');

		$generators = array();
		foreach(DBManager::getConnectionNames() as $db_name){
			$generator = new DABLGenerator($db_name);
			$generator->setOptions($options);
			$generators[] = $generator;
		}
		$this->options = $options;
		$this->generators = $generators;
	}

	function index(){

	}

	function generate(){
		if(!$this->generators)
			die('Nothing to generate');
	}
}