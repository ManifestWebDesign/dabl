<?php

require_once LIBRARIES_DIR . 'dabl/database/propel/platform/Platform.php';

abstract class BaseGenerator {

	/**
	 * @var array
	 */
	private $options = array(
		//convert table and column names to title case
		'title_case' => true,

		//enforce an upper case first letter of classes
		'cap_model_names' => true,

		//enforce an upper case first letter of get and set methods
		'cap_method_names' => true,

		//prepend this to class name
		'model_prefix' => '',

		//append this to class name
		'model_suffix' => '',

		//target directory for generated table classes
		'model_path' => null,

		//target directory for generated base table classes
		'base_model_path' => null,

		//set to true to generate views
		'view_path' => null,

		//directory to save controller files in
		'controller_path' => null
	);

	/**
	 * @var string
	 */
	private $connectionName;

	/**
	 * @var DOMDocument
	 */
	private $dbSchema;

	/**
	 *
	 * @var Database
	 */
	private $database;

	/**
	 * @var string
	 */
	protected $baseModelTemplate = '/templates/base_model.php';

	/**
	 * @var string
	 */
	protected $modelTemplate = '/templates/model.php';

	/**
	 * Constructor function
	 * @param $db_name string
	 * @param $schema DOMDocument
	 */
	function __construct($connection_name) {
		$this->setConnectionName($connection_name);
		$conn = DBManager::getConnection($connection_name);
		$this->database = $conn->getDatabaseSchema();

		$dom = new DOMDocument('1.0', 'utf-8');
		$this->database->appendXml($dom);
		$dom->formatOutput = true;
		$this->setSchema($dom);
	}

	/**
	 * @param DOMDocument $schema
	 */
	function setSchema(DOMDocument $schema) {
		$this->dbSchema = $schema;
	}

	/**
	 * @return DOMDocument
	 */
	function getSchema() {
		return $this->dbSchema;
	}

	function setOptions($options) {
		$this->options = array_merge($this->options, $options);
	}

	function getOptions() {
		return $this->options;
	}

	/**
	 * Returns an array of all the table names in the XML schema
	 * @return array
	 */
	function getTableNames() {
		$table_names = array();
		foreach ($this->database->getTables() as $table)
			$table_names[] = $table->getName();
		return $table_names;
	}

	/**
	 * Returns an array of Column objects for a given table in the XML schema
	 * @return Column[]
	 */
	function getColumns($table_name) {
		$table = $this->database->getTable($table_name);
		return $table->getColumns();
	}

	/**
	 * @param string $table_name
	 * @return array
	 */
	function getForeignKeysFromTable($table_name) {
		return $this->database->getTable($table_name)->getForeignKeys();
	}

	/**
	 * @param string $table_name
	 * @return array
	 */
	function getForeignKeysToTable($table_name) {
		return $this->database->getTable($table_name)->getReferrers();
	}

	/**
	 * @return string
	 */
	function getDBName() {
		return DBManager::getConnection($this->getConnectionName())->getDBName();
	}

	/**
	 * @param string $conn_name
	 */
	function setConnectionName($conn_name) {
		$this->connectionName = $conn_name;
	}

	/**
	 * @return string
	 */
	function getConnectionName() {
		return $this->connectionName;
	}

	/**
	 * @return array
	 */
	function getTemplateParams() {
		return array();
	}

	/**
	 * @param string $template Path to file relative to dirname(__FILE__) with leading /
	 * @return string
	 */
	function renderTemplate($table_name, $template, $extraparams = array()) {
		$params = $this->getTemplateParams($table_name);
		$params = array_merge($params, $extraparams);
		foreach ($params as $key => &$value)
			$$key = $value;

		ob_start();
		require dirname(__FILE__) . $template;
		return ob_get_clean();
	}

	/**
	 * @return string Path to base model template file relative to dirname(__FILE__) with leading /
	 */
	function getBaseModelTemplate() {
		return $this->baseModelTemplate;
	}

	/**
	 * @return string Path to model template file relative to dirname(__FILE__) with leading /
	 */
	function getModelTemplate() {
		return $this->modelTemplate;
	}

	/**
	 * @return array Paths to view template files relative to dirname(__FILE__) with leading /
	 */
	function getViewTemplates() {
		return $this->viewTemplates;
	}

	/**
	 * @return string Path to controller template file relative to dirname(__FILE__) with leading /
	 */
	function getControllerTemplate() {
		return $this->controllerTemplate;
	}

	/**
	 * Converts a table name to a class name using the given options.  Often used
	 * to add class prefixes and/or suffixes, or to convert a class_name to a title case
	 * ClassName
	 * @param string $table_name
	 * @return string
	 */
	function getModelName($table_name) {
		$options = $this->options;
		$class_name = StringFormat::removeAccents($table_name);
		if (@$options['title_case'])
			$class_name = StringFormat::titleCase($class_name);
		if (@$options['cap_model_names'])
			$class_name = ucfirst($class_name);
		if (@$options['model_prefix'])
			$class_name = $options['model_prefix'] . $class_name;
		if (@$options['model_suffix'])
			$class_name = $class_name . $options['model_suffix'];
		return $class_name;
	}

	/**
	 * @param string $table_name
	 * @return string
	 */
	function getViewDirName($table_name) {
		return StringFormat::pluralURL($table_name);
	}

	abstract function getControllerName($table_name);

	abstract function getControllerFileName($table_name);

	/**
	 * Generates a string with the contents of the Base class
	 * @param string $table_name
	 * @param string $class_name
	 * @param array $options
	 * @return string
	 */
	function getBaseModel($table_name) {
		$class_name = $this->getModelName($table_name);
		$options = $this->options;
		//Gather all the information about the table's columns from the database
		$PK = null;
		$PKs = array();
		$fields = $this->getColumns($table_name);
		$conn = DBManager::getConnection($this->getConnectionName());
		$auto_increment = false;

		foreach ($fields as $field) {
			if ($field->isPrimaryKey()) {
				$PKs[] = $field->getName();
				if ($field->isAutoIncrement()) {
					$auto_increment = true;
				}
			}
		}

		if (count($PKs) == 1) {
			$PK = $PKs[0];
		} else {
			$auto_increment = false;
		}

		ob_start();
		require dirname(__FILE__) . $this->getBaseModelTemplate();
		return ob_get_clean();
	}

	/**
	 * Generates a string with the contents of the stub class
	 * for the table, which is used for extending the Base class.
	 * @param string $table_name
	 * @param string $class_name
	 * @return string
	 */
	function getModel($table_name) {
		$class_name = $this->getModelName($table_name);
		$options = $this->options;
		ob_start();
		require dirname(__FILE__) . $this->getModelTemplate();
		return ob_get_clean();
	}

	/**
	 * Returns an associative array of file contents for
	 * each view generated by this class
	 * @param string $table_name
	 * @return array
	 */
	function getViews($table_name) {
		$rendered_views = array();
		foreach ($this->getViewTemplates() as $file_name => $view_template)
			$rendered_views[$file_name] = $this->renderTemplate($table_name, $view_template);
		foreach ($this->database->getTable($table_name)->getForeignKeys() as $fk) {
			$rendered_views[$fk->getForeignTableName() . '.php'] = $this->renderTemplate($table_name, '/templates/fkgrid.php', array('foreign_key' => $fk));
		}
		return $rendered_views;
	}

	/**
	 * Generates a String with Controller class for MVC
	 * @param String $table_name
	 * @return String
	 */
	function getController($table_name) {
		return $this->renderTemplate($table_name, $this->getControllerTemplate());
	}

	/**
	 * Generates Table classes
	 * @return void
	 */
	function generateModels($table_names = false) {
		if ($table_names === false)
			$table_names = $this->getTableNames();
		elseif (empty($table_names))
			return;

		$options = $this->options;

		if (!is_dir($options['model_path']) && !mkdir($options['model_path']))
			die('The directory ' . $options['model_path'] . ' does not exist.');

		if (!is_dir($options['base_model_path']) && !mkdir($options['base_model_path']))
			die('The directory ' . $options['base_model_path'] . ' does not exist.');

		//Write php files for classes
		foreach ($table_names as &$table_name) {
			$class_name = $this->getModelName($table_name);
			$lower_case_table = strtolower($table_name);

			$base_class = $this->getBaseModel($table_name);
			$base_file = "base$class_name.php";
			$base_file = $options['base_model_path'] . $base_file;

			if (!file_exists($base_file) || file_get_contents($base_file) != $base_class)
				file_put_contents($base_file, $base_class);

			$file = $options['model_path'] . $class_name . ".php";

			if (!file_exists($file)) {
				$class = $this->getModel($table_name);
				file_put_contents($file, $class);
				unset($class);
			}
		}
		//save xml to file
		file_put_contents($options['model_path'] . $this->getConnectionName() . "-schema.xml", $this->getSchema()->saveXML());
	}

	/**
	 * Generate views
	 */
	function generateViews($table_names = false) {
		if ($table_names === false)
			$table_names = $this->getTableNames();

		$options = $this->getOptions();

		foreach ((array) $table_names as $table_name) {
			$lower_case_table = strtolower($table_name);

			if (!is_dir($options['view_path']))
				throw new Exception($options['view_path'] . " is not a directory.");

			$target_dir = $options['view_path'] . $this->getViewDirName($table_name) . '/';

			if (!is_dir($target_dir))
				mkdir($target_dir, 0755);

			foreach ($this->getViews($table_name) as $file_name => $contents) {
				$file_name = $target_dir . $file_name;

				if (!file_exists($file_name))
					file_put_contents($file_name, $contents);
			}
		}
	}

	/**
	 * Generate controllers
	 */
	function generateControllers($table_names = false) {
		if ($table_names === false)
			$table_names = $this->getTableNames();
		elseif (empty($table_names))
			return;

		$options = $this->options;

		foreach ($table_names as &$table_name) {
			$target_dir = $options['controller_path'];
			$lower_case_table = strtolower($table_name);

			if (!is_dir($target_dir))
				throw new Exception("$target_dir is not a directory.");

			$file = $this->getControllerFileName($table_name);
			$file = $target_dir . $file;
			if (!file_exists($file)) {
				$controller = $this->getController($table_name);
				file_put_contents($file, $controller);
			}
		}
	}

}
