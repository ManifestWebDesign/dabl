<?php
/*
 *  $Id: MSSQLDatabaseInfo.php,v 1.11 2006/01/17 19:44:39 hlellelid Exp $
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
	protected function initTables()
	{
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
	protected function initSequences()
	{
		// there are no sequences -- afaik -- in MSSQL.
	}
		
}
