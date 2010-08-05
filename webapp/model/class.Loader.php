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
    static private $lookup_path;

    /**
     * Some classes has special filename that don't follow the convention
     * or classes that included in another file class or interfaces.
     * The value will be assigned inside setLookupPath method.
     */  
    static private $special_classes;
  
    /**
     * Instance of installer
     * 
     * @var mixed
     * @access private
     */
    static private $installer;
  
    /**
     * Instance of installer controller
     * 
     * @var mixed
     * @access private
     */
    static private $installer_controller;

    /**
     * Register current script to use lazy loading classes
     * @param mixed $additional_path Additional lookup path for classes
     * @return bool true
     * @access public
     */  
    static public function register($additional_path = '') {
        if ( is_null(self::$lookup_path) ) {
            self::setLookupPath($additional_path);
        }
         
        return spl_autoload_register(array(
            __CLASS__, 'load'
        ));
    }
  
    static public function unregister() {
        self::$lookup_path = null;
        self::$special_classes = null;
        
        return spl_autoload_unregister( array(__CLASS__, 'load') );
    }

    /**
     * Set additional lookup path classes
     * @param mixed $additional_path Additional lookup path for classes
     * @return bool always true
     * @access private
     */  
    static private function setLookupPath($additional_path = '') {
        if ( !defined('DS') ) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        // check two required named constants
        if ( !defined('THINKUP_ROOT_PATH') ) {
            define('THINKUP_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DS);
        }
        
        if ( !defined('THINKUP_WEBAPP_PATH') ) {
            define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp' . DS);
        }
    
        // set default lookup path for classes
        self::$lookup_path = array(
            THINKUP_WEBAPP_PATH . 'model' . DS, 
            THINKUP_WEBAPP_PATH . 'controller' . DS
        );
    
        // set default lookup path for special classes
        self::$special_classes = array(
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
    
        // one path is given in param as a string
        if ( is_string($additional_path) && !empty($additional_path) ) {
            // this is better than array_push
            self::$lookup_path[] = $additional_path;
        }
    
        // array is passed
        if ( is_array($additional_path) && !empty($additional_path) ) {
            foreach ( $additional_path as $path ) {
                self::$lookup_path[] = $path;
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
        return self::$lookup_path;
    }

    /**
     * Get special classes files
     * @return array of special classes path files
     * @access public
     */
    public function getSpecialClasses() {
        return self::$special_classes;
    }
 
     /**
      * A method that registered to spl_autoload_register. When a class
      * is instantiated this method will be called to lookup the class file
      * if the class is not present. The second instantiation of the same
      * class wouldn't call this method.
      * 
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
        if ( array_key_exists($class, self::$special_classes) ) {
            require_once self::$special_classes[$class];
            return;
        }
    
        if ( is_null(self::$installer) ) {
            require_once THINKUP_WEBAPP_PATH . 'model' . DS . 'class.Installer.php';
            require_once THINKUP_WEBAPP_PATH . 'controller' . DS . 'class.ThinkUpController.php';
            require_once THINKUP_WEBAPP_PATH . 'controller' . DS . 'class.InstallerController.php';
            self::$installer_controller = new InstallerController();
            self::$installer = Installer::getInstance(self::$installer_controller);
        }
    
        // if config class, also include the config.inc.php
        if ( $class == 'Config' && !class_exists('Config') ) {
            global $THINKUP_CFG;
            require_once THINKUP_WEBAPP_PATH . '/model/class.Config.php';
      
            if ( !file_exists( THINKUP_WEBAPP_PATH . 'config.inc.php' ) ) {
                // if config file doesn't exist
        
                $message  = "ThinkUp's configuration file doesn't exist! " .
                            "If you installed ThinkUp before, you can manually create your config file " .
                            "by renaming config.sample.inc.php to config.inc.php. " .
                            "To install and configure ThinkUp for the first time, press the button below.";
                $next     = '<div class="clearfix"><div class="grid_10 prefix_8 left">' .
                            '<div class="next_step tt-button ui-state-default ui-priority-secondary ui-corner-all">' .
                            '<a href="' . THINKUP_BASE_URL . 'install/">Start Installation!</a>' .
                            '</div></div></div>';
                $message  = "<p>$message</p>$next";
        
                // quick hack for test
                if ( defined('INSTALLER_ON_TEST') && INSTALLER_ON_TEST ) {
                    throw new Exception('Missing Configuration File');
                } else {
                    self::$installer_controller->diePage($message, 'Error');
                }
            } else {
                // config file exists in THINKUP_WEBAPP_PATH
                require_once THINKUP_WEBAPP_PATH . 'config.inc.php';
                $config = Config::getInstance();
        
                try {
                    // check if $THINKUP_CFG related to path exists
                    self::$installer->checkPath($config->config);
                  
                    // check if ThinkUp is installed
                    if ( !self::$installer->isThinkUpInstalled($config->config) ) {
                        throw new InstallerError('', Installer::ERROR_INSTALL_NOT_COMPLETE);
                    }
                } catch (InstallerError $e) {
                    return $e->showError(self::$installer_controller);
                }
            }
      
            return;
        }
    
        // regular class convention filename
        $file = 'class.' . $class . '.php';
    
        // variable to flag if class filename is found
        $file_found = false;
    
        // check class file existent on each lookup path
        foreach ( self::$lookup_path as $path ) {
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
                return $e->showError(self::$installer_controller);
            }
        }
        require_once $filename;
    
        // after including the class, check if class exists
        if ( !class_exists($class, FALSE) ) {
            try {
                if ( !class_exists('InstallerError', FALSE) ) {
                    require_once THINKUP_WEBAPP_PATH . 'model' . DS . 'class.InstallerError.php';
                }
                throw new InstallerError(
                    'Class ' . $class . ' not found.', Installer::ERROR_CLASS_NOT_FOUND
                );
            } catch (InstallerError $e) {
                return $e->showError(self::$installer_controller);
            }
        }
    
        return true;
    }
}
