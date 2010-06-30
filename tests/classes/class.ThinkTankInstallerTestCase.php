<?php
require_once dirname(dirname(__FILE__)) . '/config.tests.inc.php';
require_once 'class.ThinkTankTestDatabaseHelper.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Loader.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

define('DS', DIRECTORY_SEPARATOR);
define('THINKTANK_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DS);
define('THINKTANK_WEBAPP_PATH', THINKTANK_ROOT_PATH . 'webapp' . DS);
define('THINKTANK_BASE_URL', $SITE_ROOT_PATH);
define('INSTALLER_ON_TEST', true);

/**
 * ThinkTank Installer Test Case
 * 
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class ThinkTankInstallerTestCase extends UnitTestCase {
  public $config;
  public $db;
  
  function __construct() {
    global $TEST_DATABASE, $TEST_DATABASE_HOST, $TEST_DATABASE_USER,
           $TEST_DATABASE_PASSWORD;
    
    $this->UnitTestCase('Installer class test');
    Loader::register();
    $this->config = array(
      'db_name' => $TEST_DATABASE,
      'db_host' => $TEST_DATABASE_HOST,
      'db_user' => $TEST_DATABASE_USER,
      'db_password' => $TEST_DATABASE_PASSWORD,
      'table_prefix' => ''
    );
    $this->db = new Database( $this->config );
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
                user_name varchar(200) NOT NULL,
                user_pwd varchar(200) NOT NULL,
                user_email varchar(200) NOT NULL,
                activation_code int(10) NOT NULL DEFAULT '0',
                joined date NOT NULL DEFAULT '0000-00-00',
                country varchar(100) NOT NULL,
                user_activated int(1) NOT NULL DEFAULT '0',
                is_admin int(1) NOT NULL DEFAULT '0',
                last_login date NOT NULL DEFAULT '0000-00-00',
                PRIMARY KEY  (id)
              )";
    $this->db->exec( sprintf($query, $this->config['table_prefix'] . 'owners') );
    
    if ( !empty($admin) ) {
      $query  =  "INSERT INTO %s ";
      $query .= " (`user_email`,`user_pwd`,`country`,`joined`,`activation_code`,`full_name`, `user_activated`, `is_admin`)";
      $query .= " VALUES ('%s', '%s', '%s', now(), '', '%s', 1, %d)";
      $this->db->exec(sprintf($query, 
        $this->config['table_prefix'] . 'owners',
        $admin['site_email'],
        md5($admin['password']),
        $admin['country'],
        $admin['name'],
        $admin['is_admin']
      ));
    }
  }
  
/**
 * Drop a table.
 */
  public function del($table) {
    global $TEST_DATABASE;
    
    $sql_result = $this->db->exec('SHOW TABLES');
    $tables = array();
    while ( $row = @mysql_fetch_array($sql_result) ) {
      $tables[] = $row[0];
    }
    
    if ( in_array($table, $tables) ) {
      $this->db->exec("DROP TABLE {$table}");
    }
  }

/**
 * Drop all tables in test database.
 */
  public function drop() {    
    global $TEST_DATABASE;

    $sql_result = $this->db->exec('SHOW TABLES');
    while ( $row = @mysql_fetch_array($sql_result) ) {
        $q = "DROP TABLE ".$row['Tables_in_'.$TEST_DATABASE];
        $this->db->exec("DROP TABLE {$row[0]}");
    }
  }
}
?>