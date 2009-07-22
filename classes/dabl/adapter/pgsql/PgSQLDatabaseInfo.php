<?php
/*
 *  $Id: PgSQLDatabaseInfo.php,v 1.11 2006/01/17 19:44:40 hlellelid Exp $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://creole.phpdb.org>.
 */

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
	protected function initTables()
	{
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
	protected function initSequences()
	{
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

