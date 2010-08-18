<?php

abstract class BaseGenerator {

	/**
	 * @var array
	 */
	private $options;

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
	protected $baseModelTemplate = '/dabl/base_model.php';

	/**
	 * @var string
	 */
	protected $modelTemplate = '/dabl/model.php';

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

		$this->options = array(
			//convert table and column names to title case
			'title_case' => true,
			//enforce an upper case first letter of classes
			'cap_model_names' => true,
			//enforce an upper case first letter of get and set methods
			'cap_method_names' => true,
			//add some logic to the setter methods to not allow column values to be null if the column cannot be null
			'protect_not_null' => true,
			//prepend this to class name
			'model_prefix' => '',
			//append this to class name
			'model_suffix' => '',
			//target directory for generated table classes
			'model_path' => ROOT . "models/",
			//target directory for generated base table classes
			'base_model_path' => ROOT . "models/base/",
			//set to true to generate views
			'view_path' => ROOT . "views/",
			//directory to save controller files in
			'controller_path' => ROOT . "controllers/",
		);
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
		$references = array();

		$database_node = $this->getSchema()->getElementsByTagName('database')->item(0);
		foreach ($database_node->getElementsByTagName('table') as $table_node) {
			if ($table_node->getAttribute("name") !== $table_name
				)continue;
			foreach ($table_node->getElementsByTagName('foreign-key') as $fk_node) {
				foreach ($fk_node->getElementsByTagName('reference') as $reference_node) {
					$references[] = array(
						'to_table' => $fk_node->getAttribute('foreignTable'),
						'to_column' => $reference_node->getAttribute('foreign'),
						'from_column' => $reference_node->getAttribute('local'),
					);
				}
			}
		}
		return $references;
	}

	/**
	 * @param string $table_name
	 * @return array
	 */
	function getForeignKeysToTable($table_name) {
		$references = array();

		$database_node = $this->getSchema()->getElementsByTagName('database')->item(0);
		foreach ($database_node->getElementsByTagName('table') as $table_node) {
			foreach ($table_node->getElementsByTagName('foreign-key') as $fk_node) {
				foreach ($fk_node->getElementsByTagName('reference') as $reference_node) {
					if ($fk_node->getAttribute('foreignTable') != $table_name
						)continue;
					$references[] = array(
						'from_table' => $table_node->getAttribute('name'),
						'to_column' => $reference_node->getAttribute('foreign'),
						'from_column' => $reference_node->getAttribute('local'),
					);
				}
			}
		}
		return $references;
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
	function getTemplateParams(){
		return array();
	}

	/**
	 * @param string $template Path to file relative to dirname(__FILE__) with leading /
	 * @return string
	 */
	function renderTemplate($table_name, $template){
		$params = $this->getTemplateParams($table_name);
		foreach ($params as $key => &$value)$$key = $value;

		ob_start();
		require dirname(__FILE__) . $template;
		return ob_get_clean();
	}

	/**
	 * @return string Path to base model template file relative to dirname(__FILE__) with leading /
	 */
	function getBaseModelTemplate(){
		return $this->baseModelTemplate;
	}

	/**
	 * @return string Path to model template file relative to dirname(__FILE__) with leading /
	 */
	function getModelTemplate(){
		return $this->modelTemplate;
	}

	/**
	 * @return array Paths to view template files relative to dirname(__FILE__) with leading /
	 */
	function getViewTemplates(){
		return $this->viewTemplates;
	}

	/**
	 * @return string Path to controller template file relative to dirname(__FILE__) with leading /
	 */
	function getControllerTemplate(){
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
		$class_name = self::removeAccents($table_name);
		if (@$options['title_case'])
			$class_name = self::noSpaceTitleCase($class_name);
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
		return self::getPluralURL($table_name);
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

		foreach ($fields as $field)
			if ($field->isPrimaryKey())
				$PKs[] = $field->getName();

		$auto_increment = false;
		if (count($PKs) == 1) {
			$PK = $PKs[0];
			if ($fields[0]->isAutoIncrement())
				$auto_increment = true;
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
		foreach($this->getViewTemplates() as $file_name => $view_template)
			$rendered_views[$file_name] = $this->renderTemplate($table_name, $view_template);
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

			$target_dir = $options['view_path'] . $this->getViewDirName($table_name) . DIRECTORY_SEPARATOR;

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

	/**
	 * Replaces accent characters with non-accent characters
	 * @param string $str
	 * @return string
	 */
	static function removeAccents($str) {
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		return str_replace($a, $b, $str);
	}

	/**
	 * Returns pluralized version of string, with words separated by underscores.
	 * Intended for variable names.
	 * @param string $table_name
	 * @return string
	 */
	static function getPluralName($table_name) {
		return strtolower(join('_', self::getWords(self::pluralize(self::removeAccents($table_name)))));
	}

	/**
	 * Returns pluralized version of string, with words separated by dashes.
	 * @param string $table_name
	 * @return string
	 */
	static function getPluralURL($table_name) {
		return str_replace('_', '-', self::getPluralName(self::removeAccents($table_name)));
	}

	/**
	 * Returns non-pluralized version of string, with words separated by underscores.
	 * @param string $table_name
	 * @return string
	 */
	static function getSingularName($table_name) {
		return strtolower(join('_', self::getWords(self::removeAccents($table_name))));
	}

	/**
	 * Returns non-pluralized version of string, with words separated by dashes.
	 * @param string $table_name
	 * @return string
	 */
	static function getSingularURL($table_name) {
		return str_replace('_', '-', self::getPluralName(self::removeAccents($table_name)));
	}

	/**
	 * Converts a given string to title case
	 * @param string $string
	 * @return string
	 */
	static function noSpaceTitleCase($string) {
		return str_replace(' ', '', self::spaceTitleCase($string));
	}

	/**
	 * Returns a string with each word capitalized, seperated by spaces
	 * @param string $string
	 * @return string
	 */
	static function spaceTitleCase($string) {
		return ucwords(implode(' ', self::getWords($string)));
	}

	/**
	 * Returns array of the words in a string
	 * @param string $string
	 * @return array
	 */
	static function getWords($string) {
		return explode(' ', preg_replace('/([a-z])([A-Z])/', '$1 $2', str_replace(array("\n", '_', '-'), ' ', $string)));
	}

	/**
	 * Returns the plural version of the given word.  If the plural version is
	 * the same, then this method will simply add an 's' to the end of
	 * the word.
	 * @param string $string
	 * @return string
	 */
	static function pluralize($string) {
		$plural = array(
			array('/(quiz)$/i', "$1zes"),
			array('/^(ox)$/i', "$1en"),
			array('/([m|l])ouse$/i', "$1ice"),
			array('/(matr|vert|ind)ix|ex$/i', "$1ices"),
			array('/(x|ch|ss|sh)$/i', "$1es"),
			array('/([^aeiouy]|qu)y$/i', "$1ies"),
			array('/([^aeiouy]|qu)ies$/i', "$1y"),
			array('/(hive|move)$/i', "$1s"),
			array('/(?:([^f])fe|([lr])f)$/i', "$1$2ves"),
			array('/sis$/i', "ses"),
			array('/([ti])um$/i', "$1a"),
			array('/(buffal|tomat)o$/i', "$1oes"),
			array('/(bu)s$/i', "$1ses"),
			array('/(alias|status|campus)$/i', "$1es"),
			array('/(octop|cact|vir)us$/i', "$1i"),
			array('/(ax|test)is$/i', "$1es"),
			array('/^(m|wom)an$/i', "$1en"),
			array('/(child)$/i', "$1ren"),
			array('/(p)erson$/i', "$1eople"),
			array('/s$/i', "s"),
			array('/$/', "s")
		);

		// save some time in the case that singular and plural are the same
		//if ( in_array( strtolower( $string ), $uncountable ) )return $string;
		// check for matches using regular expressions
		foreach ($plural as &$pattern) {
			if (preg_match($pattern[0], $string))
				return preg_replace($pattern[0], $pattern[1], $string);
		}
		return $string . 's';
	}

}
