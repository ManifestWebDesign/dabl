<?php

/**
 * SQLite implementation of DatabaseInfo.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.3 $
 * @package   creole.drivers.sqlite.metadata
 */
class SQLiteDatabaseInfo extends DatabaseInfo {

	/**
	 * @throws SQLException
	 * @return void
	 */
	protected function initTables() {
		$sql = "SELECT name FROM sqlite_master WHERE type='table' UNION ALL SELECT name FROM sqlite_temp_master WHERE type='table' ORDER BY name;";
		$result = $this->getConnection()->query($sql);

		while ($row = $result->fetch()) {
			$this->tables[strtoupper($row[0])] = new SQLiteTableInfo($this, $row[0]);
		}
	}

	/**
	 * SQLite does not support sequences.
	 *
	 * @return void
	 * @throws SQLException
	 */
	protected function initSequences() {
		// throw new SQLException("MySQL does not support sequences natively.");
	}

}
