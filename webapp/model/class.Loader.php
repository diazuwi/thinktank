<?php
/**
 * Loader Model for Lazy Loading classes
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 *
 */
class Loader {

/**
 * Lookup path for classes and interfaces.
 * 
 * @var array
 * @access private
 */  
  static private $__lookupPath;

/**
 * Some classes has special filename that don't follow the convention
 * or classes that included in another file class or interfaces.
 * The value will be assigned inside __setLookupPath method.
 */  
  static private $__specialClasses;
  
  static private $__installer;

/**
 * Register current script to use lazy loading classes
 * @param mixed $additionalPath Additional lookup path for classes
 * @return bool true
 * @access public
 */  
  static public function register($additionalPath = '') {
    if ( is_null(self::$__lookupPath) ) {
      self::__setLookupPath($additionalPath);
    }
     
    return spl_autoload_register(array(
      __CLASS__, 'load'
    ));
  }

/**
 * Set additional lookup path classes
 * @param mixed $additionalPath Additional lookup path for classes
 * @return bool always true
 * @access private
 */  
  static private function __setLookupPath($additionalPath = '') {
    if ( !defined('DS') ) {
      define('DS', DIRECTORY_SEPARATOR);
    }
    // check two required named constants
    if ( !defined('THINKTANK_ROOT_PATH') ) {
      define('THINKTANK_ROOT_PATH', dirname(dirname(__FILE__)) . DS);
    }
    
    if ( !defined('THINKTANK_WEBAPP_PATH') ) {
      define('THINKTANK_WEBAPP_PATH', THINKTANK_ROOT_PATH . 'webapp');
    }
    
    // set default lookup path for classes
    self::$__lookupPath = array(
      THINKTANK_WEBAPP_PATH . 'model' . DS, 
      THINKTANK_WEBAPP_PATH . 'controller' . DS
    );
    
    // set default lookup path for special classes
    self::$__specialClasses = array(
      // interfaces
      'CrawlerPlugin' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.CrawlerPlugin.php',
      'InstanceDAO' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.InstanceDAO.php',
      'ThinkTankPlugin' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.ThinkTankPlugin.php',
      'WebappPlugin' => THINKTANK_WEBAPP_PATH . 'model' . DS . 'interface.WebappPlugin.php',
      'Controller' => THINKTANK_WEBAPP_PATH . 'controller' . DS . 'interface.Controller.php',
      
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
    
    // one path is given in param as a string
    if ( is_string($additionalPath) && !empty($additionalPath) ) {
      // this is better than array_push
      self::$__lookupPath[] = $additionalPath;
    }
    
    // array is passed
    if ( is_array($additionalPath) && !empty($additionalPath) ) {
      foreach ( $additionalPath as $path ) {
        self::$__lookupPath[] = $additionalPath;
      }
    }
    
    return true;
  }

/**
 * Get lookup path
 * @return array of lookup path
 * @access public
 */  
  public function getLookupPath() {
    return self::$__lookupPath;
  }

/**
 * Get special classes files
 * @return array of special classes path files
 * @access public
 */
  public function getSpecialClasses() {
    return self::$__specialClasses;
  }
 
 /**
  * A method that registered to spl_autoload_register. When a class
  * is instantiated this method will be called to lookup the class file
  * f the class is not present.
  * @param $class Class name
  * @return bool true
  * @access public
  */ 
  static public function load($class) {
    // if class already in scope
    if ( class_exists($class, FALSE) ) {
      return;
    }
    
    // if $class is interface or special class filename
    if ( array_key_exists($class, self::$__specialClasses) ) {
      require_once self::$__specialClasses[$class];
      return;
    }
    
    // if config class, also include the config.inc.php
    if ( $class == 'Config' && !class_exists('Config') ) {
      global $THINKTANK_CFG;
      
      if ( is_null(self::$__installer) ) {
        require_once THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.Installer.php';
        self::$__installer = Installer::getInstance();
      }
      if ( !file_exists( THINKTANK_WEBAPP_PATH . 'config.inc.php' ) ) {
        // if config file doesn't exist
        
        $message  = "<p>Config's file, <code>config.inc.php</code>, is not found! ";
        $message .= "No need to worry, this may happens if you're going install ThinkTank for the first time. ";
        $message .= "If you've installed ThinkTank before, you can create config file by copying or renaming ";
        $message .= "<code>config.sample.inc.php</code> to <code>config.inc.php</code>. If you want to install ";
        $message .= "ThinkTank clik on the link below to start installation.";
        $message .= '<div class="clearfix"><div class="grid_10 prefix_8 left">';
        $message .= '<div class="next_step tt-button ui-state-default ui-priority-secondary ui-corner-all">';
        $message .= '<a href="' . THINKTANK_BASE_URL . 'install/">Start Installation!</a>';
        $message .= '</div></div></div>';
        
        self::$__installer->diePage($message, 'Error');
      } else {
        // config file exists in THINKTANK_WEBAPP_PATH
        require_once THINKTANK_WEBAPP_PATH . 'config.inc.php';
        require_once THINKTANK_WEBAPP_PATH . '/model/class.Config.php';
        $config = Config::getInstance();
        
        try {
          // check if $THINKTANK_CFG related to path exists
          self::$__installer->checkPath($config->config);
          
          // check if ThinkTank is installed
          if ( !self::$__installer->isThinkTankInstalled($config->config) ) {
            throw new InstallerError('', Installer::ERROR_INSTALL_NOT_COMPLETE);
          }
        } catch (InstallerError $e) {
          $e->showError();
        }
      }
      
      return;
    }
    
    // regular class convention filename
    $file = 'class.' . $class . '.php';
    
    // variable to flag if class filename is found
    $file_found = false;
    
    // check class file existent on each lookup path
    foreach ( self::$__lookupPath as $path ) {
      if ( file_exists($path . $file) ) {
        $file_found = true;
        $filename = $path . $file;
        // quit loop immediately after file is found
        break;
      }
    }
    
    if ( !$file_found ) {
      // throw an error if file is not found
      try {
        throw new InstallerError(
          'File ' . $file . ' not found.', Installer::ERROR_FILE_NOT_FOUND
        );
      } catch (InstallerError $e) {
        $e->showError();
      }
    }
    
    require $filename;
    
    // after including the class, check if class exists
    if ( !class_exists($class, FALSE) ) {
      try {
        if ( !class_exists('InstallerError', FALSE) ) {
          require_once THINKTANK_WEBAPP_PATH . 'model' . DS . 'class.InstallerError.php';
        }
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
?>