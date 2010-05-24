<?php
class InstallerError extends Exception {
  function showError() {
    $title = '';
    switch ( $this->getCode() ) {
      case Installer::ERROR_FILE_NOT_FOUND:
        $title = 'File Not Found';
        break;
      case Installer::ERROR_CLASS_NOT_FOUND:
        $title = 'Class Not Found';
        break;
      case Installer::ERROR_DB_CONNECT:
        $title = 'Database Error';
      case Installer::ERROR_DB_SELECT:
        $title = 'Database Error';
        break;
      case Installer::ERROR_DB_TABLES_EXIST:
        $title = 'ThinkTank Tables Exist';
        break;
      case Installer::ERROR_SITE_NAME:
        $title = 'Invalid Site Name';
        break;
      case Installer::ERROR_SITE_EMAIL:
        $title = 'Invalid Site Email';
        break;
      case Installer::ERROR_CONFIG_SAMPLE_MISSING:
        $title = 'Missing Sample Configuration File';
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
      try {
        throw new InstallerError(
          'File ' . $file . ' not found.', Installer::ERROR_FILE_NOT_FOUND
        );
      } catch (InstallerError $e) {
        $e->showError();
      }
    }
    
    require $filename;
    
    if ( !class_exists($class, FALSE) ) {
      try {
        throw new InstallerError(
          'Class ' . $class . ' not found.', Installer::ERROR_CLASS_NOT_FOUND
        );
      } catch (InstallerError $e) {
        $e->showError();
      }
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
  const ERROR_DB_TABLES_EXIST = 5;
  const ERROR_SITE_NAME = 6;
  const ERROR_SITE_EMAIL = 7;
  const ERROR_CONFIG_SAMPLE_MISSING = 8;
  const ERROR_CONFIG_SOURCE_ROOT_PATH = 9;
  const ERROR_CONFIG_SMARTY_PATH = 10;
  const ERROR_CONFIG_LOG_LOCATION = 11;
  
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
 * List of ThinkTank tables
 *
 * @var array
 */  
  public $tables = array(
    'follows', 'instances', 'links', 'owners', 'owner_instances',
    'plugins', 'plugin_options', 'posts', 'post_errors', 'users',
    'user_errors'
  );
  
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

/**
 * Check path existent
 * @param array $config
 */
  function checkPath($config) {
    // check if $THINKTANK_CFG related to path exists
    if ( !is_dir($config['source_root_path']) ) {
      throw new InstallerError(
        "<p>ThinkTank's source root directory is not found</p>",
        self::ERROR_CONFIG_SOURCE_ROOT_PATH
      );
    }
    if ( !is_dir($config['smarty_path']) ) {
      throw new InstallerError(
        "<p>ThinkTank's smarty directory is not found</p>",
        self::ERROR_CONFIG_SMARTY_PATH
      );
    }
    if ( !is_dir(substr($config['log_location'], 0, -11)) ) {
      throw new InstallerError(
        "<p>ThinkTank log directory is not found</p>",
        self::ERROR_CONFIG_LOG_LOCATION
      );
    }
    
    return true;
  }

/**
 * Check all requirements on step 1
 * Check PHP version, cURL, 
 * @access public
 * @return bool
 */  
  function checkStep1() {
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
    
    return ($version_compat && $lib_depends_ret && $writeable_permission_ret);
  }

/**
 * Check database
 * @param array $params database credentials
 * @return mixed
 */
  function checkDb($params) {
    $c = @mysql_connect($params['db_host'], $params['db_user'], $params['db_password'], true);
    if (!$c) {
      throw new InstallerError(
        '<p>Failed establishing database connection. Probably either your username, ' .
        '<code>' . $params['db_user'] . '</code>, and password, <code>' . $params['db_password'] .
        '</code>, you have provided are incorrect or we can\'t connect the' .
        'database server at <code>' . $params['db_host'] . '</code>' .
        '</p>' ,
        self::ERROR_DB_CONNECT
      );
    }
    
    if (!@mysql_select_db($params['db_name'], $c)) {
      throw new InstallerError(
        "<p>We were able to connect to the database server (which means " .
        "your username and password is okay) but not able to select the <code>" . 
        $params['db_name'] . "</code> database.</p> ", 
        self::ERROR_DB_SELECT
      );
    }
    
    return true;
  }

/**
 * Check table existent
 * @param array $config
 */  
  function checkTable($config) {
    $db = new Database($config);
    $c  = $db->getConnection();
    
    if (!$c) {
      throw new InstallerError(
        '<p>Failed establishing database connection. Probably either your username, ' .
        '<code>' . $config['db_user'] . '</code>, and password, <code>' . $config['db_password'] .
        '</code>, you have provided are incorrect or we can\'t connect the' .
        'database server at <code>' . $config['db_host'] . '</code>' .
        '</p>' ,
        self::ERROR_DB_CONNECT
      );
    }
    
    $tables = $db->exec('SHOW TABLES');
    
    foreach ( self::tables as $table ) {
      if ( in_array($config['table_prefix'] . $table, $tables) ) {
        // TODO: when table already exists, ask for repairing
        throw new InstallerError(
          "<p>ThinkTank tables exist</p>",
          self::ERROR_DB_TABLES_EXIST
        );
      }
    }
    
    return true;
  }

/**
 * Validate Site Name
 * @param string $sitename Site name to check
 * @access private
 * @return mixed
 */
  private function __checkSiteName($sitename = '') {
    if ( empty($sitename) ) {
      throw new InstallerError(
        "<p>Please provide valid site name.</p>",
        self::ERROR_SITE_NAME
      );
    }
    
    return true;
  }

/**
 * Validate email
 * @param string $email Email to be validated
 * @return mixed
 */  
  private function __checkValidEmail($email = '') {
    if ( !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) ) {
      throw new InstallerError(
        "<p>Please provide valid email.</p>",
        self::ERROR_SITE_EMAIL
      );
    }
    
    return true;
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
 * Check if sample config (config.sample.inc.php) exists
 * @param string $file absolute file path
 * @access private
 * @return void
 */
  private function __checkSampleConfig($file) {
    if ( !file_exists($file) ) {
      throw new InstallerError(
        '<p>Sorry, ThinkTank Installer need a config.sample.inc.php file to work from. '.
        'Please re-upload this file from your ThinkTank installation.</p>',
        self::ERROR_CONFIG_SAMPLE_MISSING
      );
    }
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
    // make sure we have passed step 1
    if ( !self::checkStep1() ) {
      self::__step1();
      self::$__view->display('installer.step.1.tpl');
      die;
    }
    
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
    $config_file_exists = false;
    $config_file = THINKTANK_WEBAPP_PATH . 'config.inc.php';
    // check if we have made config.inc.php
    if ( file_exists($config_file) && !$_POST ) {
      $config_file_exists = true;
      require $config_file;
      $db_config['db_name']   = $THINKTANK_CFG['db_name'];
      $db_config['db_user']   = $THINKTANK_CFG['db_user'];
      $db_config['db_password'] = $THINKTANK_CFG['db_password'];
      $db_config['db_host']   = $THINKTANK_CFG['db_host'];
      $db_config['table_prefix'] = $THINKTANK_CFG['table_prefix'];
      $site_name    = $THINKTANK_CFG['app_title'];
      $site_email   = trim($_GET['site_email']);
    } else {
      // trim each posted value
      $db_config['db_name']   = trim($_POST['db_name']);
      $db_config['db_user']   = trim($_POST['db_user']);
      $db_config['db_password'] = trim($_POST['db_passwd']);
      $db_config['db_host']   = trim($_POST['db_host']);
      $db_config['table_prefix'] = trim($_POST['db_prefix']);
      $site_email   = trim($_POST['site_email']);
      $site_name    = trim($_POST['site_name']);
      
      if ( empty($db_config['table_prefix']) ) {
        $db_config['table_prefix'] = 'tt_';
      }
    }
    
    try {
      self::checkDb($db_config);
    } catch (InstallerError $e) {
      $e->showError();
    }
    
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
    
    // check sample configuration file
    // when config.inc.php is not exist
    if (!$config_file_exists) {
      $sample_config_filename = THINKTANK_WEBAPP_PATH . 'config.sample.inc.php';
      try {
        self::__checkSampleConfig($sample_config_filename);
      } catch (InstallerError $e) {
        $e->showError();
      }
    
      // read sample configuration file and replace some lines
      $sample_config = file($sample_config_filename);
      foreach ($sample_config as $line_num => $line) {
        switch ( substr($line, 14, 30) ) {
          case "['app_title']                 ":
            $sample_config[$line_num] = str_replace("'ThinkTank'", "'$site_name'", $line);
            break;
          case "['log_location']              ":
            $sample_config[$line_num] = str_replace(
              "'/your-server-path-to/thinktank/logs/crawler.log'", 
              "'" . THINKTANK_ROOT_PATH . "logs/crawler.log'", $line
            );
            break;
          case "['sql_log_location']          ":
            $sample_config[$line_num] = str_replace(
              "'/your-server-path-to/thinktank/logs/sql.log'", 
              "'" . THINKTANK_ROOT_PATH . "logs/sql.log'", $line
            );
            break;
          case "['site_root_path']            ":
            $sample_config[$line_num] = str_replace(
              "'/'", "'" . THINKTANK_BASE_URL . "'", $line
            );
            break;
          case "['source_root_path']          ":
            $sample_config[$line_num] = str_replace(
              "'/your-server-path-to/thinktank/'",
              "'" . THINKTANK_ROOT_PATH . "'", $line
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
              "'your_thinktank_database_name'", "'" . $db_config['db_name'] . "'", $line
            );
            break;
          case "['table_prefix']              ":
            $sample_config[$line_num] = str_replace(
              "'tt_'", "'" . $db_config['table_prefix'] . "'", $line
            );
            break;
        }
      } // end foreach
    
      if ( !is_writable(THINKTANK_WEBAPP_PATH) ) {
        /* if not writeable user should create config.sample.inc.php manually */
        $message  = "<p>ThinkTank couldn't write <code>config.sample.inc.php</code> file! Make sure <code>".
                    THINKTANK_WEBAPP_PATH."</code> writeable! ";
        $message .= "Or you can create the <code>config.sample.inc.php</code> manually and paste the following text into it.</p><br>";
        $message .= '<textarea cols="120" rows="15">';
        foreach ($sample_config as $line) {
          $message .= htmlentities($line);
        }
        $message .= '</textarea><br>';
        $message .= "<p>After you've done that, click the Next Step &raquo;</p>";
        $message .= '<div class="clearfix"><div class="grid_10 prefix_8 left">';
        $message .= '<div class="next_step tt-button ui-state-default ui-priority-secondary ui-corner-all">';
        $message .= '<a href="index.php?step=3&amp;site_email=' . $site_email . '">Next Step &raquo;</a>';
        $message .= '</div></div></div>';
        
        self::diePage($message, 'File Configuration Error');
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
    
    // check tables
    try {
      self::checkTable($db_config);
    } catch (InstallerError $e) {
      $e->showError();
    }
    
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
    // chec if compile_dir is set
    if ( !isset(self::$__view->compile_dir) ) {
      echo '<strong>ERROR: Couldn\'t instantiate SmartyInstaller or Smarty!</strong><br>';
      echo '<p>Make sure Smarty related classes exist.<br>';
      die();
    }
    
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