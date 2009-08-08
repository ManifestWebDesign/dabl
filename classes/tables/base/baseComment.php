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

abstract class baseComment extends BaseTable{

	/**
	 * Name of the table
	 */
	protected static $_tableName = "comment";

	/**
	 * Array of all primary keys
	 */
	protected static $_primaryKeys = array(
			"idComment",
	);

	/**
	 * Primary Key
	 */
	 protected static $_primaryKey = "idComment";

	/**
	 * Array of all column names
	 */
	protected static $_columnNames = array(
		'idComment',
		'idUser',
		'idPost',
		'text'
	);
	protected $idComment;
	protected $idUser = 0;
	protected $idPost = 0;
	protected $text = "";

	/**
	 * Column Accessors and Mutators
	 */

	function getIdComment(){
		return $this->idComment;
	}
	function setIdComment($theValue){
		if($theValue==="")
			$theValue = null;
		if($theValue!==null)
			$theValue = (int)$theValue;
		if($this->idComment !== $theValue){
			$this->_modifiedColumns[] = "idComment";
			$this->idComment = $theValue;
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

	function getIdPost(){
		return $this->idPost;
	}
	function setIdPost($theValue){
		if($theValue==="")
			$theValue = null;
		if($theValue===null){
			$theValue = 0;
		}
		if($theValue!==null)
			$theValue = (int)$theValue;
		if($this->idPost !== $theValue){
			$this->_modifiedColumns[] = "idPost";
			$this->idPost = $theValue;
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
		return Comment::$_tableName;
	}

	/**
	 * Access to array of column names
	 * @return array
	 */
	static function getColumnNames(){
		return Comment::$_columnNames;
	}

	/**
	 * Access to array of primary keys
	 * @return array
	 */
	static function getPrimaryKeys(){
		return Comment::$_primaryKeys;
	}

	/**
	 * Access to name of primary key
	 * @return array
	 */
	static function getPrimaryKey(){
		return Comment::$_primaryKey;
	}

	/**
	 * Searches the database for a row with the ID(primary key) that matches
	 * the one input.
	 * @return Comment
	 */
	static function retrieveByPK( $thePK ){
		if(!$thePK===null)return null;
		$PKs = Comment::getPrimaryKeys();
		if(count($PKs)>1)
			throw new Exception("This table has more than one primary key.  Use retrieveByPKs() instead.");
		elseif(count($PKs)==0)
			throw new Exception("This table does not have a primary key.");
		$conn = Comment::getConnection();
		$pkColumn = $conn->quoteIdentifier($PKs[0]);
		$tableWrapped = $conn->quoteIdentifier(Comment::getTableName());
		$query = "SELECT * FROM $tableWrapped WHERE $pkColumn=".$conn->checkInput($thePK);
		$conn->applyLimit($query, 0, 1);
		return Comment::fetchSingle($query);
	}

	/**
	 * Searches the database for a row with the primary keys that match
	 * the ones input.
	 * @return Comment
	 */
	static function retrieveByPKs( $PK0 ){
		$conn = Comment::getConnection();
		$tableWrapped = $conn->quoteIdentifier(Comment::getTableName());
		if($PK0===null)return null;
		$queryString = "SELECT * FROM $tableWrapped WHERE idComment=".$conn->checkInput($PK0)."";
		$conn->applyLimit($queryString, 0, 1);
		return Comment::fetchSingle($queryString);
	}

	/**
	 * Populates and returns an instance of Comment with the
	 * first result of a query.  If the query returns no results,
	 * returns null.
	 * @return Comment
	 */
	static function fetchSingle($queryString){
		return array_shift(Comment::fetch($queryString));
	}

	/**
	 * Populates and returns an Array of Comment Objects with the
	 * results of a query.  If the query returns no results,
	 * returns an empty Array.
	 * @return array
	 */
	static function fetch($queryString){
		$conn = Comment::getConnection();
		$result = $conn->query($queryString);
		return Comment::fromResult($result);
	}

	/**
	 * Returns an array of Comment Objects from the rows of a PDOStatement(query result)
	 * @return array
	 */
	 static function fromResult(PDOStatement $result){
		$objects = array();
		while($row = $result->fetch(PDO::FETCH_ASSOC)){
			$object = new Comment;
			$object->fromArray($row);
			$object->resetModified();
			$object->setNew(false);
			$objects[] = $object;
		}
		return $objects;
	 }

	/**
	 * Returns an Array of all Comment Objects in the database.
	 * $extra SQL can be appended to the query to limit,sort,group results.
	 * If there are no results, returns an empty Array.
	 * @param $extra String
	 * @return array
	 */
	static function getAll($extra = null){
		$conn = Comment::getConnection();
		$tableWrapped = $conn->quoteIdentifier(Comment::getTableName());
		return Comment::fetch("SELECT * FROM $tableWrapped $extra ");
	}

	/**
	 * @return Int
	 */
	static function doCount(Query $q){
		$conn = Comment::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), Comment::getTableName())===false )
			$q->setTable(Comment::getTableName());
		return $q->doCount($conn);
	}

	/**
	 * @return Int
	 */
	static function doDelete(Query $q){
		$conn = Comment::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), Comment::getTableName())===false )
			$q->setTable(Comment::getTableName());
		return $q->doDelete($conn);
	}

	/**
	 * @return array
	 */
	static function doSelect(Query $q){
		$conn = Comment::getConnection();
		$q = clone $q;
		if(!$q->getTable() || strrpos($q->getTable(), Comment::getTableName())===false )
			$q->setTable(Comment::getTableName());
		return Comment::fromResult($q->doSelect($conn));
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
	 * @var Post
	 */
	private $post_c;

	/**
	 * Returns a post Object(row) from the post table
	 * with a idPost that matches $this->idPost.
	 * When first called, this method will cache the result.
	 * After that, if $this->idPost is not modified, the
	 * method will return the cached result instead of querying the database
	 * a second time(for performance purposes).
	 * @return Post
	 */
	function getPost(){
		if($this->getidPost()===null)
			return null;
		$conn = $this->getConnection();
		$columnQuoted = $conn->quoteIdentifier("idPost");
		$tableQuoted = $conn->quoteIdentifier(Post::getTableName());
		if($this->getCacheResults() && @$this->post_c && !$this->isColumnModified("idPost"))return $this->post_c;
		$queryString = "SELECT * FROM $tableQuoted WHERE $columnQuoted=".$conn->checkInput($this->getidPost());
		$post = Post::fetchSingle($queryString);
		$this->post_c = $post;
		return $post;
	}

}