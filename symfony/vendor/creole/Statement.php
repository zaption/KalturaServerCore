<?php
/*
 *  $Id: Statement.php,v 1.17 2004/03/20 04:16:49 hlellelid Exp $
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
 * Class that represents a SQL statement.
 * 
 * This class is very generic and has no driver-specific implementations.  In fact,
 * it wouldn't be possible to have driver-specific classes, since PHP doesn't support
 * multiple inheritance.  I.e. you couldn't have MySQLPreparedStatement that extended
 * both the abstract PreparedStatement class and the MySQLStatement class.  In Java
 * this isn't a concern since PreparedStatement is an interface, not a class.
 *
 * 
 * @author   Hans Lellelid <hans@xmpl.org>
 * @version  $Revision: 1.17 $
 * @package  creole
 */
interface Statement {    
    
    /**
     * Sets the maximum number of rows to return from db.
     * This will affect the SQL if the RDBMS supports native LIMIT; if not,
     * it will be emulated.  Limit only applies to queries (not update sql).
     * @param int $v Maximum number of rows or 0 for all rows.
     * @return void
     */
    public function setLimit($v);
    
    /**
     * Returns the maximum number of rows to return or 0 for all.
     * @return int
     */
    public function getLimit();
    
    /**
     * Sets the start row.
     * This will affect the SQL if the RDBMS supports native OFFSET; if not,
     * it will be emulated. Offset only applies to queries (not update) and 
     * only is evaluated when LIMIT is set!
     * @param int $v
     * @return void
     */ 
    public function setOffset($v);
    
    /**
     * Returns the start row.
     * Offset only applies when Limit is set!
     * @return int
     */
    public function getOffset();
    
    /**
     * Free resources associated with this statement.
     * Some drivers will need to implement this method to free
     * database result resources. 
     * 
     * @return void
     */
    public function close();
    
    /**
     * Generic execute() function has to check to see whether SQL is an update or select query.
     * 
     * If you already know whether it's a SELECT or an update (manipulating) SQL, then use
     * the appropriate method, as this one will incurr overhead to check the SQL.
     * 
     * @param int $fetchmode Fetchmode (only applies to queries).
     * @return boolean True if it is a result set, false if not or if no more results (this is identical to JDBC return val).
     * @throws SQLException
     */
    public function execute($sql, $fetchmode = null);

    /**
     * Get result set.
     * This assumes that the last thing done was an executeQuery() or an execute()
     * with SELECT-type query.
     *
     * @return RestultSet (or null if none)
     */
    public function getResultSet();

    /**
     * Get update count.
     *
     * @return int Number of records affected, or <code>null</code> if not applicable.
     */
    public function getUpdateCount();        

    /**
     * Executes the SQL query in this PreparedStatement object and returns the resultset generated by the query.
     * 
     * @param string $sql This method may optionally be called with the SQL statement.
     * @param int $fetchmode The mode to use when fetching the results (e.g. ResultSet::FETCHMODE_NUM, ResultSet::FETCHMODE_ASSOC).
     * @return object Creole::ResultSet
     * @throws SQLException if a database access error occurs.
     */
    public function executeQuery($sql, $fetchmode = null);
    
    /**
     * Executes the SQL INSERT, UPDATE, or DELETE statement in this PreparedStatement object.
     * 
     * @param string $sql This method may optionally be called with the SQL statement.
     * @return int Number of affected rows (or 0 for drivers that return nothing).
     * @throws SQLException if a database access error occurs.
     */
    public function executeUpdate($sql);
    
    /**
     * Gets next result set (if this behavior is supported by driver).
     * Some drivers (e.g. MSSQL) support returning multiple result sets -- e.g.
     * from stored procedures.
     *
     * This function also closes any current restult set.
     *
     * Default behavior is for this function to return false.  Driver-specific
     * implementations of this class can override this method if they actually
     * support multiple result sets.
     * 
     * @return boolean True if there is another result set, otherwise false.
     */
    public function getMoreResults();
     
    /**
     * Gets the db Connection that created this statement.
     * @return Connection
     */
    public function getConnection();
    
}
