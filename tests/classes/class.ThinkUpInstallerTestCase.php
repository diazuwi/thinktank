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
 * ThinkUp Installer Test Case
 * 
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class ThinkUpInstallerTestCase extends UnitTestCase {
  public $config;
  public $db;
  public $controller;
  
  function __construct() {
    global $TEST_DATABASE_TYPE, $TEST_DATABASE, $TEST_DATABASE_HOST, $TEST_DATABASE_USER,
           $TEST_DATABASE_PASSWORD, $TEST_DATABASE_PORT, $TEST_DATABASE_SOCKET;
    
    $this->UnitTestCase('Installer class test');
    Loader::register();
    $this->controller = new InstallerController();
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
    $this->db = new $dao( $this->config );
  }
  
  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }
  
/**
 * Create a table
 */
  public function create($table) {
    global $TEST_DATABASE;
    
    $query = "CREATE TABLE %s (" .
             "id int(11) NOT NULL AUTO_INCREMENT," .
             "PRIMARY KEY  (id)" .
             ")";
    if ( is_string($table) && !empty($table) ) {
      $this->db->exec( sprintf($query, $this->config['table_prefix'] . $table) );
    }
    
    if ( is_array($table) && !empty($table) ) {
      foreach ($table as $t) {
        $this->db->exec( sprintf($query, $this->config['table_prefix'] . $t) );
      }
    }
  }
  
  public function createAdminTable($admin = array()) {
    global $TEST_DATABASE;
    
    $query = "CREATE TABLE %s (
                id int(20) NOT NULL AUTO_INCREMENT,
                full_name varchar(200) NOT NULL,
                pwd varchar(200) NOT NULL,
                email varchar(200) NOT NULL,
                activation_code int(10) NOT NULL DEFAULT '0',
                joined date NOT NULL DEFAULT '0000-00-00',
                is_activated int(1) NOT NULL DEFAULT '0',
                is_admin int(1) NOT NULL DEFAULT '0',
                last_login date NOT NULL DEFAULT '0000-00-00',
                PRIMARY KEY  (id)
              )";
    $this->db->exec( sprintf($query, $this->config['table_prefix'] . 'owners') );
    
    if ( !empty($admin) ) {
      $this->db->insertAdmin($this->config['table_prefix'] . 'owners', $admin);
    }
  }
  
/**
 * Drop a table.
 */
  public function del($table) {
    $tables = $this->db->showTables();
    
    if ( in_array($table, $tables) ) {
      $this->db->exec("DROP TABLE {$table}");
    }
  }

/**
 * Drop all tables in test database.
 */
  public function drop() {
    $tables = $this->db->showTables();
    foreach ( $tables as $table ) {
        $this->db->exec("DROP TABLE {$table}");
    }
  }
}
