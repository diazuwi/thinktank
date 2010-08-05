<?php
/**
 * InstallerError Model that used along with Installer Model
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 *
 */
class InstallerError extends Exception {
    public function showError($controller) {
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
            $title = 'ThinkUp Tables Exist';
            break;
        case Installer::ERROR_SITE_NAME:
            $title = 'Invalid Site Name';
            break;
        case Installer::ERROR_SITE_EMAIL:
            $title = 'Invalid Site Email';
            break;
        case Installer::ERROR_CONFIG_FILE_MISSING:
            $title = 'Missing Configuration File';
            break;
        case Installer::ERROR_CONFIG_SAMPLE_MISSING:
            $title = 'Missing Sample Configuration File';
            break;
        case Installer::ERROR_CONFIG_SOURCE_ROOT_PATH:
        case Installer::ERROR_CONFIG_SMARTY_PATH:
        case Installer::ERROR_CONFIG_LOG_LOCATION:
            $title = 'Configuration Error';
            break;
        case Installer::ERROR_TYPE_MISMATCH:
            $title = 'Type Mismatch';
            break;
        case Installer::ERROR_INSTALL_PATH_EXISTS:
            $title = 'Install Path Exists';
            break;
        case Installer::ERROR_INSTALL_NOT_COMPLETE:
            $message  = 'It seems ThinkUp not already fully installed. Here ' .
                        'are some informations: <br><ul>';
            $messages = Installer::getErrorMessages();
            $uriToRepair = array();
            foreach ($messages as $key => $msg) {
                switch ($key) {
                    case 'config_file':
                        $uriToRepair[$key] = 'config=1';
                        break;
                    case 'table':
                        $uriToRepair[$key] = 'db=1';
                        break;
                    case 'admin':
                        $uriToRepair[$key] = 'admin=1';
                        break;
                }
                $message .= "<li>$msg</li>";
            }
            $message .= '</ul>';
            
            $uriToRepairStr = '';
            if ( !empty($uriToRepair) ) {
                $uriToRepairStr = implode('&', $uriToRepair);
            }
            
            $message .= '<p>You can repair your ThinkUp database by ' .
                        'clicking <a href="' . THINKUP_BASE_URL . 'install/repair.php?'.$uriToRepairStr.'">here</a>. ' .
                        'Repairing will fix the errors encountered and will try to keep your old data that ' .
                        'still exist. If you\'re planning to reinstall ThinkUp freshly regardless of lossing your ' .
                        'old data, you can reinstall your ThinkUp by clearing out ThinkUp ' .
                        'database and then click <a href="'.
                        THINKUP_BASE_URL . 'install/">here</a></p>';
            $title    = 'Installation is Not Complete';
            $this->message = $message;
            break;
        case Installer::ERROR_INSTALL_COMPLETE:
            $message  = '<p>It seems ThinkUp already installed. ';
            $message .= 'You can repair your ThinkUp database by ' .
                        'clicking <a href="' . THINKUP_BASE_URL . 'install/repair.php">here</a>. ' .
                        'Repairing will fix the errors encountered and will try to keep your old data that ' .
                        'still exist. If you\'re planning to reinstall ThinkUp freshly regardless of lossing your ' .
                        'old data, you can reinstall your ThinkUp by clearing out ThinkUp ' .
                        'database and then click <a href="'.
                        THINKUP_BASE_URL . 'install/">here</a></p>';
            $title = 'ThinkUp already installed';
            $this->message = $message;
            break;
        case Installer::ERROR_REPAIR_CONFIG:
            $title = 'Repair Configuration Error';
            break;
        case Installer::ERROR_REQUIREMENTS:
            $title = 'Requirement is not met';
            break;
        }
    
        return $controller->diePage($this->getMessage(), $title);
    }
}
