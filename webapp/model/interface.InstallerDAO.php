<?php
/**
 * Installer Data Access Object interface
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 *
 */
interface InstallerDAO {
    /**
     * Connection initiator for ThinkTank Installer
     * @param array $THINKTANK_CFG
     * @return
     */
    public function installerConnect($THINKTANK_CFG);
    
    /**
     * Public method of protected execute method
     */
    public function exec($query, $binds = array());
    
    /**
     * Get array of tables
     * @return array of table name
     */
    public function showTables();
    
    /**
     * Check table condition
     * 
     * @param string $tablename
     * @return array of table condition
     */
    public function checkTable($tablename);
    
    /**
     * Check if there is one admin user
     * 
     * @param string $tablename
     */
    public function isAdminExists($tablename);
    
    /**
     * Create an admin user
     * 
     * @param string $tablename
     * @param array $value
     */
    public function insertAdmin($tablename, $value);
    
    /**
     * Repair table
     * 
     * @param string $tablename
     */
    public function repairTable($tablename);
    
    /**
     * Describe table
     * 
     * @param string $tablename
     */
    public function describeTable($tablename);
    
    /**
     * Get list of table indexes
     * 
     * @param string $tablename
     */
    public function showIndex($tablename);
    
    /**
     * Examines / groups queries based on modified wp's dbDelta function. Examine string of queries from
     * specified array of tables
     * 
     * @param string $queries
     * @param array $tables array of tables
     * @return array Queries and update message.
     *               The array must contains key of queries and for_update
     */
    public function examineQueries($queries = '', $tables = array());
}
