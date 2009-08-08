<?php
/**
 *	Created by Dan Blaisdell's Database->Object Mapper
 *		             Based on Propel
 *
 *		Do not alter base files, as they will be overwritten.
 *		To alter the objects, alter the extended clases in
 *		the 'tables' folder.
 *
 */

abstract class baseUser_type extends BaseTable{

	/**
	 * Name of the table
	 */
	protected static $_tableName = "user_type";

	/**
	 * Array of all primary keys
	 */
	protected static $_primaryKeys = array(
			"UserTypeID",
	);

	/**
	 * Primary Key
	 */
	 protected static $_primaryKey = "UserTypeID";

	/**
	 * Array of all column names
	 */
	protected static $_columnNames = array(
		'UserTypeID',
		'Name'
	);
	protected $UserTypeID;
	protected $Name;

	/**
	 * Column Accessors and Mutators
	 */

	function getUserTypeID(){
		return $this->UserTypeID;
	}
	function setUserTypeID($theValue){
		if($theValue==="")
			$theValue = null;
		if($theValue!==null)
			$theValue = (int)$theValue;
		if($this->UserTypeID !== $theValue){
			$this->_modifiedColumns[] = "UserTypeID";
			$this->UserTypeID = $theValue;
		}
	}

	function getName(){
		return $this->Name;
	}
	function setName($theValue){
		if($this->Name !== $theValue){
			$this->_modifiedColumns[] = "Name";
			$this->Name = $theValue;
		}
	}


	/**
	 * @return DBAdapter
	 */
	static function getConnection(){
		return DBManager::getConnection("main");
	}

	/**
	 * Returns String representation of table name
	 * @return String
	 */
	static function getTableName(){
		return User_type::$_tableName;
	}

	/**
	 * Access to array of column names
	 * @return array
	 */
	static function getColumnNames(){
		return User_type::$_columnNames;
	}

	/**
	 * Access to array of primary keys
	 * @return array
	 */
	static function getPrimaryKeys(){
		return User_type::$_primaryKeys;
	}

	/**
	 * Access to name of primary key
	 * @return array
	 */
	static function getPrimaryKey(){
		return User_type::$_primaryKey;
	}

	/**
	 * Searches the database for a row with the ID(primary key) that matches
	 * the one input.
	 * @return User_type
	 */
	static function retrieveByPK( $thePK ){
		if(!$thePK===null)return null;
		$PKs = User_type::getPrimaryKeys();
		if(count($PKs)>1)
			throw new Exception("This table has more than one primary key.  Use retrieveByPKs() instead.");
		elseif(count($PKs)==0)
			throw new Exception("This table does not have a primary key.");
		$conn = User_type::getConnection();
		$pkColumn = $conn->quoteIdentifier($PKs[0]);
		$tableWrapped = $conn->quoteIdentifier(User_type::getTableName());
		$query = "SELECT * FROM $tableWrapped WHERE $pkColumn=".$conn->checkInput($thePK);
		$conn->applyLimit($query, 0, 1);
		return User_type::fetchSingle($query);
	}

	/**
	 * Searches the database for a row with the primary keys that match
	 * the ones input.
	 * @return User_type
	 */
	static function retrieveByPKs( $PK0 ){
		$conn = User_type::getConnection();
		$tableWrapped = $conn->quoteIdentifier(User_type::getTableName());
		if($PK0===null)return null;
		$queryString = "SELECT * FROM $tableWrapped WHERE UserTypeID=".$conn->checkInput($PK0)."";
		$conn->applyLimit($queryString, 0, 1);
		return User_type::fetchSingle($queryString);
	}

	/**
	 * Populates and returns an instance of User_type with the
	 * first result of a query.  If the query returns no results,
	 * returns null.
	 * @return User_type
	 */
	static function fetchSingle($queryString){
		return array_shift(User_type::fetch($queryString));
	}

	/**
	 * Populates and returns an Array of User_type Objects with the
	 * results of a query.  If the query returns no results,
	 * returns an empty Array.
	 * @return array
	 */
	static function fetch($queryString){
		$conn = User_type::getConnection();
		$result = $conn->query($queryString);
		return User_type::fromResult($result);
	}

	/**
	 * Returns an array of User_type Objects from the rows of a PDOStatement(query result)
	 * @return array
	 */
	 static function fromResult(PDOStatement $result){
		$objects = array();
		while($row = $result->fetch(PDO::FETCH_ASSOC)){
			$object = new User_type;
			$object->fromArray($row);
			$object->resetModified();
			$object->setNew(false);
			$objects[] = $object;
		}
		return $objects;
	 }

	/**
	 * Returns an Array of all User_type Objects in the database.
	 * $extra SQL can be appended to the query to limit,sort,group results.
	 * If there are no results, returns an empty Array.
	 * @param $extra String
	 * @return array
	 */
	static function getAll($extra = null){
		$conn = User_type::getConnection();
		$tableWrapped = $conn->quoteIdentifier(User_type::getTableName());
		return User_type::fetch("SELECT * FROM $tableWrapped $extra ");
	}

	/**
	 * @return Int
	 */
	static function doCount(Query $q){
		$conn = User_type::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), User_type::getTableName())===false )
			$q->setTable(User_type::getTableName());
		return $q->doCount($conn);
	}

	/**
	 * @return Int
	 */
	static function doDelete(Query $q){
		$conn = User_type::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), User_type::getTableName())===false )
			$q->setTable(User_type::getTableName());
		return $q->doDelete($conn);
	}

	/**
	 * @return array
	 */
	static function doSelect(Query $q){
		$conn = User_type::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), User_type::getTableName())===false )
			$q->setTable(User_type::getTableName());
		return User_type::fromResult($q->doSelect($conn));
	}

}