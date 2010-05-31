<?php

abstract class BaseGenerator{

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @var String
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
	 * Constructor function
	 * @param $db_name String
	 * @param $schema DOMDocument
	 */
	function __construct($connection_name){
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
			'model_path' => ROOT."models/",

			//target directory for generated base table classes
			'base_model_path' => ROOT."models/base/",

			//set to true to generate views
			'view_path' => ROOT."views/",

			//directory to save controller files in
			'controller_path' => ROOT."controllers/",
		);
	}

	/**
	 * @param DOMDocument $schema
	 */
	function setSchema(DOMDocument $schema){
		$this->dbSchema = $schema;
	}

	/**
	 * @return DOMDocument
	 */
	function getSchema(){
		return $this->dbSchema;
	}

	function setOptions($options){
		$this->options = array_merge($this->options, $options);
	}

	function getOptions(){
		return $this->options;
	}

	/**
	 * Returns an array of all the table names in the XML schema
	 * @return array
	 */
	function getTableNames(){
		$table_names = array();
		foreach($this->database->getTables() as $table)
			$table_names[] = $table->getName();
		return $table_names;
	}

	/**
	 * Returns an array of Column objects for a given table in the XML schema
	 * @return Column[]
	 */
	function getColumns($table_name){
		$table = $this->database->getTable($table_name);
		return $table->getColumns();
	}

	/**
	 * @param string $table_name
	 * @return array
	 */
	function getForeignKeysFromTable($table_name){
		$references = array();

		$database_node = $this->getSchema()->getElementsByTagName('database')->item(0);
		foreach($database_node->getElementsByTagName('table') as $table_node){
			if($table_node->getAttribute("name")!==$table_name)continue;
			foreach($table_node->getElementsByTagName('foreign-key') as $fk_node){
				foreach($fk_node->getElementsByTagName('reference') as $reference_node){
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
	function getForeignKeysToTable($table_name){
		$references = array();

		$database_node = $this->getSchema()->getElementsByTagName('database')->item(0);
		foreach($database_node->getElementsByTagName('table') as $table_node){
			foreach($table_node->getElementsByTagName('foreign-key') as $fk_node){
				foreach($fk_node->getElementsByTagName('reference') as $reference_node){
					if($fk_node->getAttribute('foreignTable')!=$table_name)continue;
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
	function getDBName(){
		return DBManager::getConnection($this->getConnectionName())->getDBName();
	}

	/**
	 * @param String $conn_name
	 */
	function setConnectionName($conn_name){
		$this->connectionName = $conn_name;
	}

	/**
	 * @return string
	 */
	function getConnectionName(){
		return $this->connectionName;
	}

	/**
	 * Generates a String with the contents of the Base class
	 * @param String $table_name
	 * @param String $class_name
	 * @param array $options
	 * @return string
	 */
	function getBaseModel($table_name){
		$class_name = $this->getModelName($table_name);
		$options = $this->options;
		//Gather all the information about the table's columns from the database
		$PK = null;
		$PKs = array();
		$fields = $this->getColumns($table_name);
		$conn = DBManager::getConnection($this->getConnectionName());
		
		foreach($fields as $field){
			if($field->isPrimaryKey()) $PKs[] = $field->getName();
		}
		
		if(count($PKs)==1)
			$PK = $PKs[0];

        ob_start();
        require dirname(__FILE__) . '/dabl/base_model.php';
        return ob_get_clean();
	}

	/**
	 * Generates a String with the contents of the stub class
	 * for the table, which is used for extending the Base class.
	 * @param String $table_name
	 * @param String $class_name
	 * @return string
	 */
	function getModel($table_name){
		$class_name = $this->getModelName($table_name);
		$options = $this->options;
        ob_start();
        require dirname(__FILE__) . '/dabl/model.php';
        return ob_get_clean();
	}

	abstract function getViews($tableName);

	abstract function getController($tableName);

	abstract function getControllerName($tableName);

	abstract function getControllerFileName($tableName);

	/**
	 * Generates Table classes
	 * @return void
	 */
	function generateModels($tableNames = false){
		if($tableNames===false)
			$tableNames = $this->getTableNames();
		elseif(empty($tableNames))
			return;

		$options = $this->options;

		if(!is_dir($options['model_path']) && !mkdir($options['model_path']))
			die('The directory '.$options['model_path'].' does not exist.');

		if(!is_dir($options['base_model_path']) && !mkdir($options['base_model_path']))
			die('The directory '.$options['base_model_path'].' does not exist.');

		//Write php files for classes
		foreach($tableNames as $tableName){
			$className = $this->getModelName($tableName);
			$lower_case_table = strtolower($tableName);

			$baseClass = $this->getBaseModel($tableName);
			$baseFile = "base$className.php";
			$baseFile = $options['base_model_path'].$baseFile;

			if(!file_exists($baseFile) || file_get_contents($baseFile)!=$baseClass)
				file_put_contents($baseFile, $baseClass);

			$file = $options['model_path'].$className.".php";

			if (!file_exists($file)){
				$class = $this->getModel($tableName);
				file_put_contents($file, $class);
			}
		}
		//save xml to file
		file_put_contents($options['model_path'].$this->getConnectionName()."-schema.xml", $this->getSchema()->saveXML());
	}

	/**
	 * Generate views
	 */
	function generateViews($tableNames = false){
		if($tableNames===false)
			$tableNames = $this->getTableNames();

		$options = $this->getOptions();

		foreach((array)$tableNames as $tableName){
			$lower_case_table = strtolower($tableName);

			if(!is_dir($options['view_path']))
				throw new Exception($options['view_path']." is not a directory.");

			$target_dir = $options['view_path'].$this->getViewDirName($tableName).DIRECTORY_SEPARATOR;

			if(!is_dir($target_dir))
				mkdir($target_dir, 0755);

			foreach($this->getViews($tableName) as $file_name => $contents){
				$file_name = $target_dir.$file_name;

				if(!file_exists($file_name))
					file_put_contents($file_name, $contents);
			}
		}
	}

	/**
	 * Generate controllers
	 */
	function generateControllers($tableNames = false){
		if($tableNames===false)
			$tableNames = $this->getTableNames();
		elseif(empty($tableNames))
			return;

		$options = $this->options;

		foreach($tableNames as $tableName){
			$target_dir = $options['controller_path'];
			$lower_case_table = strtolower($tableName);

			if(!is_dir($target_dir))
				throw new Exception("$target_dir is not a directory.");

			$file = $this->getControllerFileName($tableName);
			$file = $target_dir.$file;
			if(!file_exists($file)){
				$controller = $this->getController($tableName);
				file_put_contents($file, $controller);
			}
		}
	}

	/**
	 * Converts a table name to a class name using the given options.  Often used
	 * to add class prefixes and/or suffixes, or to convert a class_name to a title case
	 * ClassName
	 * @param String $tableName
	 * @return string
	 */
	function getModelName($tableName){
		$options = $this->options;
		$className = $tableName;
		if(@$options['title_case'])
			$className = self::titleCase($className);
		if($options['cap_model_names'])
			$className = ucfirst($className);
		if(@$options['model_prefix'])
			$className = $options['model_prefix'].$className;
		if(@$options['model_suffix'])
			$className = $className.$options['model_suffix'];
		return $className;
	}

	/**
	 * @param string $tableName
	 * @return string
	 */
	function getViewDirName($tableName){
		return self::getPluralName($tableName);
	}

	static function getPluralName($tableName) {
		return strtolower(join('_',self::getWords(self::pluralize($tableName))));
	}

	static function getSingularName($tableName) {
		return strtolower(join('_',self::getWords($tableName)));
	}

	/**
	 * Converts a given string to title case
	 * @param String $string
	 * @return string
	 */
	static function titleCase($string){
		$string = str_replace('_', ' ', $string);
		$string = ucwords($string);
		$string = str_replace(' ', '', $string);
		return $string;
	}

	static function spaceTitleCase($string){
		return join(' ', self::getWords($string));
	}

	static function getWords($string) {
		return explode('_', preg_replace('/([a-z])([A-Z])/', '$1_$2', self::titleCase($string)));
	}

	/**
	 * Returns the plural version of the given word.  If the plural version is
	 * the same, then this method will simply add an 's' to the end of
	 * the word.
	 * @param String $string
	 * @return string
	 */
	static function pluralize( $string ){
		$plural = array(
			array( '/(quiz)$/i',                "$1zes"   ),
			array( '/^(ox)$/i',                 "$1en"    ),
			array( '/([m|l])ouse$/i',           "$1ice"   ),
			array( '/(matr|vert|ind)ix|ex$/i',  "$1ices"  ),
			array( '/(x|ch|ss|sh)$/i',          "$1es"    ),
			array( '/([^aeiouy]|qu)y$/i',       "$1ies"   ),
			array( '/([^aeiouy]|qu)ies$/i',     "$1y"     ),
			array( '/(hive|move)$/i',           "$1s"     ),
			array( '/(?:([^f])fe|([lr])f)$/i',  "$1$2ves" ),
			array( '/sis$/i',                   "ses"     ),
			array( '/([ti])um$/i',              "$1a"     ),
			array( '/(buffal|tomat)o$/i',       "$1oes"   ),
			array( '/(bu)s$/i',                 "$1ses"   ),
			array( '/(alias|status|campus)$/i', "$1es"    ),
			array( '/(octop|cact|vir)us$/i',    "$1i"     ),
			array( '/(ax|test)is$/i',           "$1es"    ),
			array( '/^(m|wom)an$/i',			"$1en"    ),
			array( '/(child)$/i',				"$1ren"   ),
			array( '/(p)erson$/i',				"$1eople" ),
			array( '/s$/i',                     "s"       ),
			array( '/$/',                       "s"       )
		);

		// save some time in the case that singular and plural are the same
		//if ( in_array( strtolower( $string ), $uncountable ) )return $string;

		// check for matches using regular expressions
		foreach ( $plural as $pattern ){
			if ( preg_match( $pattern[0], $string ) )
				return preg_replace( $pattern[0], $pattern[1], $string );
		}
		return $string.'s';
	}

}
