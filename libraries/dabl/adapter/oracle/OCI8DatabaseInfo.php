<?php

/**
 * Oracle (OCI8) implementation of DatabaseInfo.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.11 $
 * @package   creole.drivers.oracle.metadata
 */
class OCI8DatabaseInfo extends DatabaseInfo {

	private $schema;

	function __construct(DBAdapter $conn) {
		parent::__construct($conn);

		throw new Exception('This method needs a schema.  Maybe someone who knows Oracle will be able to know what to do here.');

		if ($schama) {
			$this->schema = $schema;
		} else {
			// For Changing DB/Schema in Meta Data Interface
			$this->schema = $user_name;
		}

		$this->schema = strtoupper( $this->schema );
	}

	function getSchema() {
		return $this->schema;
	}

	/**
	 * @throws SQLException
	 * @return void
	 */
	protected function initTables() {
		$sql = "SELECT table_name
            FROM all_tables
            WHERE owner = '{$this->schema}'";

		$result = $this->conn->query($sql);

		while ( $row = $result->fetch() ) {
			$row = array_change_key_case($row,CASE_LOWER);
			$this->tables[strtoupper($row['table_name'])] = new OCI8TableInfo($this,$row['table_name']);
		}
	}

	/**
	 * Oracle supports sequences.
	 *
	 * @return void
	 * @throws SQLException
	 */
	protected function initSequences() {
		// throw new SQLException("MySQL does not support sequences natively.");
	}

}
