<?php
/**
 * Installer Data Access Object
 * The data access object for ThinkTank installation
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class InstallerMySQLDAO extends PDODAO implements InstallerDAO  {
    public function __construct() {
        $THINKUP_CFG = array();
        
        $args = func_get_args();
        if ( isset($args[0]) && !empty($args[0]) ) {
            $THINKUP_CFG = $args[0];
        }
        
        if(is_null(self::$PDO)) {
            $this->installerConnect($THINKUP_CFG);
        }
        
        if (isset($THINKUP_CFG['table_prefix'])) {
            $this->prefix = $THINKUP_CFG['table_prefix'];
        }
        if (isset($THINKUP_CFG['GMT_offset'])) {
            $this->gmt_offset = $THINKUP_CFG['GMT_offset'];
        }
    }
    
    /**
     * Connection initiator
     */
    public function installerConnect($THINKUP_CFG){
        if (is_null(self::$PDO)) {
            //set default db type to mysql if not set
            $db_type = $THINKUP_CFG['db_type'];
            if(! $db_type) { $db_type = 'mysql'; }
            $db_socket = $THINKUP_CFG['db_socket'];
            if ( !$db_socket) {
                $db_socket = '';
            } else {
                $db_socket=";unix_socket=".$db_socket;
            }
            $db_string = sprintf(
                "%s:dbname=%s;host=%s%s", 
                $db_type,
                $THINKUP_CFG['db_name'],
                $THINKUP_CFG['db_host'],
                $db_socket
            );
            self::$PDO = new PDO(
                $db_string,
                $THINKUP_CFG['db_user'],
                $THINKUP_CFG['db_password']
            );
            self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        return self::$PDO;
    }
    
    /**
     * Public method of protected execute method
     */
    public function exec($sql, $binds = array()) {
        return parent::execute($sql, $binds = array());
    }
    
    public function showTables() {
        $q = 'SHOW TABLES';
        $e = $this->execute($q);
        
        $tables = array();
        while ( $row = $e->fetch(PDO::FETCH_NUM) ) {
            $tables[] = $row[0];
        }
        $e->closeCursor();
        
        return $tables;
    }
    
    /**
     * Check table query
     * @param string $tablename
     * @return array Row that consists of key Message_text.
     *               If table exists and okay it must be array('Msg_text' => 'OK')
     */
    public function checkTable($tablename) {
        $q = "CHECK TABLE {$tablename}";
        $e = $this->execute($q);
        
        $row = $e->fetch(PDO::FETCH_ASSOC);
        $e->closeCursor();
        
        return $row;
    }
    
    /**
     * Check if admin user exists
     * @param string owners tablename
     * @return int return 0 if admin user doesn't exist
     */
    public function isAdminExists($tablename) {
        $q = "SELECT id FROM $tablename WHERE is_admin = 1";
        $e = $this->execute($q);
        
        $row = $e->fetch(PDO::FETCH_ASSOC);
        $e->closeCursor();
        
        return $this->getDataIsReturned($e);
    }
    
    /**
     * Insert an admin user into owners table
     * 
     * @param string $tablename Owners table with prefix
     * @param array $value Value to insert
     */
    public function insertAdmin($tablename, $value) {
        $email = '';
        if ( isset($value['email']) && !empty($value['email']) ) {
            $email = $value['email'];
        }
        
        $password = '';
        if ( isset($value['password']) && !empty($value['password']) ) {
            $password = $value['password'];
        }
        
        $full_name = '';
        if ( isset($value['full_name']) && !empty($value['full_name']) ) {
            $owner_name = $value['full_name'];
        }
        
        $q = "INSERT INTO $tablename ";
        $q .= " (`email`,`pwd`,`joined`,".
                "`activation_code`,`full_name`, `is_activated`, `is_admin`)";
        $q .= " VALUES ('" . $email . "','" . md5($password) . "'," .
                "now(),'','" . $full_name . "', 1, 1)";
                
        $e = $this->execute($q);
        
        return $this->getUpdateCount($e);
    }
    
    /**
     * Repair table
     * @param string $tablename Name of table to repair
     * @return array Row that consists of key Message_text.
     *               If table exists and okay it must be array('Msg_text' => 'OK')
     */
    public function repairTable($tablename) {
        $q = "REPAIR TABLE $tablename";
        
        $e = $this->execute($q);
        $row = $e->fetch(PDO::FETCH_ASSOC);
        $e->closeCursor();
        
        return $row;
    }
    
    /**
     * Describe table
     * @param string $tablename
     * @return array table descriptions that consist of following case-sensitive properties:
     *             - Field => name of field
     *             - Type => type of field
     *             - Null => is type allowed to be null
     *             - Default => Default value for field
     *             - Extra => such as auto_increment
     */
    public function describeTable($tablename) {
        $e = $this->execute("DESCRIBE $tablename");
        $tablefields = array();
        while ( $row = $e->fetch(PDO::FETCH_OBJ) ) {
            $tablefields[] = $row;
        }
        $e->closeCursor();
        
        return $tablefields;
    }
    
    public function showIndex($tablename) {
        $e = $this->execute("SHOW INDEX FROM $tablename");
        $tableindices = array();
        while ( $row = $e->fetch(PDO::FETCH_OBJ) ) {
            $tableindices[] = $row;
        }
        $e->closeCursor();
        
        return $tableindices;
    }
    
    function examineQueries($queries = '', $tables = array()) {
        $queries = explode(';', $queries);
        if ( $queries[count($queries)-1] == '' ) {
            array_pop($queries);
        }
        
        $cqueries = array(); // Creation Queries
        $iqueries = array(); // Insertion / Update Queries
        $for_update = array();
        
        // Create a tablename index for an array ($cqueries) of queries
        foreach($queries as $query) {
            if (preg_match("|CREATE TABLE ([^ ]*)|", $query, $matches)) {
                $cqueries[trim( strtolower($matches[1]), '`' )] = $query;
                $for_update[$matches[1]] = 'Created table '.$matches[1];
            }
            else if (preg_match("|CREATE DATABASE ([^ ]*)|", $query, $matches)) {
                array_unshift($cqueries, $query);
            }
            else if (preg_match("|INSERT INTO ([^ ]*)|", $query, $matches)) {
                $iqueries[] = $query;
            }
            else if (preg_match("|UPDATE ([^ ]*)|", $query, $matches)) {
                $iqueries[] = $query;
            }
            else {
                // Unrecognized query type
            }
        }
        
        // Check to see which tables and fields exist
        if ( !empty($tables) ) {
            $cfields = array();
            $indices = array();
            
            // For every table in the database
            foreach ($tables as $table) {
                // If a table query exists for the database table...
                if ( array_key_exists(strtolower($table), $cqueries) ) {
                    // Clear the field and index arrays
                    unset($cfields);
                    unset($indices);
                    // Get all of the field names in the query from between the parens
                    preg_match("|\((.*)\)|ms", $cqueries[strtolower($table)], $match2);
                    $qryline = trim($match2[1]);

                    // Separate field lines into an array
                    $flds = explode("\n", $qryline);

                    // For every field line specified in the query
                    foreach ($flds as $fld) {
                        // Extract the field name
                        preg_match("|^([^ ]*)|", trim($fld), $fvals);
                        $fieldname = trim( $fvals[1], '`' );

                        // Verify the found field name
                        $validfield = true;
                        switch (strtolower($fieldname)) {
                            case '':
                            case 'primary':
                            case 'index':
                            case 'fulltext':
                            case 'unique':
                            case 'key':
                              $validfield = false;
                              $indices[] = trim(trim($fld), ", \n");
                              break;
                        }
                        $fld = trim($fld);

                        // If it's a valid field, add it to the field array
                        if ($validfield) {
                            $cfields[strtolower($fieldname)] = trim($fld, ", \n");
                        }
                    }

                    // Fetch the table column structure from the database
                    $tablefields = $this->describeTable($table);
                    
                    // For every field in the table
                    foreach ($tablefields as $tablefield) {
                        // If the table field exists in the field array...
                        if (array_key_exists(strtolower($tablefield->Field), $cfields)) {
                            // Get the field type from the query
                            preg_match("|".$tablefield->Field." ([^ ]*( unsigned)?)|i", 
                                      $cfields[strtolower($tablefield->Field)], $matches);
                            $fieldtype = $matches[1];
                            
                            // Is actual field type different from the field type in query?
                            if ($tablefield->Type != $fieldtype) {
                                // Add a query to change the column type
                                $cqueries[] = "ALTER TABLE {$table} CHANGE COLUMN {$tablefield->Field} " .
                                               $cfields[strtolower($tablefield->Field)];
                                $for_update[$table.'.'.$tablefield->Field] = "Changed type of ".
                                                                             "{$table}.{$tablefield->Field} " .
                                                                             "from {$tablefield->Type} to {$fieldtype}";
                            }

                            // Get the default value from the array
                              //echo "{$cfields[strtolower($tablefield->Field)]}<br>";
                            if (preg_match("| DEFAULT '(.*)'|i", $cfields[strtolower($tablefield->Field)], $matches)) {
                                $default_value = $matches[1];
                                if ($tablefield->Default != $default_value) {
                                    // Add a query to change the column's default value
                                    $cqueries[] = "ALTER TABLE {$table} ALTER COLUMN {$tablefield->Field} " .
                                                  "SET DEFAULT '{$default_value}'";
                                    $for_update[$table.'.'.$tablefield->Field] = "Changed default value of " .
                                                                                 "{$table}.{$tablefield->Field} from " .
                                                                                 "{$tablefield->Default} to " .
                                                                                  $default_value;
                                }
                            }

                            // Remove the field from the array (so it's not added)
                            unset($cfields[strtolower($tablefield->Field)]);
                            
                        } else {
                          // This field exists in the table, but not in the creation queries?
                        }
                    }

                    // For every remaining field specified for the table
                    foreach ($cfields as $fieldname => $fielddef) {
                        // Push a query line into $cqueries that adds the field to that table
                        $cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
                        $for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
                    }

                    // Index stuff goes here
                    // Fetch the table index structure from the database
                    $tableindices = $this->showIndex($table);
                    if ( !empty($tableindices) ) {
                        // Clear the index array
                        unset($index_ary);

                        // For every index in the table
                        foreach ($tableindices as $tableindex) {
                            // Add the index to the index data array
                            $keyname = $tableindex->Key_name;
                            $index_ary[$keyname]['columns'][] = array(
                              'fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part
                            );
                            $index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0) ? true : false;
                            $index_ary[$keyname]['fulltext'] = ($tableindex->Index_type == 'FULLTEXT') ? true : false;
                        }
                      
                        // For each actual index in the index array
                        foreach ($index_ary as $index_name => $index_data) {
                            // Build a create string to compare to the query
                            $index_string = '';
                            if ($index_name == 'PRIMARY') {
                                $index_string .= 'PRIMARY ';
                            } else if ($index_data['unique']) {
                                $index_string .= 'UNIQUE ';
                            } else if ($index_data['fulltext']) {
                                $index_string .= 'FULLTEXT ';
                            } 
                            $index_string .= 'KEY ';
                            if ($index_name != 'PRIMARY') {
                                $index_string .= $index_name;
                            }
                            $index_columns = '';
                            // For each column in the index
                            foreach ($index_data['columns'] as $column_data) {
                                if ($index_columns != '') $index_columns .= ',';
                                // Add the field to the column list string
                                $index_columns .= $column_data['fieldname'];
                                if ($column_data['subpart'] != '') {
                                    $index_columns .= '('.$column_data['subpart'].')';
                                }
                            }
                            
                            // Add the column list to the index create string
                            $index_string .= ' ('.$index_columns.')';
                            if( !(($aindex = array_search($index_string, $indices)) === false) ) {
                                unset($indices[$aindex]);
                            }
                        }
                    }
                  
                    // For every remaining index specified for the table
                    if ( isset($indices) && !empty($indices) ) {
                        foreach ( (array) $indices as $index ) {
                            // Push a query line into $cqueries that adds the index to that table
                            $cqueries[] = "ALTER TABLE {$table} ADD $index";
                            $for_update[$table.'.'.$fieldname] = 'Added index '.$table.' '.$index;
                        }
                    }

                    // Remove the original table creation query from processing
                    unset($cqueries[strtolower($table)]);
                    unset($for_update[strtolower($table)]);
                } else {
                    // This table exists in the database, but not in the creation queries?
                }
            }
        }
        
        $allqueries = array_merge($cqueries, $iqueries);
        return array('queries' => $allqueries, 'for_update' => $for_update);
    }
}
