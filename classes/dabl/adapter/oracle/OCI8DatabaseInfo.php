<?php
/*
 *  $Id: OCI8DatabaseInfo.php,v 1.11 2006/01/17 19:44:40 hlellelid Exp $
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
 * Oracle (OCI8) implementation of DatabaseInfo.
 * 
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.11 $
 * @package   creole.drivers.oracle.metadata
 */ 
class OCI8DatabaseInfo extends DatabaseInfo {
    
    private $schema;
           
    public function __construct(DBAdapter $conn) {
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
    
    public function getSchema() {
        return $this->schema;
    }
    
    /**
     * @throws SQLException
     * @return void
     */
    protected function initTables()
    {
        $sql = "SELECT table_name
            FROM all_tables
            WHERE owner = '{$this->schema}'";

		$result = $this->conn->query($sql);
        
        while ( $row = $result->fetch() )
		{
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
    protected function initSequences()
    {
        // throw new SQLException("MySQL does not support sequences natively.");
    }
        
}
