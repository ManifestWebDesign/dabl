<?php

/**
 * MSSQL impementation of DatabaseInfo.
 *
 * @author	Hans Lellelid
 * @version   $Revision: 1.11 $
 * @package   creole.drivers.mssql.metadata
 */ 
class MSSQLDatabaseInfo extends DatabaseInfo {
	
	/**
	 * @throws SQLException
	 * @return void
	 */
	protected function initTables(){
		$result = $this->conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME <> 'dtproperties'");
		
		while ($row = $result->fetch()) {
			$this->tables[strtoupper($row[0])] = new MSSQLTableInfo($this, $row[0]);			
		}
	}
	
	/**
	 * 
	 * @return void 
	 * @throws SQLException
	 */
	protected function initSequences(){
		// there are no sequences -- afaik -- in MSSQL.
	}
		
}
