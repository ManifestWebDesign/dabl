<?php

/**
 * MySQL implementation of DatabaseInfo.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.13 $
 * @package   creole.drivers.mysql.metadata
 */
class MySQLDatabaseInfo extends DatabaseInfo {

	/**
	 * @throws SQLException
	 * @return void
	 */
	protected function initTables(){
		$result = $this->getConnection()->query("SHOW TABLES FROM `" . $this->dbname. "`");

		while ($row = $result->fetch()) {
			$table = new MySQLTableInfo($this, $row[0]);
			$this->tables[strtoupper($row[0])] = $table;
			$table->getColumns();
		}
		
		$this->tablesLoaded = true;
	}

	/**
	 * MySQL does not support sequences.
	 *
	 * @return void
	 * @throws SQLException
	 */
	protected function initSequences(){
		// throw new SQLException("MySQL does not support sequences natively.");
	}
}
