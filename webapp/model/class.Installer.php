<?php
class InstallerError extends Exception {
  function showError() {
    $title = '';
    $message = '';
    switch ( $this->getCode() ) {
      case Installer::ERROR_FILE_NOT_FOUND:
      case Installer::ERROR_CLASS_NOT_FOUND:
      case Installer::ERROR_DB_CONNECT:
        $title = 'Database Error';
      case Installer::ERROR_DB_SELECT:
        $title = 'Database Error';
        break;
      case Installer::ERROR_SITE_NAME:
        $title = 'Invalid Site Name';
        break;
      case Installer::ERROR_SITE_EMAIL:
        $title = 'Invalid Site Email';
        break;
    }
    
    Installer::diePage($this->getMessage(), $title);
  }
}

class Loader {
  static public function register() {
    return spl_autoload_register(array(
      __CLASS__, 'load'
    ));
  }
  
  static public function load($class) {
    if ( class_exists($class, FALSE) ) {
      return;
    }
    
    $lookupPath = array(
      THINKTANK_WEBAPP_PATH . 'model' . DS, 
      THINKTANK_ROOT_PATH . 'extlib' . DS . 'Smarty-2.6.26' . DS .
      'libs' . DS, THINKTANK_WEBAPP_PATH . 'install' . DS
    );
    // Smarty has different filename
    if ( $class == 'Smarty' ) {
      $file = 'Smarty.class.php';
    } else {
      $file = 'class.' . $class . '.php';
    }
    $file_found = false;
    
    foreach ( $lookupPath as $path ) {
      if ( file_exists($path . $file) ) {
        $file_found = true;
        $filename = $path . $file;
        break;
      }
    }
    
    if ( !$file_found ) {
      throw new InstallerError('Error: File ' . $file . ' not found.');
    }
    
    require $filename;
    
    if ( !class_exists($class, FALSE) ) {
      throw new InstallerError('Error: Class ' . $class . ' not found.');
    }
    
    return true;
  }
}

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
  
  const ERROR_FILE_NOT_FOUND = 1;
  const ERROR_CLASS_NOT_FOUND = 2;
  const ERROR_DB_CONNECT = 3;
  const ERROR_DB_SELECT = 4;
  const ERROR_SITE_NAME = 5;
  const ERROR_SITE_EMAIL = 6;
  
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
      
      // use lazy loading
      Loader::register();
      
      // instantiate SmartyInstaller
      self::$__view = new SmartyInstaller();
      self::$__view->assign('base_url', THINKTANK_BASE_URL);
      self::$__view->assign('favicon', THINKTANK_BASE_URL . 'assets/img/favicon.ico');
      
      // get required version of php and mysql
      // and set current version
      require_once (THINKTANK_WEBAPP_PATH . 'install' . DS . 'version.php');
      
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
 * @return array
 */
  function checkDependency() {
    $ret = array('curl' => false, 'gd' => false);
    // check curl
    if ( extension_loaded('curl') && function_exists('curl_exec') ) {
      $ret['curl'] = true;
    }
    
    // check GD
    if ( extension_loaded('gd') && function_exists('gd_info') ) {
      $ret['gd'] = true;
    }
    
    return $ret;
  }

/**
 * Check if log and template directories are writeable
 * @access public
 * @return array
 */  
  function checkPermission() {
    $ret = array(
      'logs' => false, 'compiled_view' => false, 'cache' => false
    );
    
    if ( is_writable(THINKTANK_ROOT_PATH . 'logs') ) {
      $ret['logs'] = true;
    }
    
    if ( is_writable(self::$__view->compile_dir) ) {
      $ret['compiled_view'] = true;
    }
    
    if ( is_writable(self::$__view->compile_dir . 'cache') ) {
      $ret['cache'] = true;
    }
    
    return $ret;
  }
  
  function checkAll() {
    $version_compat = $this->checkVersion();
    $lib_depends = $this->checkDependency();
    $writeable_permission = $this->checkPermission();
    
    return ($version_compat && $lib_depends && $writeable_permission);
  }

/**
 * Check database
 * @param array $params database credentials
 * @access private
 * @return void
 */
  private function __checkDb($params) {
    $c = @mysql_connect($params['host'], $params['user'], $params['passwd'], true);
    if (!$c) {
      throw new InstallerError(
        '<p>Failed establishing database connection. Probably either your username, ' .
        '<code>' . $params['user'] . '</code>, and password, <code>' . $params['passwd'] .
        '</code>, you have provided are incorrect or we can\'t connect the' .
        'database server at <code>' . $params['host'] . '</code>' .
        '</p>' ,
        self::ERROR_DB_CONNECT
      );
    }
    
    if (!@mysql_select_db($params['name'], $c)) {
      throw new InstallerError(
        "<p>We were able to connect to the database server (which means " .
        "your username and password is okay) but not able to select the <code>" . 
        $params['name'] . "</code> database.</p> ", 
        self::ERROR_DB_SELECT
      );
    }
  }

/**
 * Validate Site Name
 * @param string $sitename Site name to check
 * @access private
 * @return bool
 */
  private function __checkSiteName($sitename = '') {
    if ( empty($sitename) ) {
      throw new InstallerError(
        "<p>Please provide valid site name.</p>",
        self::ERROR_SITE_NAME
      );
    }
  }

/**
 * Validate email
 * @param string $email Email to be validated
 * @return mixed string of $email when valid and false when $email is not valid
 */  
  private function __checkValidEmail($email) {
    if ( !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) ) {
      throw new InstallerError(
        "<p>Please provide valid email.</p>",
        self::ERROR_SITE_EMAIL
      );
    }
  }

/**
 * Generate random password for step 4
 * @param int $length the length of generated random password
 * @access private
 * @return string $pass random password
 */  
  private function __generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
    srand( (double)microtime()*1000000 );
    $i = 0;
    $pass = '';
    
    while ($i++ <= $length) {
      $num   = rand() % 64;
      $tmp   = substr($chars, $num, 1);
      $pass .= $tmp;
    }
    
    return $pass;
  }

/**
 * Step 1 - Check requirements
 * @access private
 * @return void
 */  
  private function __step1() {
    $php_compat = 0;
    if ( $this->checkVersion() ) {
      $php_compat = 1;
    }
    self::$__view->assign('php_compat', $php_compat);
    self::$__view->assign('php_required_version', self::$__requiredVersion['php']);
    self::$__view->assign('libs', self::checkDependency());
    self::$__view->assign('permission', self::checkPermission());
    $writeable_directories = array(
      'logs' => THINKTANK_ROOT_PATH . 'logs',
      'compiled_view' => self::$__view->compile_dir,
      'cache' => self::$__view->compile_dir . 'cache'
    );
    self::$__view->assign('writeable_directories', $writeable_directories);
    self::$__view->assign('subtitle', 'Requirements Check');
  }

/**
 * Step 2 - Setup database and site configuration
 * @access private
 * @return void
 */  
  private function __step2() {
    self::$__view->assign('db_name', 'thinktank');
    self::$__view->assign('db_user', 'username');
    self::$__view->assign('db_passwd', 'password');
    self::$__view->assign('db_host', 'localhost');
    self::$__view->assign('db_prefix', 'tt_');
    self::$__view->assign('site_name', 'My ThinkTank');
    self::$__view->assign('site_email', 'username@example.com');
    self::$__view->assign('subtitle', 'Setup Database and Site Configuration');
  }
  
/**
 * Step 3 - Populate Database and Finishing
 * @access private
 * @return void
 */  
  private function __step3() {
    // trim each posted value
    $db['name'] = trim($_POST['db_name']);
    $db['user'] = trim($_POST['db_user']);
    $db['passwd'] = trim($_POST['db_passwd']);
    $db['host'] = trim($_POST['db_host']);
    $db['prefix'] = trim($_POST['db_prefix']);
    
    try {
      self::__checkDb($db);
    } catch (InstallerError $e) {
      $e->showError();
    }
    
    $site_email = trim($_POST['site_email']);
    $site_name = trim($_POST['site_name']);
    
    try {
      self::__checkSiteName($site_name);
      
      try {
        self::__checkValidEmail($site_email);
      } catch (InstallerError $e) {
        $e->showError();
      }
    } catch (InstallerError $e) {
      $e->showError();
    }
    
    // writing configuration file
    
    self::$__view->assign('username', $site_email);
    self::$__view->assign('password', self::__generatePassword());
    self::$__view->assign('login_url', THINKTANK_BASE_URL . 'session/login.php');
    self::$__view->assign('subtitle', 'Finish');
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
 * @return void
 */
  function installPage($step = 1) {
    $methodName = '__step' . $step;
    if ( !method_exists(__CLASS__, $methodName) ) {
      $step = 1;
      $methodName = '__step1';
    }
    self::$methodName();
    self::$__view->display('installer.step.' . $step . '.tpl');
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