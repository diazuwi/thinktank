<?php
require_once dirname(dirname(__FILE__)) . '/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Loader.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

define('DS', DIRECTORY_SEPARATOR);
define('THINKUP_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DS);
define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp' . DS);

/**
 * ThinkUp Installer Test Case
 * 
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class ThinkUpLoaderTestCase extends UnitTestCase {
  public $config_file_exists;
  public $config = array(
    'db_host' => '',
    'db_name' => '',
    'db_user' => '',
    'db_password' => '',
    'table_prefix' => '',
    'GMT_offset' => '',
    'sql_log_location' => '',
    'slow_query_log_threshold' => ''
  );
  
  public $specialClasses;
  
  function __construct() {
    $this->UnitTestCase('Loader class test');
    
    global $SITE_ROOT_PATH;
    define('THINKUP_BASE_URL', $SITE_ROOT_PATH);
    define('INSTALLER_ON_TEST', true);
    $this->config_file_exists = file_exists(THINKUP_WEBAPP_PATH . 'config.inc.php');
    $this->specialClasses = array(
      // interfaces
      'CrawlerPlugin' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.CrawlerPlugin.php',
      'FollowDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.FollowDAO.php',
      'FollowerCountDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.FollowerCountDAO.php',
      'InstallerDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.InstallerDAO.php',
      'InstanceDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.InstanceDAO.php',
      'LinkDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.LinkDAO.php',
      'OwnerDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.OwnerDAO.php',
      'OwnerInstanceDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.OwnerInstanceDAO.php',
      'PluginDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.PluginDAO.php',
      'PluginOptionDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.PluginOptionDAO.php',
      'PostDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.PostDAO.php',
      'PostErrorDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.PostErrorDAO.php',
      'ThinkUpPlugin' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.ThinkUpPlugin.php',
      'UserDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.UserDAO.php',
      'UserErrorDAO' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.UserErrorDAO.php',
      'WebappPlugin' => THINKUP_WEBAPP_PATH . 'model' . DS . 'interface.WebappPlugin.php',
      
      // Smarty has different filename
      'Smarty' => THINKUP_ROOT_PATH . 'extlib' . DS . 'Smarty-2.6.26' . DS .
                  'libs' . DS . 'Smarty.class.php',
      // twitterOauth
      'twitterOAuth' => THINKUP_ROOT_PATH . 'extlib' . DS . 'twitteroauth' . DS . 'twitteroauth.php'
    );
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
    Loader::unregister();
  }
  
  public function assertClassInstantiates($class) {
    try {
      new $class;
      if ( !$this->config_file_exists ) {
        $this->fail('Missing Configuration File');
      } else {
        $this->pass('Configuration File Exists');
      }
    } catch (Exception $e) {
      if ( !$this->config_file_exists ) {
        $this->pass('Missing Configuration File');
      } else {
        $this->fail('Configuration File Exists But Failed to Load Captcha');
      }
    }
  }
}
