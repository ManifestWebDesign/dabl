<?php

abstract class BaseGenerator {

	/**
	 * @var array
	 */
	private $options = array(

		// enforce an upper case first letter of get and set methods
		'cap_method_names' => true,

		// prepend this to class name
		'model_prefix' => '',

		// append this to class name
		'model_suffix' => '',

		// target directory for generated table classes
		'model_path' => null,

		// target directory for generated base table classes
		'base_model_path' => null,

		'model_query_path' => null,

		'base_model_query_path' => null,

		// set to true to generate views
		'view_path' => null,

		// directory to save controller files in
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
	protected $baseModelTemplate = '/templates/base-model.php';

	/**
	 * @var string
	 */
	protected $baseModelQueryTemplate = '/templates/base-model-query.php';

	/**
	 * @var string
	 */
	protected $modelTemplate = '/templates/model.php';

	/**
	 * @var string
	 */
	protected $modelQueryTemplate = '/templates/model-query.php';

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
	 * @return array Column[]
	 */
	function getColumns($table_name) {
		$table = $this->database->getTable($table_name);
		return $table->getColumns();
	}

	/**
	 * @param type $table_name
	 * @return array Column[]
	 */
	function getPrimaryKeys($table_name) {
		$table = $this->database->getTable($table_name);
		return $table->getPrimaryKey();
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

	function getTemplateParams($table_name) {
		$class_name = $this->getModelName($table_name);
		$column_names = $PKs = array();
		$auto_increment = false;
		$columns = $this->getColumns($table_name);
		$pks = $this->getPrimaryKeys($table_name);
		$pk = null;

		foreach ($columns as &$column) {
			$column_names[] = $column->getName();
			if ($column->isPrimaryKey()) {
				$PKs[] = $column->getName();
				if ($column->isAutoIncrement()) {
					$auto_increment = true;
				}
			}
		}

		if (count($PKs) == 1) {
			$pk = $PKs[0];
		} else {
			$auto_increment = false;
		}

		return array(
			'auto_increment' => $auto_increment,
			'table_name' => $table_name,
			'controller_name' => $this->getControllerName($table_name),
			'model_name' => $class_name,
			'column_names' => $column_names,
			'plural' => StringFormat::pluralVariable($table_name),
			'plural_url' => StringFormat::pluralURL($table_name),
			'single' => StringFormat::variable($table_name),
			'single_url' => StringFormat::url($table_name),
			'pk' => $pk,
			'primary_keys' => $pks,
			'pk_method' => $pk ? StringFormat::classMethod('get' . StringFormat::titleCase($pk)) : null,
			'actions' => $this->getActions($table_name),
			'columns' => $columns
		);
	}

	/**
	 * @param string $template Path to file relative to dirname(__FILE__) with leading /
	 * @return string
	 */
	function renderTemplate($table_name, $template, $extra_params = array()) {
		$params = $this->getTemplateParams($table_name);
		$params = array_merge($params, $extra_params);
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
		$class_name = StringFormat::className($table_name);

		if (@$options['model_prefix']) {
			$class_name = $options['model_prefix'] . $class_name;
		}
		if (@$options['model_suffix']) {
			$class_name = $class_name . $options['model_suffix'];
		}
		return $class_name;
	}

	/**
	 * @param string $table_name
	 * @return string
	 */
	function getViewDirName($table_name) {
		return StringFormat::pluralURL($table_name);
	}

	/**
	 * @param string $table_name
	 * @return string
	 */
	function getControllerName($table_name) {
		$controller_name = StringFormat::plural($table_name);
		return StringFormat::className($controller_name) . 'Controller';
	}

	function getControllerFileName($table_name) {
		return $this->getControllerName($table_name) . '.php';
	}

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

	function getModelQuery($table_name) {
		return $this->renderTemplate($table_name, $this->modelQueryTemplate);
	}

	function getBaseModelQuery($table_name) {
		return $this->renderTemplate($table_name, $this->baseModelQueryTemplate);
	}

	/**
	 * Returns an associative array of file contents for
	 * each view generated by this class
	 * @param string $table_name
	 * @return array
	 */
	function getViews($table_name) {
		$rendered_views = array();

		foreach ($this->getViewTemplates() as $file_name => $view_template) {
			$rendered_views[$file_name] = $this->renderTemplate($table_name, $view_template);
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
	function generateModelQueries($table_names = false) {
		if ($table_names === false) {
			$table_names = $this->getTableNames();
		} elseif (empty($table_names)) {
			return;
		}

		$options = $this->options;

		if (!is_dir($options['base_model_query_path']) && !mkdir($options['base_model_query_path'])) {
			throw new Exception('The directory ' . $options['base_model_query_path'] . ' does not exist.');
		}

		if (!is_dir($options['model_query_path']) && !mkdir($options['model_query_path'])) {
			throw new Exception('The directory ' . $options['model_query_path'] . ' does not exist.');
		}

		//Write php files for classes
		foreach ($table_names as &$table_name) {
			$class_name = $this->getModelName($table_name);

			$base_query = $this->getBaseModelQuery($table_name);
			$base_query_file = "base{$class_name}Query.php";
			$base_query_file = $options['base_model_query_path'] . $base_query_file;

			if (!file_exists($base_query_file) || file_get_contents($base_query_file) != $base_query) {
				file_put_contents($base_query_file, $base_query);
			}

			$query_file = "{$class_name}Query.php";
			$query_file = $options['model_query_path'] . $query_file;
			if (!file_exists($query_file)) {
				$query = $this->getModelQuery($table_name);
				file_put_contents($query_file, $query);
			}
		}
	}

	/**
	 * Generates Table classes
	 * @return void
	 */
	function generateModels($table_names = false) {
		if ($table_names === false) {
			$table_names = $this->getTableNames();
		} elseif (empty($table_names)) {
			return;
		}

		$options = $this->options;

		if (!is_dir($options['model_path']) && !mkdir($options['model_path'])) {
			throw new Exception('The directory ' . $options['model_path'] . ' does not exist.');
		}

		if (!is_dir($options['base_model_path']) && !mkdir($options['base_model_path'])) {
			throw new Exception('The directory ' . $options['base_model_path'] . ' does not exist.');
		}

		//Write php files for classes
		foreach ($table_names as &$table_name) {
			$class_name = $this->getModelName($table_name);

			$base_class = $this->getBaseModel($table_name);
			$base_file = "base{$class_name}.php";
			$base_file = $options['base_model_path'] . $base_file;

			if (!file_exists($base_file) || file_get_contents($base_file) != $base_class) {
				file_put_contents($base_file, $base_class);
			}

			$file = $options['model_path'] . $class_name . ".php";
			if (!file_exists($file)) {
				$class = $this->getModel($table_name);
				file_put_contents($file, $class);
				unset($class);
			}
		}

		$sql = $this->database->getPlatform()->getAddTablesDDL($this->database);

		// save XML to file
		file_put_contents($options['model_path'] . $this->getConnectionName() . "-schema.xml", $this->getSchema()->saveXML());

		// save SQL to file
		file_put_contents($options['model_path'] . $this->getConnectionName() . "-schema.sql", $sql);
	}

	/**
	 * Generate views
	 */
	function generateViews($table_names = false) {
		if ($table_names === false) {
			$table_names = $this->getTableNames();
		}

		$options = $this->getOptions();

		foreach ((array) $table_names as $table_name) {
			$lower_case_table = strtolower($table_name);

			if (!is_dir($options['view_path'])) {
				throw new Exception($options['view_path'] . " is not a directory.");
			}

			$target_dir = $options['view_path'] . $this->getViewDirName($table_name) . '/';

			if (!is_dir($target_dir)) {
				mkdir($target_dir, 0755);
			}

			foreach ($this->getViews($table_name) as $file_name => $contents) {
				$file_name = $target_dir . $file_name;

				if (!file_exists($file_name)) {
					file_put_contents($file_name, $contents);
				}
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