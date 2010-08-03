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
 * Controller object that called getInstance method
 * @var object
 * @access private
 */
    private static $__controller;

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
    public static $currentVersion;

/**
 * Stores required version of each apps
 */
    public static $requiredVersion;

/**
 * maps DAO from db_type and defines class names
 */
    public static $dao = array(
        //MySQL Version
        'mysql' => 'InstallerMySQLDAO'
    );

/**
 * List of ThinkTank tables
 *
 * @var array
 */  
    public static $tables = array(
        'follower_count', 'follows', 'instances', 'links', 'owner_instances', 'owners', 
        'plugin_options', 'plugins', 'post_errors', 'posts', 'user_errors', 'users'
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
 * Name of site name
 * @var string
 */
    public static $site_name = 'ThinkTank';
  
/**
 * Private constructor, so can't be accessed
 * from outside
 */
    private function __construct() {}

/**
 * Get Installer instance
 * @param object $controller
 * @return object $this->__instance Installer instance
 */
    public static function getInstance($controller) {
        if ( self::$__instance == null ) {
            self::$__instance = new Installer();
            self::$__controller = $controller;
            
            // use lazy loading
            if ( !class_exists('Loader', FALSE) ) {
                require_once THINKUP_WEBAPP_PATH . 'model' . DS . 'class.Loader.php';
            }
            Loader::register();
            
            // get required version of php and mysql
            // and set current version
            require_once (THINKUP_WEBAPP_PATH . 'install' . DS . 'version.php');
            
            self::$requiredVersion = array(
                'php' => $THINKTANK_VERSION_REQUIRED['php'],
                'mysql' => $THINKTANK_VERSION_REQUIRED['mysql']
            );
            self::$currentVersion = $THINKTANK_VERSION;
        }
        
        return self::$__instance;
    }
  
/**
 * Check PHP version
 * 
 * @param string $ver can be used for testing for failing
 * @access public
 * @return bool Requirements met
 */
    public function checkVersion($ver = '') {
        $ret = false;
        $version = phpversion();
        
        // when testing
        if ( defined('INSTALLER_ON_TEST') && INSTALLER_ON_TEST && !empty($ver) ) {
            $version = $ver;
        }
        
        $ret = version_compare( $version, self::$requiredVersion['php'], '>=' );
        
        return $ret;
    }
  
    public function getCurrentVersion() {
        return self::$currentVersion;
    }
  
    public function getRequiredVersion() {
        return self::$requiredVersion;
    }
  
/**
 * Check GD and cURL
 * 
 * @param array $libs can be used for testing for failing
 * @return array
 */
    public function checkDependency($libs = array()) {
        $ret = array('curl' => false, 'gd' => false);
        // check curl
        if ( extension_loaded('curl') && function_exists('curl_exec') ) {
            $ret['curl'] = true;
        }
        
        // check GD
        if ( extension_loaded('gd') && function_exists('gd_info') ) {
            $ret['gd'] = true;
        }
        
        // when testing
        if ( defined('INSTALLER_ON_TEST') && INSTALLER_ON_TEST && !empty($libs) ) {
            $ret = $libs;
        }
        
        return $ret;
    }

/**
 * Check if log and template directories are writeable
 * 
 * @param array $perms can be used for testing for failing
 * @access public
 * @return array
 */  
    public function checkPermission($perms = array()) {
        $compile_dir = THINKUP_WEBAPP_PATH . 'view' . DS . 'compiled_view';
        $cache_dir = $compile_dir . DS . 'cache'; 
        
        $ret = array(
            'logs' => false, 'compiled_view' => false, 'cache' => false
        );
        
        if ( is_writable(THINKUP_ROOT_PATH . 'logs') ) {
            $ret['logs'] = true;
        }
        
        if ( is_writable($compile_dir) ) {
            $ret['compiled_view'] = true;
        }
        
        if ( is_writable($cache_dir) ) {
            $ret['cache'] = true;
        }
        
        // when testing
        if ( defined('INSTALLER_ON_TEST') && INSTALLER_ON_TEST && !empty($perms) ) {
            $ret = $perms;
        }
        
        return $ret;
    }

/**
 * Check path existent
 * @param array $config
 */
    public function checkPath($config) {
        // check if $THINKUP_CFG related to path exists
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
 * Check PHP version, cURL, GD and path permission
 * 
 * @param array $pass can be used for testing for failing
 * @access public
 * @return bool
 */  
    public function checkStep1($pass = true) {
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
        
        // when testing
        if ( defined('INSTALLER_ON_TEST') && INSTALLER_ON_TEST && !empty($pass) ) {
            $ret = $pass;
        } else {
            $ret = ($version_compat && $lib_depends_ret && $writeable_permission_ret);
        }
        
        return $ret;
    }

/**
 * Set db property
 * @param array $config Database config
 */  
    public function setDb($config) {
        try {
            // don't use DAOFactory on Installer since calling
            // DAOFactory::getDBType also calls Config this will throw an error
            // to non-existent config file
            $dao = self::$dao[$config['db_type']];
            self::$db = new $dao($config);
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        if ( !self::$db ) {
            throw new InstallerError(
                '<p>Failed establishing database connection. Probably either your username, ' .
                '<code>' . $config['db_user'] . '</code>, and password, <code>' . $config['db_password'] .
                '</code>, you have provided are incorrect or we can\'t connect the ' .
                'database server at <code>' . $config['db_host'] . '</code>. Or ' .
                'We were able to connect to the database server (which means ' .
                'your username and password is okay) but not able to select the <code>' .
                $config['db_name'] . '</code> database.' .
                '</p>',
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
        self::$showTables = self::$db->showTables();
        
        return self::$showTables;
    }

/**
 * Check database
 * @param array $params database credentials
 * @return mixed
 */
    function checkDb($params) {
        $ret = self::setDb($params);
        
        if ( $ret ) {
            return true;
        } else {
            return false;
        }
    }

/**
 * Check table existent. 
 * See also self::isThinkTankTablesExist().
 * The different between self::isThinkTankTablesExist is, self::isThinkTankTablesExist doesn't
 * throw an error and returns boolean value.
 * 
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
                        "repair your table click " .
                        "<a href=\"" . THINKUP_BASE_URL . "install/repair.php?db=1\">here</a>.</p>",
                        self::ERROR_DB_TABLES_EXIST
                    );
                }
            }
        }
        
        return true;
    }

/**
 * Check if thinktank table exists and its okay. Return true when ThinkTank table exists.
 * The different between self::checkTable is, self::isThinkTankTablesExist doesn't
 * throw an error and returns boolean value. This method should be called when
 * we're not in installation steps.
 * 
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
        global $THINKUP_CFG;
        if ( !self::$db ) {
            self::setDb($THINKUP_CFG);
        }
        
        $_tablename = $THINKUP_CFG['table_prefix'] . $tablename;
        $row = self::$db->checkTable($_tablename);
        
        $okay = false;
        if ( isset($row['Msg_text']) && $row['Msg_text'] == 'OK' ) {
            $okay = true;
        } else {
            self::$tmp_var = $row['Msg_text'];
        }
        
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
        global $THINKUP_CFG;
        
        if ( !self::$db ) {
            self::setDb($THINKUP_CFG);
        }
        
        // check if table owners exists
        if ( !self::isTableOk('owners') ) {
            return false;
        }
        $tablename = $THINKUP_CFG['table_prefix'] . 'owners';
        $exists = self::$db->isAdminExists($tablename);
        
        if ( $exists ) {
            return true;
        } else {
            return false;
        }
    }

/**
 * Insert admin into $tablename with value $insertAdminVal
 * 
 * @param string $tablename
 * @param array $insertAdminVal
 */
    function insertAdmin($tablename, $insertAdminVal) {
        return self::$db->insertAdmin($tablename, $insertAdminVal);
    }

/**
 * Check if ThinkTank is already installed.
 * 
 * @param array $config
 * @return bool true when ThinkTank is already installed
 */  
    function isThinkTankInstalled($config) {
        // check if file config present
        $config_file_exists = false;
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';
        
        // check if we have made config.inc.php
        if ( file_exists($config_file) ) {
            $config_file_exists = true;
        } else {
            self::$__errorMessages['config_file'] = "Config file doesn't exist.";
            return false;
        }
        
        // check version is met
        $version_met = self::checkStep1();
        // when testing
        if ( defined('INSTALLER_ON_TEST') && INSTALLER_ON_TEST && !empty($pass) ) {
            $version_met = $pass;
        }
        if ( !$version_met ) {
            self::$__errorMessages['requirements'] = "Requirements are not met. " .
                "Make sure your PHP version >= " . self::$requiredVersion['php'] . ", " .
                "you have cURL and GD extension installed, and template and log directories are writeable";
            return false;
        }
        
        // database is okay
        $db_check = self::checkDb($config);
        
        // table present
        $table_present = true;
        if ( !self::isThinkTankTablesExist($config) ) {
            self::$__errorMessages['table'] = 'ThinkTank table is not fully available.';
            $table_present = false;
        }
        
        // one owner exists and has is_admin = 1
        $admin_exists = true;
        global $THINKUP_CFG;
        $THINKUP_CFG = $config;
        if ( !self::isAdminExists() ) {
            self::$__errorMessages['admin'] = "There's no admin user.";
            $admin_exists = false;
        }
        
        return ($version_met && $db_check && $table_present && $admin_exists);
    }

/**
 * populate table / execute queries in queries.php
 * 
 * @param array $config database configuration
 * @param bool $verbose database configuration
 * @return mixed
 */  
    function populateTables($config, $verbose = false) {
        global $install_queries;
        
        $table = array();
        foreach (self::$tables as $t) {
            $table[$t] = $config['table_prefix'] . $t;
        }
        $query_file = THINKUP_ROOT_PATH . 'sql' . DS . 'queries.php';
        if ( !file_exists($query_file) ) {
            throw new InstallerError(
                "File <code>$query_file</code> is not found.", 
                self::ERROR_FILE_NOT_FOUND
            );
        }
        require_once $query_file;
        
        $expected_queries = self::examineQueries($install_queries);
        foreach ($expected_queries['queries'] as $query) {
            try {
                self::$db->exec($query);
            } catch (InstallerError $e) {
                $e->getMessage();
            }
        }
        
        if ( $verbose ) {
            return $expected_queries['for_update'];
        } else {
            return true;
        }
    }
  
    function repairTables($THINKUP_CFG) {
        if ( !self::$showTables ) {
            self::showTables($THINKUP_CFG);
        }
        
        // check total tables is the same with the default defined
        $total_table_found = 0;
        if ( count(self::$showTables) > 0 ) { // database contains tables
            foreach ( self::$tables as $table ) {
                if ( in_array($THINKUP_CFG['table_prefix'] . $table, self::$showTables) ) {
                    $total_table_found++;
                }
            }
        }
        $messages = array();
        
        // show missing table
        $total_table_not_found = count(self::$tables) - $total_table_found;
        if ( $total_table_not_found > 0 ) {
            $messages['missing_tables']  = "<p>There are <strong class=\"not_okay\">" .
                                           $total_table_not_found . " missing tables</strong>. ";
            $messages['missing_tables'] .= "ThinkTank will attempt to create missing tables and ".
                                           "alter existing tables if something is missing&hellip;";
            $messages['missing_tables'] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">" .
                                           "Create and alter some tables&hellip;</span>";
            $queries_logs = self::populateTables($THINKUP_CFG, true);
            if ( !empty($queries_logs) ) {
                foreach ( $queries_logs as $log ) {
                    $messages['missing_tables'] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
                                                   "<span class=\"repair_log\">$log</span>";
                }
            }
        } else {
            $messages['table_complete'] = '<p>Your ThinkTank tables are <strong class="okay">complete</strong>.</p>';
        }
        
        // does checking on tables that exist
        $okay = true;
        $table = '';
        foreach (self::$tables as $t) {
            $table = $THINKUP_CFG['table_prefix'] . $t;
            if ( self::isTableOk($table) ) {        
                $messages[$t] = "<p>The <code>$table</code> table is <strong class=\"okay\">okay</strong>.</p>";
            } else {
                $messages[$t]  = "<p>The <code>$table</code> table is <strong class=\"not_okay\">not okay</strong>. ";
                $messages[$t] .= "It is reporting the following error: <code>".self::$tmp_var."</code>. ";
                $messages[$t] .= "ThinkTank will attempt to repair this table&hellip;";
                
                // repairs table that not okay
                $row = self::$db->repairTable($table);
                
                if ( isset($row['Msg_text']) && $row['Msg_text'] == 'OK' ) {
                    $messages[$t] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">" .
                                     "Sucessfully repaired the $table table.</span>";
                } else { // failed to repair the table
                    $messages[$t] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">" .
                                     "Failed to repair the $table table. " .
                                     "Error: {$row['Msg_text']}</span><br />";
                    self::$__errorMessages[$t] = "<p class=\"repair_log\">Failed to repair the $table table.</p>";
                }
                
                $messages[$t] .= "</p>";
            }
        }
        
        return $messages;
    }
  
/**
 * Examines / groups queries
 * 
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
        
        return self::$db->examineQueries($queries, self::showTables());
    }

/**
 * Validate email
 * @param string $email Email to be validated
 * @access public
 * @return bool
 */  
    public function checkValidEmail($email = '') {
        if ( !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) ) {
            return false;
        }
        
        return true;
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
 * Create config file
 * 
 * @param array $db_config
 * @param array $admin_user
 */
    public function createConfigFile($db_config, $admin_user) {
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';
        $config_file_exists = file_exists($config_file);
        
        // check sample configuration file
        // when config.inc.php is not exist
        if (!$config_file_exists) {
            $sample_config_filename = THINKUP_WEBAPP_PATH . 'config.sample.inc.php';
            try {
                self::__checkSampleConfig($sample_config_filename);
            } catch (InstallerError $e) {
                return $e->showError(self::$__controller);
            }
        
            // read sample configuration file and replace some lines
            $sample_config = file($sample_config_filename);
            foreach ($sample_config as $line_num => $line) {
                switch ( substr($line, 12, 30) ) {
                    case "['site_root_path']            ":
                        $sample_config[$line_num] = str_replace(
                          "'/'", "'" . THINKUP_BASE_URL . "'", $line
                        );
                        break;
                    case "['source_root_path']          ":
                        $sample_config[$line_num] = str_replace(
                          "'/your-server-path-to/thinkup/'",
                          "'" . THINKUP_ROOT_PATH . "'", $line
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
                          "'your_thinkup_database_name'", "'" . $db_config['db_name'] . "'", $line
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
                          "'tu_'", "'" . $db_config['table_prefix'] . "'", $line
                        );
                        break;
                }
            } // end foreach
        
            if ( !is_writable(THINKUP_WEBAPP_PATH) ) {
                /* if not writeable user should create config.sample.inc.php manually */
                $message  = "<p>ThinkTank couldn't write <code>config.sample.inc.php</code> file! Make sure <code>".
                            THINKUP_WEBAPP_PATH."</code> writeable! ";
                $message .= "Or you can create the <code>config.sample.inc.php</code> ".
                            "manually and paste the following text into it.</p><br>";
                $message .= '<textarea cols="120" rows="15">';
                foreach ($sample_config as $line) {
                    $message .= htmlentities($line);
                }
                $message .= '</textarea><br>';
                $message .= "<p>After you've done that, click the Next Step &raquo;</p>";
              
                // hidden form
                $message .= '<form name="form1" class="input" method="post" action="index.php?step=3">';
                $message .= '<input type="hidden" name="site_email" value="' . $admin_user['email'] . '" />';
                $message .= '<input type="hidden" name="password" value="' . $admin_user['password'] . '" />';
                
                // submit button
                $message .= '<div class="clearfix append_20">' .
                            '<div class="grid_10 prefix_9 left">' .
                            '<input type="submit" name="Submit" class="tt-button '.
                                'ui-state-default ui-priority-secondary ui-corner-all" value="Next Step &raquo">' .
                            '</div></div></form>';
              
                self::$__controller->diePage($message, 'File Configuration Error');
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
        
        return true;
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
    
    function repairerCheckStep1() {
        if ( !self::checkStep1() ) {
            throw new InstallerError(
                "Requirements are not met. " .
                "Make sure your PHP version >= " . self::$requiredVersion['php'] .
                ", you have cURL and GD extension installed, and template and log directories are writeable.",
                self::ERROR_REQUIREMENTS
            );
        }
        
        return true;
    }
    
    function repairerCheckConfigFile() {
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';
        
        if ( !file_exists($config_file) ) {            
            throw new InstallerError(
                '<p>Sorry, ThinkTank Repairer need a <code>config.inc.php</code> file to work from. ' .
                'Please upload this file to <code>' . THINKUP_WEBAPP_PATH . '</code> or ' .
                'copy / rename from <code>' . THINKUP_WEBAPP_PATH . 'config.sample.inc.php</code> to ' .
                '<code>' . THINKUP_WEBAPP_PATH . 'config.inc.php</code>. If you don\'t have permission to ' .
                'do this, you can reinstall ThinkTank by ' .
                'clearing out ThinkTank tables and then clicking '.
                '<a href="' . THINKUP_BASE_URL . 'install/">here</a>',
                self::ERROR_CONFIG_FILE_MISSING
            );
        }
        
        return $config_file;
    }
    
    function repairerIsDefined($THINKUP_CFG) {
        if ( !isset($THINKUP_CFG['repair']) or !$THINKUP_CFG['repair'] ) {
            throw new InstallerError(
                'To do repairing ' .
                'you must define<br><code>$THINKUP_CFG[\'repair\'] = true;</code><br>in ' .
                'your configuration file at <code>' . THINKUP_WEBAPP_PATH . 'config.inc.php</code>',
                self::ERROR_REPAIR_CONFIG
            );
        }
        
        return true;
    } 
}
