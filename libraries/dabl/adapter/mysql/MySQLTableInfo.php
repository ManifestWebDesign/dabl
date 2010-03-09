<?php

/**
 * MySQL implementation of TableInfo.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.20 $
 * @package   creole.drivers.mysql.metadata
 */
class MySQLTableInfo extends TableInfo {

	/** Loads the columns for this table. */
	protected function initColumns() {
		// To get all of the attributes we need, we use
		// the MySQL "SHOW COLUMNS FROM $tablename" SQL.  We cannot
		// use the API functions (e.g. mysql_list_fields() because they
		// do not return complete information -- e.g. precision / scale, default
		// values).
		$res = $this->getDatabase()->getConnection()->query("SHOW COLUMNS FROM `" . $this->name . "`");

		$defaults = array();
		$nativeTypes = array();
		$precisions = array();

		while($row = $res->fetch()) {
			$name = $row['Field'];
			$is_nullable = ($row['Null'] == 'YES');
			$is_auto_increment = (strpos($row['Extra'], 'auto_increment') !== false);
			$size = null;
			$precision = null;
			$scale = null;

			if (preg_match('/^(\w+)[\(]?([\d,]*)[\)]?( |$)/', $row['Type'], $matches)) {
				//			colname[1]   size/precision[2]
				$nativeType = $matches[1];
				if ($matches[2]) {
					if ( ($cpos = strpos($matches[2], ',')) !== false) {
						$size = (int) substr($matches[2], 0, $cpos);
						$precision = $size;
						$scale = (int) substr($matches[2], $cpos + 1);
					} else {
						$size = (int) $matches[2];
					}
				}
			} elseif (preg_match('/^(\w+)\(/', $row['Type'], $matches)) {
				$nativeType = $matches[1];
			} else {
				$nativeType = $row['Type'];
			}
			//BLOBs can't have any default values in MySQL
			$default = preg_match('~blob|text~', $nativeType) ? null : $row['Default'];
			$this->columns[$name] = new ColumnInfo($this,
					$name,
					MySQLTypes::getType($nativeType),
					$nativeType,
					$size,
					$precision,
					$scale,
					$is_nullable,
					$default,
					$is_auto_increment,
					$row);
		}

		$this->colsLoaded = true;
	}

	/** Loads the primary key information for this table. */
	protected function initPrimaryKey() {
		// columns have to be loaded first
		if (!$this->colsLoaded) $this->initColumns();

		// Primary Keys
		$res = $this->getDatabase()->getConnection()->query("SHOW KEYS FROM `" . $this->name . "`");

		// Loop through the returned results, grouping the same key_name together
		// adding each column for that key.
		while($row = $res->fetch()) {
			// Skip any non-primary keys.
			if ($row['Key_name'] !== 'PRIMARY') {
				continue;
			}
			$name = $row["Column_name"];
			if (!isset($this->primaryKey)) {
				$this->primaryKey = new PrimaryKeyInfo($name, $row);
			}
			$this->primaryKey->addColumn($this->columns[$name]);
		}

		$this->pkLoaded = true;
	}

	/** Loads the indexes for this table. */
	protected function initIndexes() {
		// columns have to be loaded first
		if (!$this->colsLoaded) $this->initColumns();

		// Indexes
		$res = $this->getDatabase()->getConnection()->query("SHOW INDEX FROM `" . $this->name . "`");

		// Loop through the returned results, grouping the same key_name together
		// adding each column for that key.
		while($row = $res->fetch()) {
			$colName = $row["Column_name"];
			$name = $row["Key_name"];

			if($name == "PRIMARY") {
				continue;
			}

			if (!isset($this->indexes[$name])) {
				$isUnique = ($row["Non_unique"] == 0);
				$this->indexes[$name] = new IndexInfo($name, $isUnique, $row);
			}
			$this->indexes[$name]->addColumn($this->columns[$colName]);
		}

		$this->indexesLoaded = true;
	}

	/**
	 * Load foreign keys for supporting versions of MySQL.
	 * @author Tony Bibbs
	 */
	protected function initForeignKeys() {

		// First make sure we have supported version of MySQL:
		$res = $this->getDatabase()->getConnection()->query("SELECT VERSION()");
		$row = $res->fetch();

		// Yes, it is OK to hardcode this...this was the first version of MySQL
		// that supported foreign keys
		if ($row[0] < '3.23.44') {
			$this->fksLoaded = true;
			return;
		}

		// columns have to be loaded first
		if (!$this->colsLoaded) $this->initColumns();

		// Get the CREATE TABLE syntax
		$res = $this->getDatabase()->getConnection()->query("SHOW CREATE TABLE `" . $this->name . "`");
		$row = $res->fetch();

		// Get the information on all the foreign keys
		$regEx = '/FOREIGN KEY \(`([^`]*)`\) REFERENCES `([^`]*)` \(`([^`]*)`\)(.*)/';
		if (preg_match_all($regEx,$row[1],$matches)) {
			$tmpArray = array_keys($matches[0]);
			foreach ($tmpArray as $curKey) {
				$name = $matches[1][$curKey];
				$ftbl = $matches[2][$curKey];
				$fcol = $matches[3][$curKey];
				$fkey = $matches[4][$curKey];
				if (!isset($this->foreignKeys[$name])) {
					$this->foreignKeys[$name] = new ForeignKeyInfo($name);
					if ($this->database->hasTable($ftbl)) {
						$foreignTable = $this->database->getTable($ftbl);
					} else {
						$foreignTable = new MySQLTableInfo($this->database, $ftbl);
						$this->database->addTable($foreignTable);
					}
					if ($foreignTable->hasColumn($fcol)) {
						$foreignCol = $foreignTable->getColumn($fcol);
					} else {
						$foreignCol = new ColumnInfo($foreignTable, $fcol);
						$foreignTable->addColumn($foreignCol);
					}

					//typical for mysql is RESTRICT
					$fkactions = array(
							'ON DELETE'	=> ForeignKeyInfo::RESTRICT,
							'ON UPDATE'	=> ForeignKeyInfo::RESTRICT,
					);

					if ($fkey) {
						//split foreign key information -> search for ON DELETE and afterwords for ON UPDATE action
						foreach (array_keys($fkactions) as $fkaction) {
							$result = NULL;
							preg_match('/' . $fkaction . ' (' . ForeignKeyInfo::CASCADE . '|' . ForeignKeyInfo::SETNULL . ')/', $fkey, $result);
							if ($result && is_array($result) && isset($result[1])) {
								$fkactions[$fkaction] = $result[1];
							}
						}
					}
					$this->foreignKeys[$name]->addReference($this->columns[$name], $foreignCol, $fkactions['ON DELETE'], $fkactions['ON UPDATE']);
				}
			}
		}
		$this->fksLoaded = true;

	}

	protected function initVendorSpecificInfo() {
		$res = $this->getDatabase()->getConnection()->query("SHOW TABLE STATUS LIKE '" . $this->name . "'");
		$this->vendorSpecificInfo = $res->fetch();
		$this->vendorLoaded = true;
	}

}
