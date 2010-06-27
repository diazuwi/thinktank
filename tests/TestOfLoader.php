<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Loader.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

define('DS', DIRECTORY_SEPARATOR);
define('THINKTANK_ROOT_PATH', dirname(dirname(__FILE__)) . DS);
define('THINKTANK_WEBAPP_PATH', THINKTANK_ROOT_PATH . 'webapp' . DS);

/**
 * Test of Loader class
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 *
 */
class TestOfLoader extends UnitTestCase {
/**
 * Constructor
 */
  function __construct() {
      $this->UnitTestCase('Loader class test');
  }
  
  public function tearDown() {
    Loader::unregister();
  }
  
  public function testLoaderRegisterDefault() {
    $loader = Loader::register();
    
    // check if Loader is registered to spl autoload
    $this->assertTrue($loader, 'Loader is registered to spl autoload');
    
    // check default lookup path without additionalPath
    $this->assertEqual( Loader::getLookupPath(), array(
      THINKTANK_WEBAPP_PATH . 'model' . DS, 
      THINKTANK_WEBAPP_PATH . 'controller' . DS
    ));
    
    // check special classes
    $this->assertEqual( Loader::getSpecialClasses(), array(
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
    ));
  }
  
  public function testLoaderRegisterWithStringAdditionalPath() {
    // Loader with string of path as additional path
    $loader = Loader::register(THINKTANK_ROOT_PATH . 'tests' . DS . 'classes');
    
    // check if Loader is registered to spl autoload
    $this->assertTrue($loader, 'Loader is registered to spl autoload');
    
    // check lookup path with single additionalPath
    $this->assertEqual( Loader::getLookupPath(), array(
      THINKTANK_WEBAPP_PATH . 'model' . DS, 
      THINKTANK_WEBAPP_PATH . 'controller' . DS,
      THINKTANK_ROOT_PATH . 'tests' . DS . 'classes'
    ));
  }
  
  public function testLoaderRegisterWithArrayAdditionalPath() {
    // Loader with array of path as additional path
    $loader = Loader::register(array(
      THINKTANK_ROOT_PATH . 'tests',
      THINKTANK_ROOT_PATH . 'tests' . DS . 'classes'
    ));
    
    // check if Loader is registered to spl autoload
    $this->assertTrue($loader, 'Loader is registered to spl autoload');
    
    // check lookup path with array additionalPath
    $this->assertEqual( Loader::getLookupPath(), array(
      THINKTANK_WEBAPP_PATH . 'model' . DS, 
      THINKTANK_WEBAPP_PATH . 'controller' . DS,
      THINKTANK_ROOT_PATH . 'tests',
      THINKTANK_ROOT_PATH . 'tests' . DS . 'classes'
    ));
  }
  
  public function testLoaderUnregister() {
    Loader::register();
    $unreg = Loader::unregister();
    
    // check if Loader is succesfully unregistered
    $this->assertTrue($unreg, 'Unregister Loader');
    
    // make sure lookup path and special classes are null
    $this->assertNull(Loader::getLookupPath());
    $this->assertNull(Loader::getSpecialClasses());
  }
  
  public function testLoaderInstantiateClassesWithoutConfig() {
    global $SITE_ROOT_PATH;
    define('THINKTANK_BASE_URL', $SITE_ROOT_PATH);
    
    Loader::register();
    
    $this->assertIsA(new Crawler, 'Crawler');
    $this->assertIsA(new DAOFactory, 'DAOFactory');
  }
  
  public function testLoaderInstantiaiteClassesWithConfig() {
    
  }
}
?>