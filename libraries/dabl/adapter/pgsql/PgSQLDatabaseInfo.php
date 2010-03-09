<?php

/**
 * MySQL implementation of DatabaseInfo.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.11 $
 * @package   creole.drivers.pgsql.metadata
 */
class PgSQLDatabaseInfo extends DatabaseInfo {

	/**
	 * @throws SQLException
	 * @return void
	 */
	protected function initTables() {
		// Get Database Version
		// TODO: www.php.net/pg_version
		$result = $this->getConnection()->query("SELECT version() as ver");

		$row = $result->fetch();
		$arrVersion = sscanf ($row['ver'], '%*s %d.%d');
		$version = sprintf ("%d.%d", $arrVersion[0], $arrVersion[1]);
		// Clean up
		$arrVersion = null;
		$row = null;
		$result = null;

		$sql = "SELECT c.oid,
				case when n.nspname='public' then c.relname else n.nspname||'.'||c.relname end as relname 
				FROM pg_class c join pg_namespace n on (c.relnamespace=n.oid)
				WHERE c.relkind = 'r'
				  AND n.nspname NOT IN ('information_schema','pg_catalog')
				  AND n.nspname NOT LIKE 'pg_temp%'
				  AND n.nspname NOT LIKE 'pg_toast%'
				ORDER BY relname";
		$result = $this->getConnection()->query($sql);

		while ($row = $result->fetch()) {
			$this->tables[strtoupper($row['relname'])] = new PgSQLTableInfo($this, $row['relname'], $version, $row['oid']);
		}

		$this->tablesLoaded = true;
	}

	/**
	 * PgSQL sequences.
	 *
	 * @return void
	 * @throws SQLException
	 */
	protected function initSequences() {
		$this->sequences = array();
		$sql = "SELECT c.oid,
				case when n.nspname='public' then c.relname else n.nspname||'.'||c.relname end as relname
				FROM pg_class c join pg_namespace n on (c.relnamespace=n.oid)
				WHERE c.relkind = 'S'
				  AND n.nspname NOT IN ('information_schema','pg_catalog')
				  AND n.nspname NOT LIKE 'pg_temp%'
				  AND n.nspname NOT LIKE 'pg_toast%'
				ORDER BY name";
		$result = $this->getConnection()->query($sql);

		while ($row = $result->fetch()) {
			// FIXME -- decide what info we need for sequences & then create a SequenceInfo object (if needed)
			$obj = new stdClass;
			$obj->name = $row['relname'];
			$obj->oid = $row['oid'];
			$this->sequences[strtoupper($row['relname'])] = $obj;
		}
	}

}

