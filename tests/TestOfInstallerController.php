<?php
require_once 'classes/class.ThinkUpInstallerControllerTestCase.php';

/**
 * Test Of Installer Controller
 * 
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class TestOfInstallerController extends ThinkUpInstallerControllerTestCase {
    /**
     * Hits the webapp/index.php
     */
    public function testAccessFromWebAppIndex() {
        // we need to separate test case
        // based on how fresh ThinkUp
        if ( $this->is_installed == 2 ) {
            $this->accessForInstalledThinkUpFromWebApp();
        } else if ( $this->is_installed == 1 ) {
            $this->accessForIncompleteThinkUpFromWebApp();
        } else if ( $this->is_installed == 0 ) {
            $this->accessForFreshThinkUpFromWebApp();
        }
        
        $this->drop();
    }
    
    /**
     * Test the control method
     */
    public function testControl() {
        // since the controller checked the request file name,
        // calling the controller's go from other than index.php and repair.php will return null.
        // To emulate index.php and repair.php script name
        // we must use file_get_contents and curl
        $this->assertNull( $this->controller->go() );
    }
    
    /**
     * Hits the webapp/install/index.php
     */
    public function testAccessFromInstallIndex() {
        // we need to separate test case
        // based on how fresh ThinkUp
        if ( $this->is_installed == 2 ) {
            $this->accessFromInstallIndexWhenInstalled();
        } else {
            $this->accessFromInstallIndexWhenFresh();
        }
    }
    
    private function accessFromInstallIndexWhenInstalled() {
        global $TEST_SERVER_DOMAIN, $SITE_ROOT_PATH;
        
        $index_page = file_get_contents($TEST_SERVER_DOMAIN . $SITE_ROOT_PATH . '/install/');
        // expected title page
        $this->assertTrue( strpos($index_page, "ThinkUp :: ThinkUp already installed") > 0 );
        // expected links on page
        $this->assertTrue( strpos($index_page, $SITE_ROOT_PATH . '/install') > 0 );
    }
    
    private function accessFromInstallIndexWhenFresh() {
        global $TEST_SERVER_DOMAIN, $SITE_ROOT_PATH;
        
        // step #1
        $index_page = file_get_contents($TEST_SERVER_DOMAIN . $SITE_ROOT_PATH . '/install/');
        // expected title page
        $this->assertTrue( strpos($index_page, "ThinkUp :: Requirements Check") > 0 );
        // expected some text page
        $required_version = $this->model->getRequiredVersion();
        $this->assertTrue( strpos($index_page, "PHP Version >= " . $required_version['php']) > 0);
        $this->assertTrue( strpos($index_page, "cURL installed") > 0 );
        $this->assertTrue( strpos($index_page, "GD lib installed") > 0 );
        $this->assertTrue( strpos($index_page, "Template and Log directories are writeable?") > 0 );
        $this->assertTrue( strpos($index_page, "Template and Log directories are writeable?") > 0 );
    
        // step #2
        $index_page = file_get_contents($TEST_SERVER_DOMAIN . $SITE_ROOT_PATH . '/install/index.php?step=2');
        // expected title page
        $this->assertTrue( strpos($index_page, "ThinkUp :: Setup Database and Site Configuration") > 0 );
        // expected database credentials section and its default values
        $this->assertTrue( strpos($index_page, "Database Type") > 0 );
        $this->assertTrue( strpos($index_page, "MySQL") > 0 );
        $this->assertTrue( strpos($index_page, "Database Name") > 0 );
        $this->assertTrue( strpos($index_page, "thinkup") > 0 );
        $this->assertTrue( strpos($index_page, "User Name") > 0 );
        $this->assertTrue( strpos($index_page, "username") > 0 );
        $this->assertTrue( strpos($index_page, "Password") > 0 );
        // advanced options
        $this->assertTrue( strpos($index_page, "Database Host") > 0 );
        $this->assertTrue( strpos($index_page, "localhost") > 0 );
        $this->assertTrue( strpos($index_page, "Database Socket") > 0 );
        $this->assertTrue( strpos($index_page, "Database Port") > 0 );
        $this->assertTrue( strpos($index_page, "Table Prefix") > 0 );
        // email address
        $this->assertTrue( strpos($index_page, "Your E-mail") > 0 );
        $this->assertTrue( strpos($index_page, "username@example.com") > 0 );
    }
    
    /**
     * Test for already installed ThinkUp
     */
    private function accessForInstalledThinkUpFromWebApp() {
        global $TEST_SERVER_DOMAIN, $SITE_ROOT_PATH;
        
        $index_page = file_get_contents($TEST_SERVER_DOMAIN . $SITE_ROOT_PATH);
        $this->assertTrue( strpos($index_page, "Latest public posts and public replies") > 0,
            "not logged in; render public timeline instead");
    }
    
    /**
     * Test for incomplete installation where configuration
     * file exists but may missing some tables or non-existent
     * admin user
     */
    private function accessForIncompleteThinkUpFromWebApp() {
        global $TEST_SERVER_DOMAIN, $SITE_ROOT_PATH;
        
        $index_page = file_get_contents($TEST_SERVER_DOMAIN . $SITE_ROOT_PATH);
        // expected title page
        $this->assertTrue( strpos($index_page, "ThinkUp :: Installation is Not Complete") > 0 );
        // expected links to install path
        $this->assertTrue( strpos($index_page, $SITE_ROOT_PATH . '/install') > 0 );
        // expected error messages
        foreach ( $this->model->getErrorMessages() as $error ) {
            $this->assertTrue( strpos($index_page, $error) > 0 );
        }
    }
    
    /**
     * Test for fresh copy of thinkup where configuration file
     * doesn't exists
     */
    private function accessForFreshThinkUpFromWebApp() {
        global $TEST_SERVER_DOMAIN, $SITE_ROOT_PATH;
        
        // clean database
        $this->drop();
        $index_page = file_get_contents($TEST_SERVER_DOMAIN . $SITE_ROOT_PATH);
        $this->assertTrue( strpos($index_page, "ThinkUp :: Error") > 0 );
        $this->assertTrue( strpos($index_page, "ThinkUp's configuration file doesn't exist!") > 0 );
        $this->assertTrue( strpos($index_page, $SITE_ROOT_PATH . 'install') > 0 );
        
        // The index page will contain the same as above even if
        // table already setup with admin user
        $this->create();
        $this->insertAdmin();
        $index_page = file_get_contents($TEST_SERVER_DOMAIN . $SITE_ROOT_PATH);
        $this->assertTrue( strpos($index_page, "ThinkUp :: Error") > 0 );
        $this->assertTrue( strpos($index_page, "ThinkUp's configuration file doesn't exist!") > 0 );
        $this->assertTrue( strpos($index_page, $SITE_ROOT_PATH . 'install') > 0 );
    }
}
