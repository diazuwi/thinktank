<?php
class Loader {
  static public $__lookupPath;

/**
 * Register current script to use lazy loading classes
 * @param mixed $additionalPath Additional lookup path for classes
 * @return bool true
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
    
    // set default lookup path
    self::$__lookupPath = array(
      THINKTANK_WEBAPP_PATH . 'model' . DS, 
      THINKTANK_ROOT_PATH . 'extlib' . DS . 'Smarty-2.6.26' . DS .
      'libs' . DS, THINKTANK_WEBAPP_PATH . 'install' . DS
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
  * A method that registered to spl_autoload_register. When a class
  * is instantiated this method will be called to lookup the class file
  * f the class is not present.
  * @param $class Class name
  * @return bool true
  */ 
  static public function load($class) {
    if ( class_exists($class, FALSE) ) {
      return;
    }
    
    // Smarty has different filename
    if ( $class == 'Smarty' ) {
      $file = 'Smarty.class.php';
    } else {
      $file = 'class.' . $class . '.php';
    }
    
    $file_found = false;
    
    foreach ( self::$__lookupPath as $path ) {
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