<?php

/**
 * MySQL implementation of TableInfo.
 *
 * @author	Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.8 $
 * @package   creole.drivers.sqlite.metadata
 */
class SQLiteTableInfo extends TableInfo {

	/** Loads the columns for this table. */
	protected function initColumns() {
		// To get all of the attributes we need, we'll actually do
		// two separate queries.  The first gets names and default values
		// the second will fill in some more details.

		$sql = 'PRAGMA table_info('.$this->name.')';

		$result = $this->getDatabase()->getConnection()->query($sql);

		while($row = $result->fetch()) {

			$name = $row['name'];

			$fulltype = $row['type'];
			$size = null;
			$precision = null;
			$scale = null;

			if (preg_match('/^([^\(]+)\(\s*(\d+)\s*,\s*(\d+)\s*\)$/', $fulltype, $matches)) {
				$type = $matches[1];
				$precision = $matches[2];
				$scale = $matches[3]; // aka precision
			} elseif (preg_match('/^([^\(]+)\(\s*(\d+)\s*\)$/', $fulltype, $matches)) {
				$type = $matches[1];
				$size = $matches[2];
			} else {
				$type = $fulltype;
			}

			$not_null = $row['notnull'];
			$is_nullable = !$not_null;

			$default_val = $row['dflt_value'];

			$this->columns[$name] = new ColumnInfo($this, $name, SQLiteTypes::getType($type), $type, $size, $precision, $scale, $is_nullable, $default_val);

			if (($row['pk'] == 1) || (strtolower($type) == 'integer primary key')) {
				if ($this->primaryKey === null) {
					$this->primaryKey = new PrimaryKeyInfo($name);
				}
				$this->primaryKey->addColumn($this->columns[ $name ]);
			}

		}

		$this->colsLoaded = true;
	}

	/** Loads the primary key information for this table. */
	protected function initPrimaryKey() {
		// columns have to be loaded first
		if (!$this->colsLoaded) $this->initColumns();
		// keys are loaded by initColumns() in this class.
		$this->pkLoaded = true;
	}

	/** Loads the indexes for this table. */
	protected function initIndexes() {
		// columns have to be loaded first
		if (!$this->colsLoaded) $this->initColumns();

		$sql = 'PRAGMA index_list('.$this->name.')';
		$result = $this->getDatabase()->getConnection()->query($sql);

		while($row = $result->fetch()) {
			$name = $row['name'];
			$this->indexes[$name] = new IndexInfo($name);

			// get columns for that index
			$res2 = $this->getDatabase()->getConnection()->query('PRAGMA index_info('.$name.')');
			while($row2 = $res2->fetch()) {
				$colname = $row2['name'];
				$this->indexes[$name]->addColumn($this->columns[ $colname ]);
			}
		}

		$this->indexesLoaded = true;
	}

	/** Load foreign keys (unsupported in SQLite). */
	protected function initForeignKeys() {

		// columns have to be loaded first
		if (!$this->colsLoaded) $this->initColumns();

		// No fkeys in SQLite

		$this->fksLoaded = true;
	}

}
