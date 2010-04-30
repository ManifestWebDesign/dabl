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

			//if attempting to set value of numeric column to empty string, convert it to a zero
			'empty_string_zero' => false,

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
	 * @return String
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
	 * @return String
	 */
	function getConnectionName(){
		return $this->connectionName;
	}

	/**
	 * Generates a String with the contents of the Base class
	 * @param String $tableName
	 * @param String $className
	 * @param array $options
	 * @return String
	 */
	function getBaseModel($tableName){
		$className = $this->getModelName($tableName);
		$options = $this->options;
		//Gather all the information about the table's columns from the database
		$PK = null;
		$numeric = array();
		$null = array();
		$PKs = array();
		$fields = $this->getColumns($tableName);
		foreach($fields as $field)
			if($field->isPrimaryKey()) $PKs[] = $field->getName();

		if(count($PKs)==1)
			$PK = $PKs[0];

		$class = "<?php
/**
 *	Created by Dan Blaisdell's Database->Object Mapper
 *		             Based on Propel
 *
 *		Do not alter base files, as they will be overwritten.
 *		To alter the objects, alter the extended clases in
 *		the 'tables' folder.
 *
 */

abstract class base$className extends BaseModel{
";

		$class .= '
	/**
	 * Name of the table
	 */
	protected static $_tableName = "'.$tableName.'";

	/**
	 * Array of all primary keys
	 */
	protected static $_primaryKeys = array(';
		if($PKs)
			foreach($PKs as $thePK){
	 		$class .= '
			"'.$thePK.'",';
		}
		$class .= '
	);

	/**
	 * Primary Key
	 */
	 protected static $_primaryKey = "'.$PK.'";

	/**
	 * Array of all column names
	 */
	protected static $_columnNames = array(';
		foreach($fields as $key=>$field){
			$class .= "
		'{$field->getName()}'";
			if($key!=(count($fields)-1)) $class .= ",";
		}
$class .= '
	);
';
		foreach($fields as $key=>$field){
			$default = $field->getDefaultValue() ? $field->getDefaultValue()->getValue() : null;
			$class .= '	protected $'.$field->getName();
			if($field->isNumericType() && $default !== NULL)
				$class .= ' = '.$default;
			elseif($default!==NULL && strtolower($default)!=='null')
				$class .= ' = "'.$default.'"';
			$class .= ';
';
		}

		$class .='
	/**
	 * Column Accessors and Mutators
	 */
';

		foreach($fields as $key=>$field){
			$default = $field->getDefaultValue() ? $field->getDefaultValue()->getValue() : null;
			$class .= '
	function get'.($options['cap_method_names'] ? ucfirst($field->getName()) : $field->getName()).'('.($field->isTemporalType() ? '$format = null' : '').'){';
			if($field->isTemporalType()){
				$class .= '
		if($this->'.$field->getName().'===null || !$format)
			return $this->'.$field->getName().';
		if(strpos($this->'.$field->getName().', "0000-00-00")===0)
			return null;
		$dateTime = new DateTime($this->'.$field->getName().');
		return $dateTime->format($format);';
			}
			else{
			$class .='
		return $this->'.$field->getName().';';
			}
			$class .='
	}
	function set'.($options['cap_method_names'] ? ucfirst($field->getName()) : $field->getName()).'($theValue){';
			if($field->isNumericType() || $field->isTemporalType()){
				if($field->isNumericType() && $options['empty_string_zero'] && $field->getName()!=$PK){
					$class .= '
		if($theValue==="")
			$theValue = 0;';
				}
				else{
					$class .= '
		if($theValue==="")
			$theValue = null;';
					if($field->getType()==PropelTypes::TIMESTAMP){
						$class .= '
		elseif($theValue!==null)
			$theValue = date('.$className.'::getConnection()->getTimestampFormatter(), strtotime($theValue));';
					}
					elseif($field->getType()==PropelTypes::DATE){
						$class .= '
		elseif($theValue!==null)
			$theValue = date('.$className.'::getConnection()->getDateFormatter(), strtotime($theValue));';
					}
					elseif($field->getType()==PropelTypes::TIME){
						$class .= '
		elseif($theValue!==null)
			$theValue = date('.$className.'::getConnection()->getTimeFormatter(), strtotime($theValue));';
					}
				}
			}

			if($options['protect_not_null'] && $field->getName()!=$PK && $field->isNotNull()){
				$class .= '
		if($theValue===null){';
				if($default){
					$class .='
			$pk = $this->getPrimaryKey();
			if($pk && $this->$pk===null)
				$theValue='.(is_numeric($default) ? $default : '"'.$default.'"').';
			else{';
				}

				if($field->isNumericType())
					$class .= '
			$theValue = 0;';
				else
					$class .= '
			$theValue = "";';

				if($default){
					$class .= '
			}';
				}
				$class .= '
		}';
			}
			if($field->getPdoType()==PDO::PARAM_INT){
				$class .= '
		if($theValue!==null)
			$theValue = (int)$theValue;';
			}

			$class .= '
		if($this->'.$field->getName().' !== $theValue){
			$this->_modifiedColumns[] = "'.$field->getName().'";
			$this->'.$field->getName().' = $theValue;
		}
	}
';
		}

		$class .= '

	/**
	 * @return DABLPDO
	 */
	static function getConnection(){
		return DBManager::getConnection("'.$this->getConnectionName().'");
	}

	/**
	 * Returns String representation of table name
	 * @return String
	 */
	static function getTableName(){
		return '.$className.'::$_tableName;
	}

	/**
	 * Access to array of column names
	 * @return array
	 */
	static function getColumnNames(){
		return '.$className.'::$_columnNames;
	}

	/**
	 * @return bool
	 */
	static function hasColumn($columnName){
		return in_array(strtolower($columnName), array_map(\'strtolower\', '.$className.'::getColumnNames()));
	}

	/**
	 * Access to array of primary keys
	 * @return array
	 */
	static function getPrimaryKeys(){
		return '.$className.'::$_primaryKeys;
	}

	/**
	 * Access to name of primary key
	 * @return array
	 */
	static function getPrimaryKey(){
		return '.$className.'::$_primaryKey;
	}

	/**
	 * Searches the database for a row with the ID(primary key) that matches
	 * the one input.
	 * @return '.$className.'
	 */
	static function retrieveByPK( $thePK ){
		if($thePK===null)return null;
		$PKs = '.$className.'::getPrimaryKeys();
		if(count($PKs)>1)
			throw new Exception("This table has more than one primary key.  Use retrieveByPKs() instead.");
		elseif(count($PKs)==0)
			throw new Exception("This table does not have a primary key.");
		$q = new Query;
		$conn = '.$className.'::getConnection();
		$pkColumn = $conn->quoteIdentifier($PKs[0]);
		$q->add($pkColumn, $thePK);
		$q->setLimit(1);
		return array_shift('.$className.'::doSelect($q));
	}

	/**
	 * Searches the database for a row with the primary keys that match
	 * the ones input.
	 * @return '.$className.'
	 */
	static function retrieveByPKs( ';
		foreach($PKs as $key=>$value){
			if($key == 0) $class .= '$PK'.$key;
			if($key > 0) $class .= ', $PK'.$key;
		}

		$class .= ' ){
		$conn = '.$className.'::getConnection();
		$tableWrapped = $conn->quoteIdentifier('.$className.'::getTableName());';
		foreach($PKs as $key=>$value){
			$class .= '
		if($PK'.$key.'===null)return null;';
		}
		$class .= '
		$queryString = "SELECT * FROM $tableWrapped WHERE ';

		foreach($PKs as $key=>$value){
			if($key == 0) $class .= '".$conn->quoteIdentifier(\''.$value.'\')."=".$conn->checkInput($PK'.$key.')."';
			if($key > 0) $class .= ' AND '.$value.'=".$conn->checkInput($PK'.$key.')."';
		}

		$class .= '";
		$conn->applyLimit($queryString, 0, 1);
		return '.$className.'::fetchSingle($queryString);
	}

	/**
	 * Populates and returns an instance of '.$className.' with the
	 * first result of a query.  If the query returns no results,
	 * returns null.
	 * @return '.$className.'
	 */
	static function fetchSingle($queryString){
		return array_shift('.$className.'::fetch($queryString));
	}

	/**
	 * Populates and returns an Array of '.$className.' Objects with the
	 * results of a query.  If the query returns no results,
	 * returns an empty Array.
	 * @return array
	 */
	static function fetch($queryString){
		$conn = '.$className.'::getConnection();
		$result = $conn->query($queryString);
		return '.$className.'::fromResult($result);
	}

	/**
	 * Returns an array of '.$className.' Objects from the rows of a PDOStatement(query result)
	 * @return array
	 */
	 static function fromResult(PDOStatement $result){
		$objects = array();
		while($row = $result->fetch(PDO::FETCH_ASSOC)){
			$object = new '.$className.';
			$object->fromArray($row);
			$object->resetModified();
			$object->setNew(false);
			$objects[] = $object;
		}
		return $objects;
	 }

	/**
	 * Returns an Array of all '.$className.' Objects in the database.
	 * $extra SQL can be appended to the query to limit,sort,group results.
	 * If there are no results, returns an empty Array.
	 * @param $extra String
	 * @return array
	 */
	static function getAll($extra = null){
		$conn = '.$className.'::getConnection();
		$tableWrapped = $conn->quoteIdentifier('.$className.'::getTableName());
		return '.$className.'::fetch("SELECT * FROM $tableWrapped $extra ");
	}

	/**
	 * @return Int
	 */
	static function doCount(Query $q){
		$conn = '.$className.'::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), '.$className.'::getTableName())===false )
			$q->setTable('.$className.'::getTableName());
		return $q->doCount($conn);
	}

	/**
	 * @return Int
	 */
	static function doDelete(Query $q){
		$conn = '.$className.'::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), '.$className.'::getTableName())===false )
			$q->setTable('.$className.'::getTableName());
		return $q->doDelete($conn);
	}

	/**
	 * @return array
	 */
	static function doSelect(Query $q){
		$conn = '.$className.'::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), '.$className.'::getTableName())===false )
			$q->setTable('.$className.'::getTableName());
		return '.$className.'::fromResult($q->doSelect($conn));
	}
';

		$used_from = array();
		foreach($this->getForeignKeysFromTable($tableName) as $r){
			$to_table = $r['to_table'];
			$to_className = $this->getModelName($to_table);
			$to_column = $r['to_column'];
			$from_column = $r['from_column'];

			if(@$used_from[$to_table]) continue;

			$used_from[$to_table] = $from_column;

			$class .= '
	/**
	 * @var '.$to_className.'
	 */
	private $'.$to_className.'_c;

	/**
	 * Returns a '.$to_table.' Object(row) from the '.$to_table.' table
	 * with a '.$to_column.' that matches $this->'.$from_column.'.
	 * When first called, this method will cache the result.
	 * After that, if $this->'.$from_column.' is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return '.$to_className.'
	 */
	function get'.$to_className.'(){
		if($this->get'.$from_column.'()===null)
			return null;
		$conn = $this->getConnection();
		$columnQuoted = $conn->quoteIdentifier("'.$to_column.'");
		$tableQuoted = $conn->quoteIdentifier('.$to_className.'::getTableName());
		if($this->getCacheResults() && @$this->'.$to_className.'_c && !$this->isColumnModified("'.$from_column.'"))return $this->'.$to_className.'_c;
		$queryString = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->get'.$from_column.'());
		$'.$to_table.' = '.$to_className.'::fetchSingle($queryString);
		$this->'.$to_className.'_c = $'.$to_table.';
		return $'.$to_table.';
	}
';
		}

		$used_to = array();
		foreach($this->getForeignKeysToTable($tableName) as $r){
			$from_table = $r['from_table'];
			$from_className = $this->getModelName($from_table);
			$from_column = $r['from_column'];
			$to_column = $r['to_column'];
			if(@$used_to[$from_table]){
				echo "WARNING: <b>$tableName.$to_column</b> USED BY MORE THAN ONE FOREIGN KEY IN TABLE: <b>$from_table</b>.
						METHODS CREATED FOR <b>$from_table.".$used_to[$from_table]."</b> ONLY.<br />";
				continue;
			}
			$used_to[$from_table]=$from_column;
			$class .= '

	/**
	 * Returns a Query for selecting '.$from_table.' Objects(rows) from the '.$from_table.' table
	 * with a '.$from_column.' that matches $this->'.$to_column.'.
	 * @return Query
	 */
	function get'.$from_className.'sQuery(Query $q = null){
		if($this->get'.$to_column.'()===null)
			throw new Exception("NULL cannot be used to match keys.");
		$conn = $this->getConnection();
		$column = $conn->quoteIdentifier("'.$from_column.'");
		if($q){
			$q = clone $q;
			$alias = $q->getAlias();
			if($alias && $q->getTableName()=="'.$from_table.'")
				$column = "$alias.$column";
		}
		else
			$q = new Query;
		$q->add($column, $this->get'.$to_column.'());
		return $q;
	}

	/**
	 * Returns the count of '.$from_className.' Objects(rows) from the '.$from_table.' table
	 * with a '.$from_column.' that matches $this->'.$to_column.'.
	 * @return Int
	 */
	function count'.$from_className.'s(Query $q = null){
		if($this->get'.$to_column.'()===null)
			return 0;
		return '.$from_className.'::doCount($this->get'.$from_className.'sQuery($q));
	}

	/**
	 * Deletes the '.$from_table.' Objects(rows) from the '.$from_table.' table
	 * with a '.$from_column.' that matches $this->'.$to_column.'.
	 * @return Int
	 */
	function delete'.$from_className.'s(Query $q = null){
		if($this->get'.$to_column.'()===null)
			return 0;
		return '.$from_className.'::doDelete($this->get'.$from_className.'sQuery($q));
	}

	private $'.$from_className.'s_c = array();

	/**
	 * Returns an Array of '.$from_className.' Objects(rows) from the '.$from_table.' table
	 * with a '.$from_column.' that matches $this->'.$to_column.'.
	 * When first called, this method will cache the result.
	 * After that, if $this->'.$to_column.' is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return array
	 */
	function get'.$from_className.'s($extra=NULL){
		if($this->get'.$to_column.'()===null)
			return array();

		if(!$extra || $extra instanceof Query)
			return '.$from_className.'::doSelect($this->get'.$from_className.'sQuery($extra));

		if(!$extra && $this->getCacheResults() && @$this->'.$from_className.'s_c && !$this->isColumnModified("'.$to_column.'"))
			return $this->'.$from_className.'s_c;

		$conn = $this->getConnection();
		$tableQuoted = $conn->quoteIdentifier('.$from_className.'::getTableName());
		$columnQuoted = $conn->quoteIdentifier("'.$from_column.'");
		$queryString = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->get'.$to_column.'())." $extra";
		$'.$from_table.'s = '.$from_className.'::fetch($queryString);
		if(!$extra)$this->'.$from_className.'s_c = $'.$from_table.'s;
		return $'.$from_table.'s;
	}
';
		}

		$class .= '
}';

//<?php

		return $class;
	}

	/**
	 * Generates a String with the contents of the stub class
	 * for the table, which is used for extending the Base class.
	 * @param String $tableName
	 * @param String $className
	 * @return String
	 */
	function getModel($tableName){
		$className = $this->getModelName($tableName);
		$options = $this->options;
		$class = "<?php

class ".$className." extends base$className{

}";
//<?php

		return $class;
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
	 * @return String
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
	 * @return String
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
	 * @return String
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
