<?php
require_once dirname(dirname(__FILE__)) . '/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Loader.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

define('DS', DIRECTORY_SEPARATOR);
define('THINKTANK_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DS);
define('THINKTANK_WEBAPP_PATH', THINKTANK_ROOT_PATH . 'webapp' . DS);

/**
 * ThinkTank Installer Test Case
 * 
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class ThinkTankLoaderTestCase extends UnitTestCase {
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
    define('THINKTANK_BASE_URL', $SITE_ROOT_PATH);
    define('INSTALLER_ON_TEST', true);
    $this->config_file_exists = file_exists(THINKTANK_WEBAPP_PATH . 'config.inc.php');
    $this->specialClasses = array(
      // interfaces
      'Controller' => THINKTANK_WEBAPP_PATH . 'controller' . DS . 'interface.Controller.php',
      'CrawlerPlugin' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.CrawlerPlugin.php',
      'FollowDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.FollowDAO.php',
      'InstanceDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.InstanceDAO.php',
      'LinkDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.LinkDAO.php',
      'OwnerDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.OwnerDAO.php',
      'OwnerInstanceDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.OwnerInstanceDAO.php',
      'PostDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.PostDAO.php',
      'PostErrorDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.PostErrorDAO.php',
      'ThinkTankPlugin' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.ThinkTankPlugin.php',
      'UserDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.UserDAO.php',
      'UserErrorDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.UserErrorDAO.php',
      'WebappPlugin' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.WebappPlugin.php',
      
      // Smarty has different filename
      'Smarty' => THINKTANK_ROOT_PATH . 'extlib' . DS . 'Smarty-2.6.26' . DS .
                  'libs' . DS . 'Smarty.class.php',
      
      // Class that belongs to other class file
      // TODO: remove below when it lives in its own file
      'UserDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.User.php',
      'UserErrorDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.User.php',
      'PluginDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.Plugin.php',
      'OwnerDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.Owner.php',
      'OwnerInstanceDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.OwnerInstance.php',
      'PostDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.Post.php',
      'PostErrorDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.Post.php',
      'LinkDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.Link.php'
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