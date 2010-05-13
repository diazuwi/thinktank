<?php
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
  private $__errorMessages = array();
  
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
      
      // instantiate SmartyInstaller 
      require_once (THINKTANK_ROOT_PATH . 'extlib/Smarty-2.6.26/libs/Smarty.class.php');
      require_once 'class.SmartyInstaller.php';
      self::$__view = new SmartyInstaller();
      self::$__view->assign('base_url', THINKTANK_BASE_URL);
      self::$__view->assign('favicon', THINKTANK_BASE_URL . 'assets/img/favicon.ico');
      
      // get required version of php and mysql
      // and set current version
      require_once (THINKTANK_WEBAPP_PATH . 'install/version.php');
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
    $ret = version_compare( phpversion(), $this->__requiredVersion['php'], '>=' );
    
    return $ret;
  }

/**
 * Check GD and cURL
 * @return bool True when libs dependency available
 */
  function checkDependency() {
    $ret = false;
    // check curl
    if ( !function_exists('curl_exec') ) {
      
    }
    
    // check GD
    
    return $ret;
  }
  
  function checkPermission() {
    $ret = false;
    
    return $ret;
  }
  
  function checkAll() {
    $version_compat = $this->checkVersion();
    $lib_depends = $this->checkDependency();
    $writeable_permission = $this->checkPermission;
    
    return ($version_compat && $lib_depends && $writeable_permission);
  }
  
/**
 * Get error messages.
 * 
 * @access public
 */
  function getErrorMessages() {
    return $this->__errorMessages;
  }
  
/**
 * Installation steps page
 * @param int $step Current step
 */
  function installPage($step) {
    switch ($step) {
      case 1:
        $php_compat = 0;
        if ( $this->checkVersion() ) {
          $php_compat = 1;
        }
        self::$__view->assign('php_compat', $php_compat);
        break;
      case 2:
        break;
      case 3:
        break;
      case 4:
        break;
    }
    self::$__view->display('installer.step.tpl');
  }

/**
 * Die with page formatted, inspired by wp_die()
 * Formatting happens when Smarty is available
 * 
 * @param string $message Content to be displayed
 * @param string $title Title on browser
 */
  function diePage($message, $title = '') {
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