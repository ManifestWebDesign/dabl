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

abstract class baseUser extends BaseTable{

	/**
	 * Name of the table
	 */
	protected static $_tableName = "user";

	/**
	 * Array of all primary keys
	 */
	protected static $_primaryKeys = array(
			"idUser",
	);

	/**
	 * Primary Key
	 */
	 protected static $_primaryKey = "idUser";

	/**
	 * Array of all column names
	 */
	protected static $_columnNames = array(
		'idUser',
		'username',
		'password',
		'country',
		'email',
		'enabled'
	);
	protected $idUser;
	protected $username = "";
	protected $password = "";
	protected $country = "";
	protected $email = "";
	protected $enabled = 0;

	/**
	 * Column Accessors and Mutators
	 */

	function getIdUser(){
		return $this->idUser;
	}
	function setIdUser($theValue){
		if($theValue==="")
			$theValue = null;
		if($theValue!==null)
			$theValue = (int)$theValue;
		if($this->idUser !== $theValue){
			$this->_modifiedColumns[] = "idUser";
			$this->idUser = $theValue;
		}
	}

	function getUsername(){
		return $this->username;
	}
	function setUsername($theValue){
		if($theValue===null){
			$theValue = "";
		}
		if($this->username !== $theValue){
			$this->_modifiedColumns[] = "username";
			$this->username = $theValue;
		}
	}

	function getPassword(){
		return $this->password;
	}
	function setPassword($theValue){
		if($theValue===null){
			$theValue = "";
		}
		if($this->password !== $theValue){
			$this->_modifiedColumns[] = "password";
			$this->password = $theValue;
		}
	}

	function getCountry(){
		return $this->country;
	}
	function setCountry($theValue){
		if($theValue===null){
			$theValue = "";
		}
		if($this->country !== $theValue){
			$this->_modifiedColumns[] = "country";
			$this->country = $theValue;
		}
	}

	function getEmail(){
		return $this->email;
	}
	function setEmail($theValue){
		if($theValue===null){
			$theValue = "";
		}
		if($this->email !== $theValue){
			$this->_modifiedColumns[] = "email";
			$this->email = $theValue;
		}
	}

	function getEnabled(){
		return $this->enabled;
	}
	function setEnabled($theValue){
		if($theValue==="")
			$theValue = null;
		if($theValue===null){
			$theValue = 0;
		}
		if($theValue!==null)
			$theValue = (int)$theValue;
		if($this->enabled !== $theValue){
			$this->_modifiedColumns[] = "enabled";
			$this->enabled = $theValue;
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
		return User::$_tableName;
	}

	/**
	 * Access to array of column names
	 * @return array
	 */
	static function getColumnNames(){
		return User::$_columnNames;
	}

	/**
	 * Access to array of primary keys
	 * @return array
	 */
	static function getPrimaryKeys(){
		return User::$_primaryKeys;
	}

	/**
	 * Access to name of primary key
	 * @return array
	 */
	static function getPrimaryKey(){
		return User::$_primaryKey;
	}

	/**
	 * Searches the database for a row with the ID(primary key) that matches
	 * the one input.
	 * @return User
	 */
	static function retrieveByPK( $thePK ){
		if(!$thePK===null)return null;
		$PKs = User::getPrimaryKeys();
		if(count($PKs)>1)
			throw new Exception("This table has more than one primary key.  Use retrieveByPKs() instead.");
		elseif(count($PKs)==0)
			throw new Exception("This table does not have a primary key.");
		$conn = User::getConnection();
		$pkColumn = $conn->quoteIdentifier($PKs[0]);
		$tableWrapped = $conn->quoteIdentifier(User::getTableName());
		$query = "SELECT * FROM $tableWrapped WHERE $pkColumn=".$conn->checkInput($thePK);
		$conn->applyLimit($query, 0, 1);
		return User::fetchSingle($query);
	}

	/**
	 * Searches the database for a row with the primary keys that match
	 * the ones input.
	 * @return User
	 */
	static function retrieveByPKs( $PK0 ){
		$conn = User::getConnection();
		$tableWrapped = $conn->quoteIdentifier(User::getTableName());
		if($PK0===null)return null;
		$queryString = "SELECT * FROM $tableWrapped WHERE idUser=".$conn->checkInput($PK0)."";
		$conn->applyLimit($queryString, 0, 1);
		return User::fetchSingle($queryString);
	}

	/**
	 * Populates and returns an instance of User with the
	 * first result of a query.  If the query returns no results,
	 * returns null.
	 * @return User
	 */
	static function fetchSingle($queryString){
		return array_shift(User::fetch($queryString));
	}

	/**
	 * Populates and returns an Array of User Objects with the
	 * results of a query.  If the query returns no results,
	 * returns an empty Array.
	 * @return array
	 */
	static function fetch($queryString){
		$conn = User::getConnection();
		$result = $conn->query($queryString);
		return User::fromResult($result);
	}

	/**
	 * Returns an array of User Objects from the rows of a PDOStatement(query result)
	 * @return array
	 */
	 static function fromResult(PDOStatement $result){
		$objects = array();
		while($row = $result->fetch(PDO::FETCH_ASSOC)){
			$object = new User;
			$object->fromArray($row);
			$object->resetModified();
			$object->setNew(false);
			$objects[] = $object;
		}
		return $objects;
	 }

	/**
	 * Returns an Array of all User Objects in the database.
	 * $extra SQL can be appended to the query to limit,sort,group results.
	 * If there are no results, returns an empty Array.
	 * @param $extra String
	 * @return array
	 */
	static function getAll($extra = null){
		$conn = User::getConnection();
		$tableWrapped = $conn->quoteIdentifier(User::getTableName());
		return User::fetch("SELECT * FROM $tableWrapped $extra ");
	}

	/**
	 * @return Int
	 */
	static function doCount(Query $q){
		$conn = User::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), User::getTableName())===false )
			$q->setTable(User::getTableName());
		return $q->doCount($conn);
	}

	/**
	 * @return Int
	 */
	static function doDelete(Query $q){
		$conn = User::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), User::getTableName())===false )
			$q->setTable(User::getTableName());
		return $q->doDelete($conn);
	}

	/**
	 * @return array
	 */
	static function doSelect(Query $q){
		$conn = User::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), User::getTableName())===false )
			$q->setTable(User::getTableName());
		return User::fromResult($q->doSelect($conn));
	}


	/**
	 * Returns a Query for selecting comment Objects(rows) from the comment table
	 * with a idUser that matches $this->idUser.
	 * @return Query
	 */
	function getCommentsQuery(Query $q = null){
		if($this->getidUser()===null)
			throw new Exception("NULL cannot be used to match keys.");
		$column = "idUser";
		if($q){
			$q = clone $q;
			$alias = $q->getAlias();
			if($q->getTableName()=="comment" && $alias)
				$column = "$alias.idUser";
		}
		else
			$q = new Query;
		$q->add($column, $this->getidUser());
		return $q;
	}

	/**
	 * Returns the count of Comment Objects(rows) from the comment table
	 * with a idUser that matches $this->idUser.
	 * @return Int
	 */
	function countComments(Query $q = null){
		if($this->getidUser()===null)
			return 0;
		return Comment::doCount($this->getCommentsQuery($q));
	}

	/**
	 * Deletes the comment Objects(rows) from the comment table
	 * with a idUser that matches $this->idUser.
	 * @return Int
	 */
	function deleteComments(Query $q = null){
		if($this->getidUser()===null)
			return 0;
		return Comment::doDelete($this->getCommentsQuery($q));
	}

	private $comments_c = array();

	/**
	 * Returns an Array of Comment Objects(rows) from the comment table
	 * with a idUser that matches $this->idUser.
	 * When first called, this method will cache the result.
	 * After that, if $this->idUser is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return array
	 */
	function getComments($extra=NULL){
		if($this->getidUser()===null)
			return array();

		if($extra instanceof Query)
			return Comment::doSelect($this->getCommentsQuery($extra));

		if(!$extra && $this->getCacheResults() && @$this->Comments_c && !$this->isColumnModified("idUser"))
			return $this->Comments_c;

		$conn = $this->getConnection();
		$tableQuoted = $conn->quoteIdentifier(Comment::getTableName());
		$columnQuoted = $conn->quoteIdentifier("idUser");
		$queryString = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->getidUser())." $extra";
		$comments = Comment::fetch($queryString);
		if(!$extra)$this->comments_c = $comments;
		return $comments;
	}


	/**
	 * Returns a Query for selecting post Objects(rows) from the post table
	 * with a idUser that matches $this->idUser.
	 * @return Query
	 */
	function getPostsQuery(Query $q = null){
		if($this->getidUser()===null)
			throw new Exception("NULL cannot be used to match keys.");
		$column = "idUser";
		if($q){
			$q = clone $q;
			$alias = $q->getAlias();
			if($q->getTableName()=="post" && $alias)
				$column = "$alias.idUser";
		}
		else
			$q = new Query;
		$q->add($column, $this->getidUser());
		return $q;
	}

	/**
	 * Returns the count of Post Objects(rows) from the post table
	 * with a idUser that matches $this->idUser.
	 * @return Int
	 */
	function countPosts(Query $q = null){
		if($this->getidUser()===null)
			return 0;
		return Post::doCount($this->getPostsQuery($q));
	}

	/**
	 * Deletes the post Objects(rows) from the post table
	 * with a idUser that matches $this->idUser.
	 * @return Int
	 */
	function deletePosts(Query $q = null){
		if($this->getidUser()===null)
			return 0;
		return Post::doDelete($this->getPostsQuery($q));
	}

	private $posts_c = array();

	/**
	 * Returns an Array of Post Objects(rows) from the post table
	 * with a idUser that matches $this->idUser.
	 * When first called, this method will cache the result.
	 * After that, if $this->idUser is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return array
	 */
	function getPosts($extra=NULL){
		if($this->getidUser()===null)
			return array();

		if($extra instanceof Query)
			return Post::doSelect($this->getPostsQuery($extra));

		if(!$extra && $this->getCacheResults() && @$this->Posts_c && !$this->isColumnModified("idUser"))
			return $this->Posts_c;

		$conn = $this->getConnection();
		$tableQuoted = $conn->quoteIdentifier(Post::getTableName());
		$columnQuoted = $conn->quoteIdentifier("idUser");
		$queryString = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->getidUser())." $extra";
		$posts = Post::fetch($queryString);
		if(!$extra)$this->posts_c = $posts;
		return $posts;
	}

}