<?php
require_once dirname(dirname(__FILE__)) . '/config.tests.inc.php';
require_once 'class.ThinkUpTestDatabaseHelper.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Loader.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

define('DS', DIRECTORY_SEPARATOR);
define('THINKUP_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DS);
define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp' . DS);
define('THINKUP_BASE_URL', $SITE_ROOT_PATH);
define('INSTALLER_ON_TEST', true);

/**
 * ThinkUp Installer Controller Test Case
 * 
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id
 */
class ThinkUpInstallerControllerTestCase extends UnitTestCase {
    var $db;
    var $model;
    var $controller;
    var $is_installed = 0;
    
    function __construct() {
        global $TEST_DATABASE_TYPE, $TEST_DATABASE, $TEST_DATABASE_HOST, $TEST_DATABASE_USER,
               $TEST_DATABASE_PASSWORD, $TEST_DATABASE_PORT, $TEST_DATABASE_SOCKET;
        
        $this->UnitTestCase('InstallerController class test');
        Loader::register();
        
        $this->controller = new InstallerController();
        $this->model = Installer::getInstance($this->controller);
        
        // check if ThinkUp is already installed
        if ( file_exists( THINKUP_WEBAPP_PATH . 'config.inc.php' ) ) {
            require_once THINKUP_WEBAPP_PATH . 'config.inc.php';
            $this->model->setDb($THINKUP_CFG);
            // check if ThinkUp is installed
            if ( $this->model->isThinkUpInstalled($THINKUP_CFG) && 
                 $this->model->checkPath($THINKUP_CFG) ) {
            
                $this->is_installed = 2; // show dashboard
            } else {
                $this->is_installed = 1; // error : installation isn't complete
            }
        }
        
        $this->config = array(
            'db_type' => $TEST_DATABASE_TYPE,
            'db_name' => $TEST_DATABASE,
            'db_host' => $TEST_DATABASE_HOST,
            'db_user' => $TEST_DATABASE_USER,
            'db_password' => $TEST_DATABASE_PASSWORD,
            'table_prefix' => '',
            'GMT_offset' => 7,
            'db_socket' => $TEST_DATABASE_SOCKET,
            'db_port' => $TEST_DATABASE_PORT
        );
        $dao = Installer::$dao[$TEST_DATABASE_TYPE];
        if ( is_object(Installer::$db) ) {
            Installer::$db->close();
        }
        
        $this->db = new $dao( $this->config );
    }
    
    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }
    
    /**
     * Create full table
     */
    public function create() {
        error_reporting(22527); //Don't show E_DEPRECATED PHP messages, split() is deprecated

        $str_queries = Installer::getInstallQueries($this->config['table_prefix']);
        $create_statements = split(";", $str_queries);
        foreach ($create_statements as $q) {
            if (trim($q) != '') {
                $this->db->exec($q.";");
            }
        }
    }
    
    public function drop() {
        //Delete test data by dropping all existing tables
        $tables = $this->db->showTables();
        foreach ( $tables as $table ) {
            $this->db->exec("DROP TABLE {$table}");
        }
    }
    
    public function insertAdmin() {
        $admin = array(
            'email' => 'admin@diazuwi.web.id', 'password' => 'password'
        );
        $this->db->insertAdmin($this->config['table_prefix'] . 'owners', $admin);
    }
}
