<?php
/**
 * Installer Model
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 *
 */
class Installer {
/**
 * Singleton instance of Installer
 * @var mixed
 * @access private
 * @todo Make sure the instance records unique id (something like IP
 *       or mac address) which identifies executor
 */
  private static $__instance = null;

/**
 * Stores error messages.
 * 
 * @var array
 * @access private
 */
  private static $__errorMessages = array();
  
  const ERROR_FILE_NOT_FOUND = 1;
  const ERROR_CLASS_NOT_FOUND = 2;
  const ERROR_DB_CONNECT = 3;
  const ERROR_DB_SELECT = 4;
  const ERROR_DB_TABLES_EXIST = 5;
  const ERROR_SITE_NAME = 6;
  const ERROR_SITE_EMAIL = 7;
  const ERROR_CONFIG_FILE_MISSING = 8;
  const ERROR_CONFIG_SAMPLE_MISSING = 9;
  const ERROR_CONFIG_SOURCE_ROOT_PATH = 10;
  const ERROR_CONFIG_SMARTY_PATH = 11;
  const ERROR_CONFIG_LOG_LOCATION = 12;
  const ERROR_TYPE_MISMATCH = 13;
  const ERROR_INSTALL_PATH_EXISTS = 14;
  const ERROR_INSTALL_NOT_COMPLETE = 15;
  const ERROR_INSTALL_COMPLETE = 16;
  const ERROR_REPAIR_CONFIG = 17;
  const ERROR_REQUIREMENTS = 18;
  
/**
 * Stores current version of ThinkTank
 */
  private static $__currentVersion;

/**
 * Stores required version of each apps
 */
  private static $__requiredVersion;
  
/**
 *  Smarty Instance
 */
  private static $__view;

/**
 * List of ThinkTank tables
 *
 * @var array
 */  
  public static $tables = array(
    'follows', 'instances', 'links', 'owners', 'owner_instances',
    'plugins', 'plugin_options', 'posts', 'post_errors', 'users',
    'user_errors'
  );

/**
 * Result from SHOW TABLES
 * @var array
 */
  public static $showTables;
  
/**
 * Database object
 */
  public static $db;
  
/**
 * Temporary var. Helper
 * when hold temporary var between method calls
 */
  public static $tmp_var;
  
/**
 * Private constructor, so can't be accessed
 * from outside
 */
  private function __construct() {}

/**
 * Get Installer instance
 * @return object $this->__instance Installer instance
 */
  public static function getInstance() {
    if ( self::$__instance == null ) {
      self::$__instance = new Installer();
      
      // use lazy loading
      if ( !class_exists('Loader', FALSE) ) {
        require_once 'class.Loader.php';
      }
      Loader::register();
      
      // instantiate Smarty
      self::$__view = new Smarty();
      self::$__view->template_dir = array(THINKTANK_WEBAPP_PATH . 'view');
      self::$__view->compile_dir = THINKTANK_WEBAPP_PATH . 'view' . DS . 'compiled_view' . DS;
      self::$__view->caching = FALSE;
      self::$__view->assign('base_url', THINKTANK_BASE_URL);
      self::$__view->assign('favicon', THINKTANK_BASE_URL . 'assets/img/favicon.ico');
      
      // get required version of php and mysql
      // and set current version
      require_once (THINKTANK_WEBAPP_PATH . 'install' . DS . 'version.php');
      
      self::$__requiredVersion = array(
        'php' => $THINKTANK_VERSION_REQUIRED['php'],
        'mysql' => $THINKTANK_VERSION_REQUIRED['mysql']
      );
      self::$__currentVersion = $THINKTANK_VERSION;
    }
    
    return self::$__instance;
  }
  
/**
 * Check PHP version
 * 
 * @access public
 * @return bool Requirements met
 */
  function checkVersion() {
    $ret = false;
    $ret = version_compare( phpversion(), self::$__requiredVersion['php'], '>=' );
    
    return $ret;
  }

/**
 * Check GD and cURL
 * @return array
 */
  function checkDependency() {
    $ret = array('curl' => false, 'gd' => false);
    // check curl
    if ( extension_loaded('curl') && function_exists('curl_exec') ) {
      $ret['curl'] = true;
    }
    
    // check GD
    if ( extension_loaded('gd') && function_exists('gd_info') ) {
      $ret['gd'] = true;
    }
    
    return $ret;
  }

/**
 * Check if log and template directories are writeable
 * @access public
 * @return array
 */  
  function checkPermission() {
    $ret = array(
      'logs' => false, 'compiled_view' => false, 'cache' => false
    );
    
    if ( is_writable(THINKTANK_ROOT_PATH . 'logs') ) {
      $ret['logs'] = true;
    }
    
    if ( is_writable(self::$__view->compile_dir) ) {
      $ret['compiled_view'] = true;
    }
    
    if ( is_writable(self::$__view->compile_dir . 'cache') ) {
      $ret['cache'] = true;
    }
    
    return $ret;
  }

/**
 * Check path existent
 * @param array $config
 */
  function checkPath($config) {
    // check if $THINKTANK_CFG related to path exists
    if ( !is_dir($config['source_root_path']) ) {
      throw new InstallerError(
        "<p>ThinkTank's source root directory is not found</p>",
        self::ERROR_CONFIG_SOURCE_ROOT_PATH
      );
    }
    if ( !is_dir($config['smarty_path']) ) {
      throw new InstallerError(
        "<p>ThinkTank's smarty directory is not found</p>",
        self::ERROR_CONFIG_SMARTY_PATH
      );
    }
    if ( !is_dir(substr($config['log_location'], 0, -11)) ) {
      throw new InstallerError(
        "<p>ThinkTank log directory is not found</p>",
        self::ERROR_CONFIG_LOG_LOCATION
      );
    }
    
    return true;
  }

/**
 * Check all requirements on step 1
 * Check PHP version, cURL, 
 * @access public
 * @return bool
 */  
  function checkStep1() {
    $version_compat = $this->checkVersion();
    
    $lib_depends = $this->checkDependency();
    $lib_depends_ret = true;
    foreach ($lib_depends as $lib) {
      $lib_depends_ret = $lib_depends_ret && $lib;
    }
    
    $writeable_permission = $this->checkPermission();
    $writeable_permission_ret = true;
    foreach ($writeable_permission as $permission) {
      $writeable_permission_ret = $writeable_permission_ret && $permission;
    }
    
    return ($version_compat && $lib_depends_ret && $writeable_permission_ret);
  }

/**
 * Set db property
 * @param array $config
 */  
  function setDb($config) {
    self::$db = new Database($config);
    try {
      $c = self::$db->getConnection();
    } catch (Exception $e) {
      $e->getMessage();
    }
    
    if (!$c) {
      throw new InstallerError(
        '<p>Failed establishing database connection. Probably either your username, ' .
        '<code>' . $config['db_user'] . '</code>, and password, <code>' . $config['db_password'] .
        '</code>, you have provided are incorrect or we can\'t connect the ' .
        'database server at <code>' . $config['db_host'] . '</code>' .
        '</p>' ,
        self::ERROR_DB_CONNECT
      );
    }
    
    return self::$db;
  }

/**
 * Get SHOW TABLES at current $db
 * @param array $config
 * @return array tables
 */
  function showTables($config = null) {
    if ( is_array(self::$showTables) && !empty(self::$showTables) ) {
      return self::$showTables;
    }
    
    if ( !self::$db ) {
      self::setDb($config);
    }
    try {
      $c = self::$db->getConnection();
    } catch (Exception $e) {
      $e->getMessage();
    }
    
    $sql_result = self::$db->exec('SHOW TABLES');
    $tables = array();
    while ( $row = @mysql_fetch_array($sql_result) ) {
      $tables[] = $row[0];
    }
    self::$showTables = $tables;
    
    return self::$showTables;
  }

/**
 * Check database
 * @param array $params database credentials
 * @return mixed
 */
  function checkDb($params) {
    $c = @mysql_connect($params['db_host'], $params['db_user'], $params['db_password'], true);
    if (!$c) {
      throw new InstallerError(
        '<p>Failed establishing database connection. Probably either your username, ' .
        '<code>' . $params['db_user'] . '</code>, and password, <code>' . $params['db_password'] .
        '</code>, you have provided are incorrect or we can\'t connect the ' .
        'database server at <code>' . $params['db_host'] . '</code>' .
        '</p>' ,
        self::ERROR_DB_CONNECT
      );
    }
    
    if (!@mysql_select_db($params['db_name'], $c)) {
      mysql_close($c);
      throw new InstallerError(
        "<p>We were able to connect to the database server (which means " .
        "your username and password is okay) but not able to select the <code>" . 
        $params['db_name'] . "</code> database.</p> ", 
        self::ERROR_DB_SELECT
      );
    }
    mysql_close($c);
    
    return true;
  }

/**
 * Check table existent, see also self::isThinkTankTablesExist()
 * @param array $config
 * @return mixed throw error when table exists
 *         return true when thinktank tables don't exist
 */
  function checkTable($config) {
    if ( !self::$showTables ) {
      self::showTables($config);
    }
    
    if ( count(self::$showTables) > 0 ) { // database contains tables
      foreach ( self::$tables as $table ) {
        if ( in_array($config['table_prefix'] . $table, self::$showTables) ) {
          // database contains thinktank table
          // TODO: when table already exists, ask for repairing
          throw new InstallerError(
            "<p><strong>Ups!</strong> ThinkTank tables exist. If you're considering ".
            "to install ThinkTank from scratch please clear out ThinkTank tables in ".
            "<code>{$config['db_name']}</code> database. If you're planning to ".
            "repair your table click <a href=\"" . THINKTANK_BASE_URL . "install/repair.php?db=1\">here</a>.</p>",
            self::ERROR_DB_TABLES_EXIST
          );
        }
      }
    }
    
    return true;
  }

/**
 * Check if thinktank table exists and its okay
 * The different between self::checkTable is, self::isThinkTankTablesExist doesn't
 * throw an error and returns boolean value. This method should be called when
 * we're not in installation steps
 * @param array $config
 * @return bool true when ThinkTank tables Exist
 */  
  function isThinkTankTablesExist($config) {
    if ( !self::$showTables ) {
      self::showTables($config);
    }
    
    $total_table_found = 0;
    if ( count(self::$showTables) > 0 ) { // database contains tables
      foreach ( self::$tables as $table ) {
        if ( in_array($config['table_prefix'] . $table, self::$showTables) ) {
          $total_table_found++;
        }
      }
    }
    
    if ( $total_table_found == count(self::$tables) ) {
      return true;
    } else {
      return false;
    }
  }

/** 
 * check if $tablename Ok
 * @param string $tablename Table name
 * @return bool true if $tablename ok
 */
  function isTableOk($tablename) {
    global $THINKTANK_CFG;
    if ( !self::$db ) {
      self::setDb($THINKTANK_CFG);
    }
    
    $q = "CHECK TABLE {$THINKTANK_CFG['table_prefix']}{$tablename};";
    $sql_result = self::$db->exec($q);
    $row = @mysql_fetch_array($sql_result);
    
    $okay = false;
    if ( isset($row['Msg_text']) && $row['Msg_text'] == 'OK' ) {
      $okay = true;
    } else {
      self::$tmp_var = $row['Msg_text'];
    }
    mysql_free_result($sql_result);
    
    if ( $okay ) {
      return true;
    } else {
      return false;
    }
  }

/**
 * Check is there's at least one admin user
 * @return bool
 */  
  function isAdminExists() {
    global $THINKTANK_CFG;
    if ( !self::$db ) {
      self::setDb($THINKTANK_CFG);
    }
    
    // check if table owners exists
    if ( !self::isTableOk('owners') ) {
      return false;
    }
    
    $q = "SELECT id FROM {$THINKTANK_CFG['table_prefix']}owners WHERE is_admin = 1 LIMIT 1;";
    $sql_result = self::$db->exec($q);
    $exists = mysql_num_rows($sql_result);
    
    mysql_free_result($sql_result);
    
    if ( $exists ) {
      return true;
    } else {
      return false;
    }
  }

/**
 * Check if ThinkTank is already installed
 * @return bool true when ThinkTank is already installed
 */  
  function isThinkTankInstalled() {
    // check if file config present
    $config_file_exists = false;
    $config_file = THINKTANK_WEBAPP_PATH . 'config.inc.php';
    // check if we have made config.inc.php
    if ( file_exists($config_file) ) {
      require_once $config_file;
      global $THINKTANK_CFG;
      $config_file_exists = true;
    } else {
      self::$__errorMessages['config_file'] = "Config file doesn't exist.";
      return false;
    }
    
    // check version is met
    $version_met = self::checkStep1();
    if ( !$version_met ) {
      self::$__errorMessages['requirements'] = "Requirements are not met. " .
        "Make sure your PHP version >= " . self::$__requiredVersion['php'] .
        " and you have cURL and GD extension installed.";
      return false;
    }
    
    // database is okay
    $db_check = self::checkDb($THINKTANK_CFG);
    
    // table present
    $table_present = true;
    if ( !self::isThinkTankTablesExist($THINKTANK_CFG) ) {
      self::$__errorMessages['table'] = 'ThinkTank table is not fully available.';
      $table_present = false;
    }
    
    // one owner exists and has is_admin = 1
    $admin_exists = true;
    if ( !self::isAdminExists($THINKTANK_CFG) ) {
      self::$__errorMessages['admin'] = "There's no admin user.";
      $admin_exists = false;
    }
    
    return ($version_met && $db_check && $table_present && $admin_exists);
  }

/**
 * populate table / execute queries in queries.php
 * @param array $config database configuration
 * @param bool $verbose database configuration
 * @return mixed
 */  
  function populateTables($config, $verbose = false) {
    $table = array();
    foreach (self::$tables as $t) {
      $table[$t] = $config['table_prefix'] . $t;
    }
    $query_file = THINKTANK_WEBAPP_PATH . 'install' . DS . 'queries.php';
    if ( !file_exists($query_file) ) {
      throw new InstallerError(
        "File <code>$query_file</code> is not found.", 
        self::ERROR_FILE_NOT_FOUND);
    }
    require_once $query_file;
    
    $install_queries = self::examineQueries($install_queries);
    foreach ($install_queries['queries'] as $query) {
      try {
        self::$db->exec($query);
      } catch (InstallerError $e) {
        $e->getMessage();
      }
    }
    
    if ( $verbose ) {
      return $install_queries['for_update'];
    } else {
      return true;
    }
  }
  
  function repairTables($THINKTANK_CFG) {
    if ( !self::$showTables ) {
      self::showTables($THINKTANK_CFG);
    }
    
    // check total tables is the same with the default defined
    $total_table_found = 0;
    if ( count(self::$showTables) > 0 ) { // database contains tables
      foreach ( self::$tables as $table ) {
        if ( in_array($THINKTANK_CFG['table_prefix'] . $table, self::$showTables) ) {
          $total_table_found++;
        }
      }
    }
    $messages = array();
    
    // show missing table
    $total_table_not_found = count(self::$tables) - $total_table_found;
    if ( $total_table_not_found > 0 ) {
      $messages['missing_tables']  = "<p>There are <strong class=\"not_okay\">$total_table_not_found missing tables</strong>. ";
      $messages['missing_tables'] .= "ThinkTank will attempt to create missing tables and alter existing tables if " .
                                     " something is missing&hellip;";
      $messages['missing_tables'] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">Create and alter some tables&hellip;</span>";
      $queries_logs = self::populateTables($THINKTANK_CFG, true);
      if ( !empty($queries_logs) ) {
        foreach ( $queries_logs as $log ) {
          $messages['missing_tables'] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">$log</span>";
        }
      }
    } else {
      $messages['table_complete'] = '<p>Your ThinkTank tables are <strong class="okay">complete</strong>.</p>';
    }
    
    // does checking on tables that exist
    $okay = true;
    $table = '';
    foreach (self::$tables as $t) {
      $table = $THINKTANK_CFG['table_prefix'] . $t;
      if ( self::isTableOk($table) ) {        
        $messages[$t] = "<p>The <code>$table</code> table is <strong class=\"okay\">okay</strong>.</p>";
      } else {
        $messages[$t]  = "<p>The <code>$table</code> table is <strong class=\"not_okay\">not okay</strong>. ";
        $messages[$t] .= "It is reporting the following error: <code>".self::$tmp_var."</code>. ";
        $messages[$t] .= "ThinkTank will attempt to repair this table&hellip;";
        
        // repairs table that not okay
        $q = "REPAIR TABLE $table;";
       
        $sql_result = self::$db->exec($q);
        $row = @mysql_fetch_array($sql_result);
        
        if ( isset($row['Msg_text']) && $row['Msg_text'] == 'OK' ) {
          $messages[$t] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">Sucessfully repaired the $table table.</span>";
        } else { // failed to repair the table
          $messages[$t] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">Failed to repair the $table table. " .
                           "Error: {$row['Msg_text']}</span><br />";
          self::$__errorMessages[$t] = "<p class=\"repair_log\">Failed to repair the $table table.</p>";
        }
        
        $messages[$t] .= "</p>";
        mysql_free_result($sql_result);
      }
    }
    
    return $messages;
  }
  
/**
 * Modified wp's dbDelta function
 * Examines / groups queries
 * @param string $queries
 * @return array
 */
  function examineQueries($queries = '') {
    if ( !is_string($queries) || empty($queries) ) {
      throw new InstallerError(
        'Installer::examineQueries($queries), parameter $queries only accept string',
        self::ERROR_TYPE_MISMATCH
      );
    }
    
    $queries = explode(';', $queries);
    if ( $queries[count($queries)-1] == '' ) {
      array_pop($queries);
    }
    
    $cqueries = array(); // Creation Queries
    $iqueries = array(); // Insertion / Update Queries
    $for_update = array();
    
    // Create a tablename index for an array ($cqueries) of queries
    foreach($queries as $query) {
      if(preg_match("|CREATE TABLE ([^ ]*)|", $query, $matches)) {
        $cqueries[trim( strtolower($matches[1]), '`' )] = $query;
        $for_update[$matches[1]] = 'Created table '.$matches[1];
      }
      else if(preg_match("|CREATE DATABASE ([^ ]*)|", $query, $matches)) {
        array_unshift($cqueries, $query);
      }
      else if(preg_match("|INSERT INTO ([^ ]*)|", $query, $matches)) {
        $iqueries[] = $query;
      }
      else if(preg_match("|UPDATE ([^ ]*)|", $query, $matches)) {
        $iqueries[] = $query;
      }
      else {
        // Unrecognized query type
      }
    }
    
    // Check to see which tables and fields exist
    if ($tables = self::showTables()) {
      // For every table in the database
      foreach($tables as $table) {
        // If a table query exists for the database table...
        if( array_key_exists(strtolower($table), $cqueries) ) {
          // Clear the field and index arrays
          unset($cfields);
          unset($indices);
          // Get all of the field names in the query from between the parens
          preg_match("|\((.*)\)|ms", $cqueries[strtolower($table)], $match2);
          $qryline = trim($match2[1]);

          // Separate field lines into an array
          $flds = explode("\n", $qryline);

          //echo "<hr/><pre>\n".print_r(strtolower($table), true).":\n".print_r($cqueries, true)."</pre><hr/>";

          // For every field line specified in the query
          foreach($flds as $fld) {
            // Extract the field name
            preg_match("|^([^ ]*)|", trim($fld), $fvals);
            $fieldname = trim( $fvals[1], '`' );

            // Verify the found field name
            $validfield = true;
            switch(strtolower($fieldname))
            {
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
            if($validfield) {
              $cfields[strtolower($fieldname)] = trim($fld, ", \n");
            }
          }

          // Fetch the table column structure from the database
          $sql_result = self::$db->exec("DESCRIBE {$table};");
          $tablefields = array();
          while ( $row = @mysql_fetch_object($sql_result) ) {
            $tablefields[] = $row;
          }

          // For every field in the table
          foreach($tablefields as $tablefield) {
            // If the table field exists in the field array...
            if(array_key_exists(strtolower($tablefield->Field), $cfields)) {
              // Get the field type from the query
              preg_match("|".$tablefield->Field." ([^ ]*( unsigned)?)|i", $cfields[strtolower($tablefield->Field)], $matches);
              $fieldtype = $matches[1];
              
              // Is actual field type different from the field type in query?
              if($tablefield->Type != $fieldtype) {
                // Add a query to change the column type
                $cqueries[] = "ALTER TABLE {$table} CHANGE COLUMN {$tablefield->Field} " . $cfields[strtolower($tablefield->Field)];
                $for_update[$table.'.'.$tablefield->Field] = "Changed type of {$table}.{$tablefield->Field} from {$tablefield->Type} to {$fieldtype}";
              }

              // Get the default value from the array
                //echo "{$cfields[strtolower($tablefield->Field)]}<br>";
              if(preg_match("| DEFAULT '(.*)'|i", $cfields[strtolower($tablefield->Field)], $matches)) {
                $default_value = $matches[1];
                if($tablefield->Default != $default_value)
                {
                  // Add a query to change the column's default value
                  $cqueries[] = "ALTER TABLE {$table} ALTER COLUMN {$tablefield->Field} SET DEFAULT '{$default_value}'";
                  $for_update[$table.'.'.$tablefield->Field] = "Changed default value of {$table}.{$tablefield->Field} from {$tablefield->Default} to {$default_value}";
                }
              }

              // Remove the field from the array (so it's not added)
              unset($cfields[strtolower($tablefield->Field)]);
            }
            else {
              // This field exists in the table, but not in the creation queries?
            }
          }

          // For every remaining field specified for the table
          foreach($cfields as $fieldname => $fielddef) {
            // Push a query line into $cqueries that adds the field to that table
            $cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
            $for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
          }

          // Index stuff goes here
          // Fetch the table index structure from the database
          $sql_result = self::$db->exec("SHOW INDEX FROM {$table};");
          $tableindices = array();
          while ( $row = @mysql_fetch_object($sql_result) ) {
            $tableindices[] = $row;
          }
          if($tableindices) {
            // Clear the index array
            unset($index_ary);

            // For every index in the table
            foreach($tableindices as $tableindex) {
              // Add the index to the index data array
              $keyname = $tableindex->Key_name;
              $index_ary[$keyname]['columns'][] = array('fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part);
              $index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0)?true:false;
              $index_ary[$keyname]['fulltext'] = ($tableindex->Index_type == 'FULLTEXT')?true:false;
            }
            
            // For each actual index in the index array
            foreach($index_ary as $index_name => $index_data) {
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
              if($index_name != 'PRIMARY') {
                $index_string .= $index_name;
              }
              $index_columns = '';
              // For each column in the index
              foreach($index_data['columns'] as $column_data) {
                if($index_columns != '') $index_columns .= ',';
                // Add the field to the column list string
                $index_columns .= $column_data['fieldname'];
                if($column_data['subpart'] != '') {
                  $index_columns .= '('.$column_data['subpart'].')';
                }
              }
              // Add the column list to the index create string
              $index_string .= ' ('.$index_columns.')';
              if(!(($aindex = array_search($index_string, $indices)) === false)) {
                unset($indices[$aindex]);
                //echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br />Found index:".$index_string."</pre>\n";
              }
              //else echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br /><b>Did not find index:</b>".$index_string."<br />".print_r($indices, true)."</pre>\n";
            }
          }
          
          // For every remaining index specified for the table
          foreach ( (array) $indices as $index ) {
            // Push a query line into $cqueries that adds the index to that table
            $cqueries[] = "ALTER TABLE {$table} ADD $index";
            $for_update[$table.'.'.$fieldname] = 'Added index '.$table.' '.$index;
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

/**
 * Validate Site Name
 * @param string $sitename Site name to check
 * @access private
 * @return mixed
 */
  private function __checkSiteName($sitename = '') {
    if ( empty($sitename) ) {
      throw new InstallerError(
        "<p>Please provide valid site name.</p>",
        self::ERROR_SITE_NAME
      );
    }
    
    return true;
  }

/**
 * Validate email
 * @param string $email Email to be validated
 * @return mixed
 */  
  private function __checkValidEmail($email = '') {
    if ( !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) ) {
      throw new InstallerError(
        "<p>Please provide valid email.</p>",
        self::ERROR_SITE_EMAIL
      );
    }
    
    return true;
  }

/**
 * Generate random password for step 4
 * @param int $length the length of generated random password
 * @access private
 * @return string $pass random password
 */  
  private function __generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
    srand( (double)microtime()*1000000 );
    $i = 0;
    $pass = '';
    
    while ($i++ <= $length) {
      $num   = rand() % 64;
      $tmp   = substr($chars, $num, 1);
      $pass .= $tmp;
    }
    
    return $pass;
  }

/**
 * Check if sample config (config.sample.inc.php) exists
 * @param string $file absolute file path
 * @access private
 * @return void
 */
  private function __checkSampleConfig($file) {
    if ( !file_exists($file) ) {
      throw new InstallerError(
        '<p>Sorry, ThinkTank Installer need a config.sample.inc.php file to work from. '.
        'Please re-upload this file from your ThinkTank installation.</p>',
        self::ERROR_CONFIG_SAMPLE_MISSING
      );
    }
  }

/**
 * Step 1 - Check requirements
 * @access private
 * @return void
 */  
  private function __step1() {
    $php_compat = 0;
    if ( $this->checkVersion() ) {
      $php_compat = 1;
    }
    self::$__view->assign('php_compat', $php_compat);
    self::$__view->assign('php_required_version', self::$__requiredVersion['php']);
    self::$__view->assign('libs', self::checkDependency());
    self::$__view->assign('permission', self::checkPermission());
    $writeable_directories = array(
      'logs' => THINKTANK_ROOT_PATH . 'logs',
      'compiled_view' => self::$__view->compile_dir,
      'cache' => self::$__view->compile_dir . 'cache'
    );
    self::$__view->assign('writeable_directories', $writeable_directories);
    self::$__view->assign('subtitle', 'Requirements Check');
  }

/**
 * Step 2 - Setup database and site configuration
 * @access private
 * @return void
 */  
  private function __step2() {
    // make sure we have passed step 1
    if ( !self::checkStep1() ) {
      header('Location: index.php?step=1');
      die;
    }
    
    self::$__view->assign('db_name', 'thinktank');
    self::$__view->assign('db_user', 'username');
    self::$__view->assign('db_passwd', 'password');
    self::$__view->assign('db_host', 'localhost');
    self::$__view->assign('db_prefix', 'tt_');
    self::$__view->assign('db_socket', '');
    self::$__view->assign('db_port', '');
    self::$__view->assign('site_name', 'My ThinkTank');
    self::$__view->assign('owner_name', 'Your Name');
    self::$__view->assign('site_email', 'username@example.com');
    self::$__view->assign('subtitle', 'Setup Database and Site Configuration');
  }
  
/**
 * Step 3 - Populate Database and Finishing
 * @access private
 * @return void
 */  
  private function __step3() {
    $config_file_exists = false;
    $config_file = THINKTANK_WEBAPP_PATH . 'config.inc.php';
    
    // make sure we are here with posted data
    if ( empty($_POST) ) {
      header('Location: index.php?step=2');
      die;
    }
    
    // check if we have made config.inc.php
    if ( file_exists($config_file) ) {
      // this is could be from step 2 is not able writing
      // to webapp dir
      $config_file_exists = true;
      require $config_file;
      $db_config['db_name']   = $THINKTANK_CFG['db_name'];
      $db_config['db_user']   = $THINKTANK_CFG['db_user'];
      $db_config['db_password'] = $THINKTANK_CFG['db_password'];
      $db_config['db_host']   = $THINKTANK_CFG['db_host'];
      $db_config['db_socket']   = $THINKTANK_CFG['db_socket'];
      $db_config['db_port']   = $THINKTANK_CFG['db_port'];
      $db_config['table_prefix'] = $THINKTANK_CFG['table_prefix'];
      $site_email   = trim($_POST['site_email']);
      $owner_name   = trim($_POST['owner_name']);
      $site_name    = $THINKTANK_CFG['app_title'];
      $country      = trim($_POST['country']);
    } else {
      // make sure we're not from error of couldn't write config.inc.php
      if ( !isset($_POST['db_user']) && !isset($_POST['db_passwd']) &&
           !isset($_POST['db_name']) && !isset($_POST['db_host']) ) {
        
        header('Location: index.php?step=2');
        die;
      }
      
      // trim each posted value
      $db_config['db_name']   = trim($_POST['db_name']);
      $db_config['db_user']   = trim($_POST['db_user']);
      $db_config['db_password'] = trim($_POST['db_passwd']);
      $db_config['db_host']   = trim($_POST['db_host']);
      $db_config['db_socket']   = trim($_POST['db_socket']);
      $db_config['db_port']   = trim($_POST['db_port']);
      $db_config['table_prefix'] = trim($_POST['db_prefix']);
      $site_email   = trim($_POST['site_email']);
      $owner_name   = trim($_POST['owner_name']);
      $site_name    = trim($_POST['site_name']);
      $country      = trim($_POST['country']);
      
      if ( empty($db_config['table_prefix']) ) {
        $db_config['table_prefix'] = 'tt_';
      }
    }
    
    try {
      self::checkDb($db_config);
    } catch (InstallerError $e) {
      $e->showError();
    }
    
    try {
      self::__checkSiteName($site_name);
            
      try {
        self::__checkValidEmail($site_email);
      } catch (InstallerError $e) {
        $e->showError();
      }
    } catch (InstallerError $e) {
      $e->showError();
    }
    
    // check sample configuration file
    // when config.inc.php is not exist
    if (!$config_file_exists) {
      $sample_config_filename = THINKTANK_WEBAPP_PATH . 'config.sample.inc.php';
      try {
        self::__checkSampleConfig($sample_config_filename);
      } catch (InstallerError $e) {
        $e->showError();
      }
    
      // read sample configuration file and replace some lines
      $sample_config = file($sample_config_filename);
      foreach ($sample_config as $line_num => $line) {
        switch ( substr($line, 14, 30) ) {
          case "['app_title']                 ":
            $sample_config[$line_num] = str_replace("'ThinkTank'", "'$site_name'", $line);
            break;
          case "['log_location']              ":
            $sample_config[$line_num] = str_replace(
              "'/your-server-path-to/thinktank/logs/crawler.log'", 
              "'" . THINKTANK_ROOT_PATH . "logs/crawler.log'", $line
            );
            break;
          case "['sql_log_location']          ":
            $sample_config[$line_num] = str_replace(
              "'/your-server-path-to/thinktank/logs/sql.log'", 
              "'" . THINKTANK_ROOT_PATH . "logs/sql.log'", $line
            );
            break;
          case "['site_root_path']            ":
            $sample_config[$line_num] = str_replace(
              "'/'", "'" . THINKTANK_BASE_URL . "'", $line
            );
            break;
          case "['source_root_path']          ":
            $sample_config[$line_num] = str_replace(
              "'/your-server-path-to/thinktank/'",
              "'" . THINKTANK_ROOT_PATH . "'", $line
            );
            break;
          case "['db_host']                   ":
            $sample_config[$line_num] = str_replace(
              "'localhost'", "'" . $db_config['db_host'] . "'", $line
            );
            break;
          case "['db_user']                   ":
            $sample_config[$line_num] = str_replace(
              "'your_database_username'", "'" . $db_config['db_user'] . "'", $line
            );
            break;
          case "['db_password']               ":
            $sample_config[$line_num] = str_replace(
              "'your_database_password'", "'" . $db_config['db_password'] . "'", $line
            );
            break;
          case "['db_name']                   ":
            $sample_config[$line_num] = str_replace(
              "'your_thinktank_database_name'", "'" . $db_config['db_name'] . "'", $line
            );
            break;
          case "['db_socket']                 ":
            $sample_config[$line_num] = str_replace(
              "= '';", "= '" . $db_config['db_socket'] . "';", $line
            );
            break;
          case "['db_port']                   ":
            $sample_config[$line_num] = str_replace(
              "= '';", "= '" . $db_config['db_port'] . "';", $line
            );
            break; 
          case "['table_prefix']              ":
            $sample_config[$line_num] = str_replace(
              "'tt_'", "'" . $db_config['table_prefix'] . "'", $line
            );
            break;
        }
      } // end foreach
    
      if ( !is_writable(THINKTANK_WEBAPP_PATH) ) {
        /* if not writeable user should create config.sample.inc.php manually */
        $message  = "<p>ThinkTank couldn't write <code>config.sample.inc.php</code> file! Make sure <code>".
                    THINKTANK_WEBAPP_PATH."</code> writeable! ";
        $message .= "Or you can create the <code>config.sample.inc.php</code> manually and paste the following text into it.</p><br>";
        $message .= '<textarea cols="120" rows="15">';
        foreach ($sample_config as $line) {
          $message .= htmlentities($line);
        }
        $message .= '</textarea><br>';
        $message .= "<p>After you've done that, click the Next Step &raquo;</p>";
        
        // hidden form
        $message .= '<form name="form1" method="post" action="index.php?step=3">';
        $message .= '<input type="hidden" name="owner_name" value="'.$owner_name.'">';
        $message .= '<input type="hidden" name="site_email" value="'.$site_email.'">';
        $message .= '<input type="hidden" name="country" value="'.$country.'">';
        
        // submit button
        $message .= '<div class="clearfix append_20">' .
                    '<div class="grid_10 prefix_9 left">' .
                    '<input type="submit" name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Next Step &raquo">' .
                    '</div></div></form>';
        
        self::diePage($message, 'File Configuration Error');
      } else {
        /* write the config file */
        $handle = fopen($config_file, 'w');
        foreach( $sample_config as $line ) {
          fwrite($handle, $line);
        }
        fclose($handle);
        chmod($config_file, 0666);
      }
      
    } // if !$config_file_exists
    
    // check tables
    try {
      self::checkTable($db_config);
    } catch (InstallerError $e) {
      // catch error when tablea exist
      $e->showError();
    }
    
    // if empty, we're ready to populate the database with ThinkTank tables
    self::populateTables($db_config);
    
    global $THINKTANK_CFG;
    $THINKTANK_CFG = $db_config;
    $admin_exists = self::isAdminExists();
    
    if ( !$admin_exists ) { // create admin if not exists
      $password = self::__generatePassword();
      $q = "INSERT INTO {$db_config['table_prefix']}owners ";
      $q .= " (`user_email`,`user_pwd`,`country`,`joined`,`activation_code`,`full_name`, `user_activated`, `is_admin`)";
      $q .= " VALUES ('".$site_email."','".md5($password)."','".$country."',now(),'','".$owner_name."', 1, 1)";
      self::$db->exec($q);
    } else {
      $site_email = 'Use your old email';
      $password = 'Use your old password';
    }
    unset($THINKTANK_CFG);
    
    self::$__view->assign('errors', self::getErrorMessages() );
    self::$__view->assign('username', $site_email);
    self::$__view->assign('password', $password);
    self::$__view->assign('login_url', THINKTANK_BASE_URL . 'session/login.php');
    self::$__view->assign('subtitle', 'Finish');
  }
  
/**
 * Get error messages.
 * 
 * @access public
 */
  function getErrorMessages() {
    return self::$__errorMessages;
  }

/**
 * Clear error messages.
 * 
 * @access public
 * @return void
 */  
  function clearErrorMessages() {
    self::$__errorMessages = array();
  }
  
/**
 * Installation steps page
 * @param int $step Current step
 * @return void
 */
  function installPage($step = 1) {
    $methodName = '__step' . $step;
    if ( !method_exists(__CLASS__, $methodName) ) {
      $step = 1;
      $methodName = '__step1';
    }
    self::$methodName();
    self::$__view->display('installer.step.' . $step . '.tpl');
  }

/**
 * Repairing page
 * @param array $params
 * @return void
 */  
  function repairPage($params = null) {
    $config_file = THINKTANK_WEBAPP_PATH . 'config.inc.php';
    
    // check requirements
    if ( !self::checkStep1() ) {
      try {
        throw new InstallerError(
          "Requirements are not met. " .
          "Make sure your PHP version >= " . self::$__requiredVersion['php'] .
          " and you have cURL and GD extension installed.", self::ERROR_REQUIREMENTS
        );
      } catch (InstallerError $e) {
        $e->showError();
      }
    }
    
    // check file configuration
    if ( !file_exists($config_file) ) {
      try {
        throw new InstallerError(
          '<p>Sorry, ThinkTank Repairer need a <code>config.inc.php</code> file to work from. ' .
          'Please upload this file to <code>' . THINKTANK_WEBAPP_PATH . '</code> or ' .
          'copy / rename from <code>' . THINKTANK_WEBAPP_PATH . 'config.sample.inc.php</code> to ' .
          '<code>' . THINKTANK_WEBAPP_PATH . 'config.inc.php</code>. If you don\'t have permission to ' .
          'do this, you can reinstall ThinkTank by ' .
          'clearing out ThinkTank tables and then clicking <a href="' . THINKTANK_BASE_URL . 'install/">here</a>',
          self::ERROR_CONFIG_FILE_MISSING
        );
      } catch (InstallerError $e) {
        $e->showError();
      }
    }
    require_once $config_file;
    
    // check database
    try {
      self::checkDb($THINKTANK_CFG);
    } catch (InstallerError $e) {
      $e->showError();
    }
    
    // check $THINKTANK_CFG['repair'] is set to true
    if ( !isset($THINKTANK_CFG['repair']) or !$THINKTANK_CFG['repair'] ) {
      try {
        throw new InstallerError(
          'To do repairing ' .
          'you must define<br><code>$THINKTANK_CFG[\'repair\'] = true;</code><br>in ' .
          'your configuration file at <code>' . THINKTANK_WEBAPP_PATH . 'config.inc.php</code>',
          self::ERROR_REPAIR_CONFIG
        );
      } catch (InstallerError $e) {
        $e->showError();
      }
    }
    
    // clearing error messages before doing the repair
    if ( !empty(self::$__errorMessages) ) {
      self::clearErrorMessages();
    }
    
    // do repairing when form is posted and $_GET is not empty
    if ( isset($_POST['repair']) && !empty($_GET) ) {
      self::$__view->assign('posted', true);
      $succeed = false;
      $messages = array();
      
      // check if we repairing db
      if ( isset($params['db']) ) {
        $messages['db'] = self::repairTables($THINKTANK_CFG);
        self::$__view->assign('messages_db', $messages['db']);
      }
      
      // check if we need to create admin user
      if ( isset($params['admin']) ) {
        $site_email   = trim($_POST['site_email']);
        $owner_name   = trim($_POST['owner_name']);
        $country      = trim($_POST['country']);
        $password = self::__generatePassword();
        $q = "INSERT INTO {$THINKTANK_CFG['table_prefix']}owners ";
        $q .= " (`user_email`,`user_pwd`,`country`,`joined`,`activation_code`,`full_name`, `user_activated`, `is_admin`)";
        $q .= " VALUES ('".$site_email."','".md5($password)."','".$country."',now(),'','".$owner_name."', 1, 1)";
        self::$db->exec($q);
        $messages['admin'] = "Create admin user <strong>$site_email</strong> with password <strong>$password</strong>";
        self::$__view->assign('messages_admin', $messages['admin']);
        self::$__view->assign('username', $site_email);
        self::$__view->assign('password', $password);
      }
      
      if ( !empty(self::$__errorMessages) ) {
        // failed repairing
        self::$__view->assign('messages_error', self::$__errorMessages);
      } else {
        $succeed = true;
      }
      
      self::$__view->assign('succeed', $succeed);
    } else {
      if ( empty($params) ) {
        self::$__view->assign('show_form', 0);
      } else {
        $information_message = array();
        self::$__view->assign('show_form', 1);
        if ( isset($params['db']) ) {
          $information_message['db']  = 'Checking your existing ThinkTank tables. If some tables are missing, ';
          $information_message['db'] .= 'ThinkTank will attempt to create those tables. ThinkTank will check every ThinkTank tables and ';
          $information_message['db'] .= 'will attemp to repair those tables if the status is not okay.';
        }
        
        if ( isset($params['admin']) ) {
          self::$__view->assign('owner_name', 'Your Name');
          self::$__view->assign('site_email', 'username@example.com');
          self::$__view->assign('admin_form', 1);
          $information_message['admin'] = 'ThinkTank will attemp to create one admin user based on form below.';
        }
        
        if ( !empty($information_message) ) {
          $info  = '<div class="clearfix info_message">';
          $info .= '<p><strong class="not_okay">Read before repairing!</strong> ';
          $info .= 'ThinkTank Repairer will do the following actions when repairing: </p><ul>';
          foreach ($information_message as $msg) {
            $info .= "<li>$msg</li>";
          }
          $info .= '</ul></div>';
          self::$__view->assign('info', $info);
        }
        self::$__view->assign('action_form', $_SERVER['REQUEST_URI']);
      }
    }
    
    self::$__view->assign('subtitle', 'Repairing');
    self::$__view->display('installer.repair.tpl');
  }

/**
 * Die with page formatted, inspired by wp_die()
 * Formatting happens when Smarty is available
 * 
 * @param string $message Content to be displayed
 * @param string $title Title on browser
 */
  function diePage($message, $title = '') {
    // chec if compile_dir is set
    if ( !isset(self::$__view->compile_dir) ) {
      echo '<strong>ERROR: Couldn\'t instantiate SmartyInstaller or Smarty!</strong><br>';
      echo '<p>Make sure Smarty related classes exist.<br>';
      die();
    }
    
    // check if compiled directory is writeable
    if ( !is_writable(self::$__view->compile_dir) ) {
      echo '<strong>ERROR: ' . self::$__view->compile_dir . ' is not writeable!</strong><br>';
      echo '<p>Make sure <code>' . self::$__view->compile_dir . '</code> is writeable by the webserver.<br>';
      echo 'The fastest way: <code>chmod -R 777 ' . self::$__view->compile_dir . '</code>.</p>';
      die();
    }
    
    self::$__view->assign('message', $message);
    self::$__view->assign('subtitle', $title);
    self::$__view->display('installer.die.tpl');
    die();
  }
}
?>