<?php
/**
 * Installer Model
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class Installer {
    /**
     * Singleton instance of Installer
     * 
     * @var mixed
     * @access private
     * @todo Make sure the instance records unique id (something like IP
     *       or mac address) which identifies executor
     */
    private static $instance = null;
    
    /**
     * Controller object that called getInstance method
     * 
     * @var object
     * @access private
     */
    private static $controller;

    /**
     * Stores error messages.
     * 
     * @var array
     * @access private
     */
    private static $error_messages = array();
  
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
     * Stores current version of ThinkUp
     * 
     * @var string
     * @access private
     */
    private static $current_version;

    /**
     * Stores required version of each apps
     * 
     * @var array
     * @access private
     */
    private static $required_version;

    /**
     * Maps DAO from db_type and defines class names.
     * We don't use DAOFactory in Installer to avaoid
     * non-existence configuration file
     * 
     * @var array
     * @access public
     */
    public static $dao = array(
        //MySQL Version
        'mysql' => 'InstallerMySQLDAO'
    );

    /**
     * List of ThinkUp tables.
     * If there are new tables added, make sure this property also updated
     *
     * @var array
     * @access public
     */  
    public static $tables = array(
        'follower_count', 'follows', 'instances', 'links', 'owner_instances', 'owners', 
        'plugin_options', 'plugins', 'post_errors', 'posts', 'user_errors', 'users'
    );

    /**
     * Result from SHOW TABLES
     * 
     * @var array
     * @access public
     */
    public static $show_tables;
  
    /**
     * PDO Instance
     * 
     * @var object
     * @access public
     */
    public static $db;
  
    /**
     * Temporary var. Helper
     * when hold temporary var between method calls
     * 
     * @var mixed
     * @access public
     */
    public static $tmp_var;
    
    /**
     * Name of site name. Default is ThinkUp.
     * 
     * @var string
     * @access public
     */
    public static $site_name = 'ThinkUp';
  
    /**
     * Private constructor, so can't be accessed
     * from outside
     * 
     * @access private
     */
    private function __construct() {}

    /**
     * Get Installer instance
     * 
     * @param object $controller
     * @return object $this->__instance Installer instance
     */
    public static function getInstance($controller) {
        if ( self::$instance == null ) {
            self::$instance = new Installer();
            self::$controller = $controller;
            
            // use lazy loading
            if ( !class_exists('Loader', FALSE) ) {
                require_once THINKUP_WEBAPP_PATH . 'model' . DS . 'class.Loader.php';
            }
            Loader::register();
            
            // get required version of php and mysql
            // and set current version
            require_once (THINKUP_WEBAPP_PATH . 'install' . DS . 'version.php');
            
            self::$required_version = array(
                'php' => $THINKUP_VERSION_REQUIRED['php'],
                'mysql' => $THINKUP_VERSION_REQUIRED['mysql']
            );
            self::$current_version = $THINKUP_VERSION;
        }
        
        return self::$instance;
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
        
        $ret = version_compare( $version, self::$required_version['php'], '>=' );
        
        return $ret;
    }
  
    public function getCurrentVersion() {
        return self::$current_version;
    }
  
    public function getRequiredVersion() {
        return self::$required_version;
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
     * Check path existent. This method may throw an Exception.
     * 
     * @param array $config
     * @return bool
     */
    public function checkPath($config) {
        // check if $THINKUP_CFG related to path exists
        if ( !is_dir($config['source_root_path']) ) {
            throw new InstallerError(
                "<p>ThinkUp's source root directory is not found</p>",
                self::ERROR_CONFIG_SOURCE_ROOT_PATH
            );
        }
        if ( !is_dir($config['smarty_path']) ) {
            throw new InstallerError(
                "<p>ThinkUp's smarty directory is not found</p>",
                self::ERROR_CONFIG_SMARTY_PATH
            );
        }
        if ( !is_dir(substr($config['log_location'], 0, -11)) ) {
            throw new InstallerError(
                "<p>ThinkUp log directory is not found</p>",
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
     * Set db property so it contains PDO Instance
     * 
     * @param array $config Database config
     * @return object PDO Instance
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
        
        if ( !self::$db || self::$db->error_message ) {
            throw new InstallerError(
                '<p>Failed establishing database connection. ' .
                    self::$db->error_message .
                '</p>',
                self::ERROR_DB_CONNECT
            );
        }
      
        return self::$db;
    }

    /**
     * Get SHOW TABLES at current $db
     * 
     * @param array $config
     * @access public
     * @return array tables
     */
    public function showTables($config = null) {
        if ( is_array(self::$show_tables) && !empty(self::$show_tables) ) {
            return self::$show_tables;
        }
        
        if ( !self::$db ) {
            self::setDb($config);
        }
        self::$show_tables = self::$db->showTables();
        
        return self::$show_tables;
    }

    /**
     * Check database
     * 
     * @param array $config database credentials
     * @return bool
     */
    public function checkDb($config) {
        $ret = self::setDb($config);
        
        if ( $ret ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check table existent. 
     * See also self::isThinkUpTablesExist().
     * The different between self::isThinkUpTablesExist is, self::isThinkUpTablesExist doesn't
     * throw an error and returns boolean value.
     * 
     * @param array $config
     * @return mixed throw error when table exists
     *         return true when ThinkUp tables don't exist
     */
    public function checkTable($config) {
        if ( !self::$show_tables ) {
            self::showTables($config);
        }
        
        if ( count(self::$show_tables) > 0 ) { // database contains tables
            foreach ( self::$tables as $table ) {
                if ( in_array($config['table_prefix'] . $table, self::$show_tables) ) {
                    // database contains ThinkUp table
                    // TODO: when table already exists, ask for repairing
                    throw new InstallerError(
                        "<p><strong>Ups!</strong> ThinkUp tables exist. If you're considering ".
                        "to install ThinkUp from scratch please clear out ThinkUp tables in ".
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
     * Check if ThinkUp table exists and its okay. Return true when ThinkUp table exists.
     * The different between self::checkTable is, self::isThinkUpTablesExist doesn't
     * throw an error and returns boolean value. This method should be called when
     * we're not in installation steps.
     * 
     * @param array $config
     * @return bool true when ThinkUp tables Exist
     */  
    public function isThinkUpTablesExist($config) {
        if ( !self::$show_tables ) {
            self::showTables($config);
        }
        
        $total_table_found = 0;
        if ( count(self::$show_tables) > 0 ) { // database contains tables
            foreach ( self::$tables as $table ) {
                if ( in_array($config['table_prefix'] . $table, self::$show_tables) ) {
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
     * 
     * @param string $tablename Table name
     * @param array $config
     * @access private
     * @return bool true if $tablename ok
     */
    private function isTableOk($tablename, $config) {
        if ( !self::$db ) {
            self::setDb($config);
        }
        
        $_tablename = $config['table_prefix'] . $tablename;
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
     * @param $config
     * 
     * @return bool
     */
    public function isAdminExists($config) {
        if ( !self::$db ) {
            self::setDb($config);
        }
        
        // check if table owners exists
        if ( !self::isTableOk('owners', $config) ) {
            return false;
        }
        $tablename = $config['table_prefix'] . 'owners';
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
     * @param array $insert_admin_val
     */
    public function insertAdmin($tablename, $insert_admin_val) {
        return self::$db->insertAdmin($tablename, $insert_admin_val);
    }

    /**
     * Check if ThinkUp is already installed.
     * 
     * @param array $config
     * @return bool true when ThinkUp is already installed
     */
    public function isThinkUpInstalled($config) {
        // check if file config present
        $config_file_exists = false;
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';
        
        // check if we have made config.inc.php
        if ( file_exists($config_file) ) {
            $config_file_exists = true;
        } else {
            self::$error_messages['config_file'] = "Config file doesn't exist.";
            return false;
        }
        
        // check version is met
        $version_met = self::checkStep1();
        // when testing
        if ( defined('INSTALLER_ON_TEST') && INSTALLER_ON_TEST && !empty($pass) ) {
            $version_met = $pass;
        }
        if ( !$version_met ) {
            self::$error_messages['requirements'] = "Requirements are not met. " .
                "Make sure your PHP version >= " . self::$required_version['php'] . ", " .
                "you have cURL and GD extension installed, and template and log directories are writeable";
            return false;
        }
        
        // database is okay
        $db_check = self::checkDb($config);
        
        // table present
        $table_present = true;
        if ( !self::isThinkUpTablesExist($config) ) {
            self::$error_messages['table'] = 'ThinkUp table is not fully available. Make sure ' .
                                            'the <code>$THINKUP_CFG[\'table_prefix\']</code> is set ' .
                                            'correctly.';
            $table_present = false;
        }
        
        // check one owner exists and has is_admin = 1
        $admin_exists = true;
        if ( !self::isAdminExists($config) ) {
            self::$error_messages['admin'] = "There's no admin user.";
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
    public function populateTables($config, $verbose = false) {
        $table = array();
        foreach (self::$tables as $t) {
            $table[$t] = $config['table_prefix'] . $t;
        }
        
        $install_queries = self::getInstallQueries($config['table_prefix']);
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
    
    /**
     * Read the contents of the /sql/build-db_mysql.sql file. 
     * Replace all instances of 'tu_' with the custom table prefix.
     * 
     * @param string $table_prefix custom table prefix to replace the 'tu_' prefix
     * @access private
     * @return string
     */
    public function getInstallQueries($table_prefix) {
        $query_file = THINKUP_ROOT_PATH . 'sql' . DS . 'build-db_mysql.sql';
        if ( !file_exists($query_file) ) {
            throw new InstallerError(
                "File <code>$query_file</code> is not found.", 
                self::ERROR_FILE_NOT_FOUND
            );
        }
        
        $str_query = file_get_contents($query_file);
        $search = array();
        $replace = array();
        foreach (self::$tables as $key => $table) {
            $search[$key] = 'tu_' . $table;
            $replace[$key] = $table_prefix . $table;
        }
        // additional search for adding two spaces after PRIMARY KEY
        $search[]  = 'PRIMARY KEY (';
        $replace[] = 'PRIMARY KEY  (';
        
        $str_query = str_replace($search, $replace, $str_query);
        
        return $str_query;
    }

    /**
     * Repair tables
     * 
     * @param array $config
     * @return arary Messages
     */
    public function repairTables($config) {
        if ( !self::$show_tables ) {
            self::showTables($config);
        }
        
        // check total tables is the same with the default defined
        $total_table_found = 0;
        if ( count(self::$show_tables) > 0 ) { // database contains tables
            foreach ( self::$tables as $table ) {
                if ( in_array($config['table_prefix'] . $table, self::$show_tables) ) {
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
            $messages['missing_tables'] .= "ThinkUp will attempt to create missing tables and ".
                                           "alter existing tables if something is missing&hellip;";
            $messages['missing_tables'] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">" .
                                           "Create and alter some tables&hellip;</span>";
            $queries_logs = self::populateTables($config, true);
            if ( !empty($queries_logs) ) {
                foreach ( $queries_logs as $log ) {
                    $messages['missing_tables'] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
                                                   "<span class=\"repair_log\">$log</span>";
                }
            }
        } else {
            $messages['table_complete'] = '<p>Your ThinkUp tables are <strong class="okay">complete</strong>.</p>';
        }
        
        // does checking on tables that exist
        $okay = true;
        $table = '';
        foreach (self::$tables as $t) {
            $table = $config['table_prefix'] . $t;
            if ( self::isTableOk($table, $config) ) {        
                $messages[$t] = "<p>The <code>$table</code> table is <strong class=\"okay\">okay</strong>.</p>";
            } else {
                $messages[$t]  = "<p>The <code>$table</code> table is <strong class=\"not_okay\">not okay</strong>. ";
                $messages[$t] .= "It is reporting the following error: <code>".self::$tmp_var."</code>. ";
                $messages[$t] .= "ThinkUp will attempt to repair this table&hellip;";
                
                // repairs table that not okay
                $row = self::$db->repairTable($table);
                
                if ( isset($row['Msg_text']) && $row['Msg_text'] == 'OK' ) {
                    $messages[$t] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">" .
                                     "Sucessfully repaired the $table table.</span>";
                } else { // failed to repair the table
                    $messages[$t] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">" .
                                     "Failed to repair the $table table. " .
                                     "Error: {$row['Msg_text']}</span><br />";
                    self::$error_messages[$t] = "<p class=\"repair_log\">Failed to repair the $table table.</p>";
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
    public function examineQueries($queries = '') {
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
     * 
     * @param string $email Email to be validated
     * @access public
     * @return bool
     */
    public function checkValidEmail($email = '') {
        $hostname = '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';
        $pattern = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' . $hostname . '$/i';
        if ( !preg_match($pattern, $email) ) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if sample config (config.sample.inc.php) exists
     * 
     * @param string $file absolute file path
     * @access private
     */
    private function checkSampleConfig($file) {
        if ( !file_exists($file) ) {
            throw new InstallerError(
                '<p>Sorry, ThinkUp Installer need a config.sample.inc.php file to work from. '.
                'Please re-upload this file from your ThinkUp installation.</p>',
                self::ERROR_CONFIG_SAMPLE_MISSING
            );
        }
    }
    
    /**
     * Create config file
     * 
     * @param array $db_config
     * @param array $admin_user
     * @return bool  true if config successfuly created
     */
    public function createConfigFile($db_config, $admin_user) {
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';
        $config_file_exists = file_exists($config_file);
        
        // check sample configuration file
        // when config.inc.php is not exist
        if (!$config_file_exists) {
            $sample_config_filename = THINKUP_WEBAPP_PATH . 'config.sample.inc.php';
            try {
                self::checkSampleConfig($sample_config_filename);
            } catch (InstallerError $e) {
                return $e->showError(self::$controller);
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
                $message  = "<p>ThinkUp couldn't write <code>config.sample.inc.php</code> file. Either make ".
                            "<code>" . THINKUP_WEBAPP_PATH . "</code> writeable ";
                $message .= "or create the <code>config.sample.inc.php</code> ".
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
                $message .= '<input type="hidden" name="confirm_password" value="' . 
                                $admin_user['confirm_password'] . '" />';
                
                // submit button
                $message .= '<div class="clearfix append_20">' .
                            '<div class="grid_10 prefix_9 left">' .
                            '<input type="submit" name="Submit" class="tt-button '.
                                'ui-state-default ui-priority-secondary ui-corner-all" value="Next Step &raquo">' .
                            '</div></div></form>';
              
                self::$controller->diePage($message, 'File Configuration Error');
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
    public function getErrorMessages() {
        return self::$error_messages;
    }

    /**
     * Clear error messages.
     * 
     * @access public
     * @return void
     */
    public function clearErrorMessages() {
        self::$error_messages = array();
    }

    /**
     * Repairer does checking on step #1
     * 
     * @return bool
     */
    public function repairerCheckStep1() {
        if ( !self::checkStep1() ) {
            throw new InstallerError(
                "Requirements are not met. " .
                "Make sure your PHP version >= " . self::$required_version['php'] .
                ", you have cURL and GD extension installed, and template and log directories are writeable.",
                self::ERROR_REQUIREMENTS
            );
        }
        
        return true;
    }

    /**
     * Repairer does checking on files configuration existent
     * 
     * @return string Path file
     */
    public function repairerCheckConfigFile() {
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';
        
        if ( !file_exists($config_file) ) {            
            throw new InstallerError(
                '<p>Sorry, ThinkUp Repairer need a <code>config.inc.php</code> file to work from. ' .
                'Please upload this file to <code>' . THINKUP_WEBAPP_PATH . '</code> or ' .
                'copy / rename from <code>' . THINKUP_WEBAPP_PATH . 'config.sample.inc.php</code> to ' .
                '<code>' . THINKUP_WEBAPP_PATH . 'config.inc.php</code>. If you don\'t have permission to ' .
                'do this, you can reinstall ThinkUp by ' .
                'clearing out ThinkUp tables and then clicking '.
                '<a href="' . THINKUP_BASE_URL . 'install/">here</a>',
                self::ERROR_CONFIG_FILE_MISSING
            );
        }
        
        return $config_file;
    }

    /**
     * Repairer does checking on files configuration if
     * $THINKUP_CFG['repair'] has been defined or not
     * 
     * @param $config
     * 
     * @return bool
     */
    public function repairerIsDefined($config) {
        if ( !isset($config['repair']) or !$config['repair'] ) {
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
