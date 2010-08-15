<?php
require_once 'classes/class.ThinkUpLoaderTestCase.php';
/**
 * Test of Loader class
 * 
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class TestOfLoader extends ThinkUpLoaderTestCase {  
    public function testLoaderRegisterDefault() {
        $loader = Loader::register();
        
        // check if Loader is registered to spl autoload
        $this->assertTrue($loader, 'Loader is registered to spl autoload');
        
        // check default lookup path without additionalPath
        $this->assertEqual( Loader::getLookupPath(), array(
            THINKUP_WEBAPP_PATH . 'model' . DS, 
            THINKUP_WEBAPP_PATH . 'controller' . DS
        ));
        
        // check special classes
        $this->assertEqual( Loader::getSpecialClasses(), $this->special_classes);
    }
  
    public function testLoaderRegisterWithStringAdditionalPath() {
        // Loader with string of path as additional path
        $loader = Loader::register(THINKUP_ROOT_PATH . 'tests' . DS . 'classes');
        
        // check if Loader is registered to spl autoload
        $this->assertTrue($loader, 'Loader is registered to spl autoload');
        
        // check lookup path with single additionalPath
        $this->assertEqual( Loader::getLookupPath(), array(
            THINKUP_WEBAPP_PATH . 'model' . DS, 
            THINKUP_WEBAPP_PATH . 'controller' . DS,
            THINKUP_ROOT_PATH . 'tests' . DS . 'classes'
        ));
    }
  
    public function testLoaderRegisterWithArrayAdditionalPath() {
        // Loader with array of path as additional path
        $loader = Loader::register(array(
            THINKUP_ROOT_PATH . 'tests',
            THINKUP_ROOT_PATH . 'tests' . DS . 'classes'
        ));
        
        // check if Loader is registered to spl autoload
        $this->assertTrue($loader, 'Loader is registered to spl autoload');
        
        // check lookup path with array additionalPath
        $this->assertEqual( Loader::getLookupPath(), array(
            THINKUP_WEBAPP_PATH . 'model' . DS, 
            THINKUP_WEBAPP_PATH . 'controller' . DS,
            THINKUP_ROOT_PATH . 'tests',
            THINKUP_ROOT_PATH . 'tests' . DS . 'classes'
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
  
    public function testLoaderInstantiateClasses() {
        Loader::register();
        
        try {
            // test classes that use Config
            $this->assertClassInstantiates(new Captcha);
        } catch (Exception $e) {}
        
        $this->assertIsA(new Crawler, 'Crawler');
        $this->assertIsA(new DAOFactory, 'DAOFactory');
        
        $controller = new InstallerController();
        $this->assertIsA($controller, 'InstallerController');
        
        $this->assertIsA(Installer::getInstance($controller), 'Installer');
        $this->assertIsA(Config::getInstance(), 'Config');
        $this->assertIsA(Logger::getInstance('/tmp/test.log'), 'Logger');
    }
}
