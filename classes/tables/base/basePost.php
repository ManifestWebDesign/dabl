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

abstract class basePost extends BaseTable{

	/**
	 * Name of the table
	 */
	protected static $_tableName = "post";

	/**
	 * Array of all primary keys
	 */
	protected static $_primaryKeys = array(
			"idPost",
	);

	/**
	 * Primary Key
	 */
	 protected static $_primaryKey = "idPost";

	/**
	 * Array of all column names
	 */
	protected static $_columnNames = array(
		'idPost',
		'idUser',
		'text'
	);
	protected $idPost;
	protected $idUser = 0;
	protected $text = "";

	/**
	 * Column Accessors and Mutators
	 */

	function getIdPost(){
		return $this->idPost;
	}
	function setIdPost($theValue){
		if($theValue==="")
			$theValue = null;
		if($theValue!==null)
			$theValue = (int)$theValue;
		if($this->idPost !== $theValue){
			$this->_modifiedColumns[] = "idPost";
			$this->idPost = $theValue;
		}
	}

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

	function getText(){
		return $this->text;
	}
	function setText($theValue){
		if($theValue===null){
			$theValue = "";
		}
		if($this->text !== $theValue){
			$this->_modifiedColumns[] = "text";
			$this->text = $theValue;
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
		return Post::$_tableName;
	}

	/**
	 * Access to array of column names
	 * @return array
	 */
	static function getColumnNames(){
		return Post::$_columnNames;
	}

	/**
	 * Access to array of primary keys
	 * @return array
	 */
	static function getPrimaryKeys(){
		return Post::$_primaryKeys;
	}

	/**
	 * Access to name of primary key
	 * @return array
	 */
	static function getPrimaryKey(){
		return Post::$_primaryKey;
	}

	/**
	 * Searches the database for a row with the ID(primary key) that matches
	 * the one input.
	 * @return Post
	 */
	static function retrieveByPK( $thePK ){
		if(!$thePK===null)return null;
		$PKs = Post::getPrimaryKeys();
		if(count($PKs)>1)
			throw new Exception("This table has more than one primary key.  Use retrieveByPKs() instead.");
		elseif(count($PKs)==0)
			throw new Exception("This table does not have a primary key.");
		$conn = Post::getConnection();
		$pkColumn = $conn->quoteIdentifier($PKs[0]);
		$tableWrapped = $conn->quoteIdentifier(Post::getTableName());
		$query = "SELECT * FROM $tableWrapped WHERE $pkColumn=".$conn->checkInput($thePK);
		$conn->applyLimit($query, 0, 1);
		return Post::fetchSingle($query);
	}

	/**
	 * Searches the database for a row with the primary keys that match
	 * the ones input.
	 * @return Post
	 */
	static function retrieveByPKs( $PK0 ){
		$conn = Post::getConnection();
		$tableWrapped = $conn->quoteIdentifier(Post::getTableName());
		if($PK0===null)return null;
		$queryString = "SELECT * FROM $tableWrapped WHERE idPost=".$conn->checkInput($PK0)."";
		$conn->applyLimit($queryString, 0, 1);
		return Post::fetchSingle($queryString);
	}

	/**
	 * Populates and returns an instance of Post with the
	 * first result of a query.  If the query returns no results,
	 * returns null.
	 * @return Post
	 */
	static function fetchSingle($queryString){
		return array_shift(Post::fetch($queryString));
	}

	/**
	 * Populates and returns an Array of Post Objects with the
	 * results of a query.  If the query returns no results,
	 * returns an empty Array.
	 * @return array
	 */
	static function fetch($queryString){
		$conn = Post::getConnection();
		$result = $conn->query($queryString);
		return Post::fromResult($result);
	}

	/**
	 * Returns an array of Post Objects from the rows of a PDOStatement(query result)
	 * @return array
	 */
	 static function fromResult(PDOStatement $result){
		$objects = array();
		while($row = $result->fetch(PDO::FETCH_ASSOC)){
			$object = new Post;
			$object->fromArray($row);
			$object->resetModified();
			$object->setNew(false);
			$objects[] = $object;
		}
		return $objects;
	 }

	/**
	 * Returns an Array of all Post Objects in the database.
	 * $extra SQL can be appended to the query to limit,sort,group results.
	 * If there are no results, returns an empty Array.
	 * @param $extra String
	 * @return array
	 */
	static function getAll($extra = null){
		$conn = Post::getConnection();
		$tableWrapped = $conn->quoteIdentifier(Post::getTableName());
		return Post::fetch("SELECT * FROM $tableWrapped $extra ");
	}

	/**
	 * @return Int
	 */
	static function doCount(Query $q){
		$conn = Post::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), Post::getTableName())===false )
			$q->setTable(Post::getTableName());
		return $q->doCount($conn);
	}

	/**
	 * @return Int
	 */
	static function doDelete(Query $q){
		$conn = Post::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), Post::getTableName())===false )
			$q->setTable(Post::getTableName());
		return $q->doDelete($conn);
	}

	/**
	 * @return array
	 */
	static function doSelect(Query $q){
		$conn = Post::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), Post::getTableName())===false )
			$q->setTable(Post::getTableName());
		return Post::fromResult($q->doSelect($conn));
	}

	/**
	 * @var User
	 */
	private $user_c;

	/**
	 * Returns a user Object(row) from the user table
	 * with a idUser that matches $this->idUser.
	 * When first called, this method will cache the result.
	 * After that, if $this->idUser is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return User
	 */
	function getUser(){
		if($this->getidUser()===null)
			return null;
		$conn = $this->getConnection();
		$columnQuoted = $conn->quoteIdentifier("idUser");
		$tableQuoted = $conn->quoteIdentifier(User::getTableName());
		if($this->getCacheResults() && @$this->user_c && !$this->isColumnModified("idUser"))return $this->user_c;
		$queryString = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->getidUser());
		$user = User::fetchSingle($queryString);
		$this->user_c = $user;
		return $user;
	}


	/**
	 * Returns a Query for selecting comment Objects(rows) from the comment table
	 * with a idPost that matches $this->idPost.
	 * @return Query
	 */
	function getCommentsQuery(Query $q = null){
		if($this->getidPost()===null)
			throw new Exception("NULL cannot be used to match keys.");
		$column = "idPost";
		if($q){
			$q = clone $q;
			$alias = $q->getAlias();
			if($q->getTableName()=="comment" && $alias)
				$column = "$alias.idPost";
		}
		else
			$q = new Query;
		$q->add($column, $this->getidPost());
		return $q;
	}

	/**
	 * Returns the count of Comment Objects(rows) from the comment table
	 * with a idPost that matches $this->idPost.
	 * @return Int
	 */
	function countComments(Query $q = null){
		if($this->getidPost()===null)
			return 0;
		return Comment::doCount($this->getCommentsQuery($q));
	}

	/**
	 * Deletes the comment Objects(rows) from the comment table
	 * with a idPost that matches $this->idPost.
	 * @return Int
	 */
	function deleteComments(Query $q = null){
		if($this->getidPost()===null)
			return 0;
		return Comment::doDelete($this->getCommentsQuery($q));
	}

	private $comments_c = array();

	/**
	 * Returns an Array of Comment Objects(rows) from the comment table
	 * with a idPost that matches $this->idPost.
	 * When first called, this method will cache the result.
	 * After that, if $this->idPost is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return array
	 */
	function getComments($extra=NULL){
		if($this->getidPost()===null)
			return array();

		if($extra instanceof Query)
			return Comment::doSelect($this->getCommentsQuery($extra));

		if(!$extra && $this->getCacheResults() && @$this->Comments_c && !$this->isColumnModified("idPost"))
			return $this->Comments_c;

		$conn = $this->getConnection();
		$tableQuoted = $conn->quoteIdentifier(Comment::getTableName());
		$columnQuoted = $conn->quoteIdentifier("idPost");
		$queryString = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->getidPost())." $extra";
		$comments = Comment::fetch($queryString);
		if(!$extra)$this->comments_c = $comments;
		return $comments;
	}

}