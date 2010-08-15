<?php
/**
 * Installer Controller
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 *
 */
class InstallerController extends ThinkUpController {
    /**
     * Installer Model Instance
     * 
     * @var obj
     */
    private $installer;
    
    public function __construct() {
        if ( !defined('DS') ) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        
        if ( !defined('THINKUP_ROOT_PATH') ) {
            define('THINKUP_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DS);
        }
        
        if ( !defined('THINKUP_WEBAPP_PATH') ) {
            define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp' . DS);
        }
        
        $this->installer = Installer::getInstance($this);
        
        // instantiate Smarty
        $this->view_mgr = new Smarty();
        $this->view_mgr->template_dir = array(THINKUP_WEBAPP_PATH . 'view');
        $this->view_mgr->compile_dir = THINKUP_WEBAPP_PATH . 'view' . DS . 'compiled_view' . DS;
        $this->view_mgr->caching = FALSE;
        $this->addToView('base_url', THINKUP_BASE_URL);
        $this->addToView('favicon', THINKUP_BASE_URL . 'assets/img/favicon.ico');
    }

/**
 * Step 1 - Check requirements
 * @access private
 * @return void
 */  
    private function __step1() {
        // php version check
        $php_compat = 0;
        if ( $this->installer->checkVersion() ) {
            $php_compat = 1;
        }
        $this->addToView('php_compat', $php_compat);
        $requiredVersion = $this->installer->getRequiredVersion();
        $this->addToView('php_required_version', $requiredVersion['php']);
        
        // libs check
        $libs = $this->installer->checkDependency();
        $libs_compat = TRUE;
        foreach ($libs as $lib) {
            if (!$lib) {
                $libs_compat = FALSE;
            }
        }
        $this->addToView('libs', $libs);
        
        // path permissions check
        $permissions = $this->installer->checkPermission();
        $this->addToView('permission', $permissions);
        $permissions_compat = TRUE;
        foreach ($permissions as $perm) {
            if (!$perm) {
                $permissions_compat = FALSE;
            }
        }
        $this->addToView('permissions_compat', $permissions_compat);
        $writeable_directories = array(
            'logs' => THINKUP_ROOT_PATH . 'logs',
            'compiled_view' => $this->view_mgr->compile_dir,
            'cache' => $this->view_mgr->compile_dir . 'cache'
        );
        $this->addToView('writeable_directories', $writeable_directories);
        
        // other vars set to view
        $requirements_met = ($php_compat && $libs_compat && $permissions_compat);
        $this->addToView('requirements_met', $requirements_met);
        $this->addToView('subtitle', 'Requirements Check');
    }

/**
 * Step 2 - Setup database and site configuration
 * @access private
 * @return void
 */  
    private function __step2() {
        // make sure we have passed step 1
        if ( !$this->installer->checkStep1() ) {
            header('Location: index.php?step=1');
            die;
        }
        
        if ( isset($_GET['email_error']) ) {
            $this->addToView('errormsg', "Please provide valid email on site configuration .");
        } else if ( isset($_GET['password_error']) ) {
            if ( $_GET['password_error'] == 1 ) {
                $message = "Password on site configuration didn't match.";
            } else if ( $_GET['password_error'] == 2 ) {
                $message = "Password on site configuration can't be blank.";
            }
            $this->addToView('errormsg', $message);
        }
        
        $this->addToView('db_name', 'thinkup');
        $this->addToView('db_user', 'username');
        $this->addToView('db_passwd', 'password');
        $this->addToView('db_host', 'localhost');
        $this->addToView('db_prefix', 'tu_');
        $this->addToView('db_socket', '');
        $this->addToView('db_port', '');
        $this->addToView('site_email', 'username@example.com');
        $this->addToView('subtitle', 'Setup Database and Site Configuration');
    }

/**
 * Step 3 - Populate Database and Finishing
 * @access private
 * @return void
 */  
    private function __step3() {
        $config_file_exists = false;
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';
        
        // make sure we are here with posted data
        if ( empty($_POST) ) {
            header('Location: index.php?step=2');
            die;
        }
        
        // check if we have made config.inc.php
        if ( file_exists($config_file) ) {
            // this is could be from step 2 is not able writing
            // to webapp dir
            $config_file_exists = true;
            require $config_file;
            $db_config['db_type']      = $THINKUP_CFG['db_type'];
            $db_config['db_name']      = $THINKUP_CFG['db_name'];
            $db_config['db_user']      = $THINKUP_CFG['db_user'];
            $db_config['db_password']  = $THINKUP_CFG['db_password'];
            $db_config['db_host']      = $THINKUP_CFG['db_host'];
            $db_config['db_socket']    = $THINKUP_CFG['db_socket'];
            $db_config['db_port']      = $THINKUP_CFG['db_port'];
            $db_config['table_prefix'] = $THINKUP_CFG['table_prefix'];
            $db_config['GMT_offset']   = $THINKUP_CFG['GMT_offset'];
            $email                     = trim($_POST['site_email']);
        } else {
            // make sure we're not from error of couldn't write config.inc.php
            if ( !isset($_POST['db_user']) && !isset($_POST['db_passwd']) &&
                 !isset($_POST['db_name']) && !isset($_POST['db_host']) ) {
              
                header('Location: index.php?step=2');
                die;
            }
            
            // trim each posted value
            $db_config['db_type']      = trim($_POST['db_type']);
            $db_config['db_name']      = trim($_POST['db_name']);
            $db_config['db_user']      = trim($_POST['db_user']);
            $db_config['db_password']  = trim($_POST['db_passwd']);
            $db_config['db_host']      = trim($_POST['db_host']);
            $db_config['db_socket']    = trim($_POST['db_socket']);
            $db_config['db_port']      = trim($_POST['db_port']);
            $db_config['table_prefix'] = trim($_POST['db_prefix']);
            $db_config['GMT_offset']   = 7;
            $email                     = trim($_POST['site_email']);
            
            if ( empty($db_config['table_prefix']) ) {
                $db_config['table_prefix'] = 'tu_';
            }
        }
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
      
        // check db
        try {
            $this->installer->checkDb($db_config);
        } catch (InstallerError $e) {
            return $e->showError($this);
        }
        
        // check email
        if ( !$this->installer->checkValidEmail($email) ) {
            header('Location: index.php?step=2&email_error=1');
            die;
        }
      
        // check password
        $password_error = 0;
        if ( $password != $confirm_password ) {
            $password_error = 1;
        }
        if ( $password == '' ) {
            $password_error = 2;
        }
        if ( $password_error ) {
            header('Location: index.php?step=2&password_error=' . $password_error);
            die;
        }
        
        $admin_user = array(
            'email' => $email, 'password' => $password, 'confirm_password' => $confirm_password
        );
        // trying to create config file
        $this->installer->createConfigFile($db_config, $admin_user);
        unset($admin_user['confirm_password']);
        
        // check tables
        try {
            $this->installer->checkTable($db_config);
        } catch (InstallerError $e) {
          // catch error when tables exist
            return $e->showError($this);
        }
      
        // if empty, we're ready to populate the database with ThinkUp tables
        $this->installer->populateTables($db_config);
        
        $admin_exists = $this->installer->isAdminExists($db_config);
        if ( !$admin_exists ) { // create admin if not exists
            $this->installer->insertAdmin($db_config['table_prefix'] . 'owners', $admin_user);
            
            // view for email
            $email_view = new Smarty();
            $email_view->template_dir = array(THINKUP_WEBAPP_PATH . 'view');
            $email_view->compile_dir = THINKUP_WEBAPP_PATH . 'view' . DS . 'compiled_view' . DS;
            $email_view->caching = FALSE;
            $email_view->assign('server', $_SERVER['HTTP_HOST'] . THINKUP_BASE_URL);
            $email_view->assign('email', $email);
            $email_view->assign('password', $password);
            $email_message = $email_view->fetch('installer.step.3.email.tpl');
            
            // send email
            $subject = "Your ThinkUp Installation";
            Mailer::mail($email, $subject, $email_message);
        } else {
            $email = 'Use your old email admin';
            $password = 'Use your old password admin';
        }
        unset($THINKUP_CFG);
        
        $this->addToView('errors', $this->installer->getErrorMessages() );
        $this->addToView('username', $email);
        $this->addToView('password', $password);
        $this->addToView('login_url', THINKUP_BASE_URL . 'session/login.php');
        $this->addToView('subtitle', 'Finish');
    }

/**
 * Installation page
 * @param int $step
 */     
    private function installPage($step = 1) {
        $methodName = '__step' . $step;
        if ( !method_exists(__CLASS__, $methodName) ) {
            $step = 1;
            $methodName = '__step1';
        }
        $this->$methodName();
        return $this->view_mgr->fetch('installer.step.' . $step . '.tpl');
    }

/**
 * Repairing page
 * @param array $params
 */    
    private function repairPage($params = null) {
        // check requirements on step #1        
        try {
            $this->installer->repairerCheckStep1();
        } catch (InstallerError $e) {
            $e->showError($this);
        }
        
        // check file configuration
        try {
            $config_file = $this->installer->repairerCheckConfigFile();
        } catch (InstallerError $e) {
            $e->showError($this);
        }
        require $config_file;
        
        
        // check database
        try {
            $this->installer->checkDb($THINKUP_CFG);
        } catch (InstallerError $e) {
            $e->showError($this);
        }
        
        // check $THINKUP_CFG['repair'] is set to true
        try {
            $this->installer->repairerIsDefined($THINKUP_CFG);
        } catch (InstallerError $e) {
            $e->showError($this);
        }
        
        // clearing error messages before doing the repair
        $error_messages = $this->installer->getErrorMessages();
        if ( !empty($error_messages) ) {
            $this->installer->clearErrorMessages();
        }
        
        // do repairing when form is posted and $_GET is not empty
        if ( isset($_POST['repair']) && !empty($_GET) ) {
            $this->addToView('posted', true);
            $succeed = false;
            $messages = array();
            
            // check database again
            try {
                $this->installer->checkDb($THINKUP_CFG);
            } catch (InstallerError $e) {
                $e->showError($this);
            }
            
            // check if we repairing db
            if ( isset($params['db']) ) {
                $messages['db'] = $this->installer->repairTables($THINKUP_CFG);
                $this->addToView('messages_db', $messages['db']);
            }
            
            // check if we need to create admin user
            if ( isset($params['admin']) ) {
                $site_email   = trim($_POST['site_email']);
                $password     = trim($_POST['password']);
                $insertAdminVal = array(
                    'email' => $site_email, 'password' => $password
                );
                $this->installer->insertAdmin($THINKUP_CFG['table_prefix'] . 'owners', $insertAdminVal);
                
                $messages['admin'] = "Create admin user <strong>$site_email</strong> ".
                                     "with password <strong>$password</strong>";
                $this->addToView('messages_admin', $messages['admin']);
                $this->addToView('username', $site_email);
                $this->addToView('password', $password);
            }
            
            $error_messages = $this->installer->getErrorMessages();
            if ( !empty($error_messages) ) {
                // failed repairing
                $this->addToView('messages_error', $error_messages);
            } else {
                $succeed = true;
            }
            
            $this->addToView('succeed', $succeed);
        } else {
            if ( empty($params) ) {
                $this->addToView('show_form', 0);
            } else {
                $information_message = array();
                $this->addToView('show_form', 1);
                if ( isset($params['db']) ) {
                    $information_message['db']  = 'Checking your existing ThinkUp tables. '.
                                                  'If some tables are missing, ';
                    $information_message['db'] .= 'ThinkUp will attempt to create those tables. '.
                                                  'ThinkUp will check every ThinkUp tables and ';
                    $information_message['db'] .= 'will attemp to repair those tables if the status is not okay.';
                }
                
                if ( isset($params['admin']) ) {
                    $this->addToView('site_email', 'username@example.com');
                    $this->addToView('admin_form', 1);
                    $information_message['admin'] = 'ThinkUp will attemp to create one admin user ' .
                                                    'based on form below.';
                }
                
                if ( !empty($information_message) ) {
                    $info  = '<div class="clearfix info_message">';
                    $info .= '<p><strong class="not_okay">Read before repairing!</strong> ';
                    $info .= 'ThinkUp Repairer will do the following actions when repairing: </p><ul>';
                    foreach ($information_message as $msg) {
                        $info .= "<li>$msg</li>";
                    }
                    $info .= '</ul></div>';
                    $this->addToView('info', $info);
                }
                $this->addToView('action_form', $_SERVER['REQUEST_URI']);
            }
        }
        
        $this->addToView('subtitle', 'Repairing');
        return $this->view_mgr->fetch('installer.repair.tpl');
    }
    
/**
 * Die with page formatted, inspired by wp_die()
 * Formatting happens when Smarty is available
 * 
 * @param string $message Content to be displayed
 * @param string $title Title on browser
 */
    public function diePage($message, $title = '') {
        // check if compile_dir is set
        if ( !isset($this->view_mgr->compile_dir) ) {
            $message = '<strong>ERROR: Couldn\'t instantiate SmartyInstaller or Smarty!</strong><br>' .
                       '<p>Make sure Smarty related classes exist.<br>';
            echo $message;
            die;
        }
        
        // check if compiled directory is writeable
        if ( !is_writable($this->view_mgr->compile_dir) ) {
            $message = 
                '<strong>ERROR: ' . $this->view_mgr->compile_dir . ' is not writeable!</strong><br>' .
                '<p>Make sure <code>' . $this->view_mgr->compile_dir . '</code> is writeable by the webserver.<br>' .
                'The fastest way: <code>chmod -R 777 ' . $this->view_mgr->compile_dir . '</code>.</p>';
            echo $message;
            die;
        }
        
        $this->addToView('message', $message);
        $this->addToView('subtitle', $title);
        echo $this->view_mgr->fetch('installer.die.tpl');
        die;
    }
    
    public function control() {
        $page = substr(str_replace(THINKUP_WEBAPP_PATH . 'install' . DS, '', $_SERVER['SCRIPT_FILENAME']), 0, -4);
        
        if ( $page == 'index' ) {
            if ( file_exists( THINKUP_WEBAPP_PATH . 'config.inc.php' ) ) {
                // config file exists in THINKUP_WEBAPP_PATH
                require_once THINKUP_WEBAPP_PATH . 'config.inc.php';
                
                try {
                    // check if ThinkUp is installed
                    if ( $this->installer->isThinkUpInstalled($THINKUP_CFG) && 
                         $this->installer->checkPath($THINKUP_CFG) )
                    {
                      throw new InstallerError('', Installer::ERROR_INSTALL_COMPLETE);
                    }
                } catch (InstallerError $e) {
                    return $e->showError($this);
                }
            }
            
            // clear error messages after called isThinkUpInstalled successfully
            $this->installer->clearErrorMessages();
            $step = 1;
            if ( isset($_GET['step']) ) {
                $step = (int) $_GET['step'];
            }
            return $this->installPage($step);
        } else if ( $page == 'repair' ) {
            return $this->repairPage($_GET);
        }
    }
}
