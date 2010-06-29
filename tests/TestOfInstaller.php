<?php
require_once 'classes/class.ThinkTankInstallerTestCase.php';
class TestOfInstaller extends ThinkTankInstallerTestCase {
  public function testGetInstallerInstance() {
    $this->assertIsA(Installer::getInstance(), 'Installer');
  }
  
  public function testInstallerCheckVersion() {
    $this->assertTrue(Installer::checkVersion());
    $this->assertFalse(Installer::checkVersion('4'));
    
    $ver = Installer::getRequiredVersion();
    $ver = $ver['php'] + 0.1;
    
    $this->assertTrue(Installer::checkVersion("$ver"));
  }
  
  public function testInstallerCheckDependency() {
    $dependency = Installer::checkDependency();
    $this->assertTrue($dependency['curl'], 'cURL is installed');
    $this->assertTrue($dependency['gd'], 'gd lib is installed');
  }
  
  public function testInstallerCheckPermission() {
    $perms = Installer::checkPermission();
    $this->assertTrue($perms['logs'], THINKTANK_ROOT_PATH . 'logs is writeable by the webserver');
    $this->assertTrue($perms['compiled_view'], THINKTANK_ROOT_PATH .
      'webapp' . DS . 'view' . DS . 'compiled_view is writeable by the webserver');
    $this->assertTrue($perms['cache'], THINKTANK_ROOT_PATH .
      'webapp' . DS . 'view' . DS . 'compiled_view' . DS . 'cache is writeable by the webserver');
  }
  
  public function testInstallerCheckPath() {
    $this->assertTrue(Installer::checkPath(array(
      'source_root_path' => THINKTANK_ROOT_PATH,
      'smarty_path' => THINKTANK_ROOT_PATH . 'extlib' . DS . 'Smarty-2.6.26' . DS . 'libs' . DS,
      'log_location' => THINKTANK_ROOT_PATH . 'logs' . DS . 'crawler.log'
    )));
  }
  
  public function testInstallerCheckStep1() {
    $installer = Installer::getInstance();
    $this->assertTrue($installer->checkStep1());
  }
  
  public function testInstallerCheckDb() {
    $installer = Installer::getInstance();
    
    // Check db.
    // Using this way when we handle a method that may
    // throws an exception
    try {
      $cdb = $installer->checkDb($this->config);
    } catch (Exception $e) {}
    $this->assertTrue($cdb);
    
    // failed check db
    error_reporting(E_ERROR);
    $ori_db_user = $this->config['db_user'];
    $ori_db_password = $this->config['db_password'];
    $this->config['db_user'] .= ' ';
    $this->config['db_password'] .= ' ';
    // lets modify user and password so it couldn't connect
    $this->config['db_user'] .= ' ';
    $this->config['db_password'] .= ' ';
    try {
      $installer->setDb($this->config);
      $this->fail();
    } catch (Exception $e) {
      $this->pass();
    }
    $this->config['db_user'] = $ori_db_user;
    $this->config['db_password'] = $ori_db_password;
    
    // try set db
    try {
      $db = $installer->setDb($this->config);
    } catch (Exception $e) {}
    $this->assertIsA($db, 'Database');
    
    // test failed to connect
    $this->config['db_user'] .= ' ';
    $this->config['db_password'] .= ' ';
    try {
      $installer->setDb($this->config);
      $this->fail();
    } catch (Exception $e) {
      $this->pass();
    }
  }
  
  public function testInstallerShowTables() {
    // test empty tables
    $installer = Installer::getInstance();
    $this->drop();
    $tables = $installer->showTables($this->config);
    $expected = array();
    $this->assertIdentical($tables, $expected);
    $this->assertIdentical($installer::$showTables, $expected);
    
    // test with a table
    $installer = Installer::getInstance();
    $installer::$showTables = array();
    $expected = 'follows';
    $this->create($expected);
    $tables = $installer->showTables($this->config);
    $this->assertIdentical($tables, array($expected));
    $this->assertIdentical($installer::$showTables, array($expected));
    $this->drop();
    
    // test with some tables
    $installer = Installer::getInstance();
    $installer::$showTables = array();
    $expected = array('follows', 'links');
    $this->create($expected);
    $tables = $installer->showTables($this->config);
    $this->assertIdentical($tables, $expected);
    $this->assertIdentical($installer::$showTables, $expected);
    $this->drop();
  }
  
  public function testInstallerCheckTable() {
    // test with complete tables (will fail)
    $installer = Installer::getInstance();
    $installer::$showTables = array();
    $expected = $installer::$tables;
    $this->create($expected);
    try {
      $this->assertTrue($installer->checkTable($this->config));
      $this->fail();
    } catch (Exception $e) {
      $this->pass();
    }
    
    // test with incomplete tables (also fail)
    $this->del( $installer::$tables[0] );
    $installer::$showTables = array();
    try {
      $installer->checkTable($this->config);
      $this->fail();
    } catch (Exception $e) {
      $this->pass();
    }
    
    // test with empty table
    $this->drop();
    $installer::$showTables = array();
    $this->assertTrue($installer->checkTable($this->config));
    
    // test with complete tables but with different prefix
    $tables = $installer::$tables;
    foreach ($tables as $key => $table) {
      $tables[$key] = 'prefix_' . $this->config['table_prefix'] . $table;
    }
    $this->create($tables);
    $installer::$showTables = array();
    $this->assertTrue($installer->checkTable($this->config));
    $this->drop();
  }
  
  public function testInstallerIsThinkTankTablesExist() {
    // test with complete tables
    $installer = Installer::getInstance();
    $installer::$showTables = array();
    $expected = $installer::$tables;
    $this->create($expected);
    $this->assertTrue($installer->isThinkTankTablesExist($this->config));
    $this->drop();
    
    // test with incomplete tables (will fail)
    $installer::$showTables = array();
    $expected = $installer::$tables;
    array_pop($expected);
    $this->create($expected);
    $this->assertFalse($installer->isThinkTankTablesExist($this->config));
    $this->drop();
  }
  
  public function testInstallerIsTableOk() {
    global $THINKTANK_CFG;
    $THINKTANK_CFG['table_prefix'] = $this->config['table_prefix'];
    
    $installer = Installer::getInstance();
    $installer::$showTables = array();
    $expected = $installer::$tables;
    $this->create($expected);
    foreach ($expected as $table) {
      $this->assertTrue($installer->isTableOk($table));
    }
    $this->drop();
    
    // check on nonexistent table
    $installer::$showTables = array();
    foreach ($expected as $table) {
      $this->assertFalse($installer->isTableOk($table));
    }
  }
  
  public function testInstallerIsAdminExists() {
    global $THINKTANK_CFG;
    $THINKTANK_CFG['table_prefix'] = $this->config['table_prefix'];
    $installer = Installer::getInstance();
    $admin = array();
    
    // test with admin user exists
    $installer::$showTables = array();
    $admin['site_email'] = 'admin@diazuwi.web.id';
    $admin['password'] = $installer->generatePassword();
    $admin['country'] = 'Indonesia';
    $admin['name'] = 'Dwi Widiastuti';
    $admin['is_admin'] = 1;
    $this->createAdminTable($admin);
    $this->assertTrue( $installer->isAdminExists() );
    $this->drop();
    
    // test with a user with is_admin = 0
    $installer::$showTables = array();
    $admin['is_admin'] = 0;
    $this->createAdminTable($admin);
    $this->assertFalse( $installer->isAdminExists() );
    $this->drop();
    
    // test with admin user doesn't exist
    $installer::$showTables = array();
    $this->createAdminTable();
    $this->assertFalse( $installer->isAdminExists() );
    $this->drop();
  }
  
  public function testInstallerIsThinkTankInstalled() {
    global $THINKTANK_CFG;
    $THINKTANK_CFG['table_prefix'] = $this->config['table_prefix'];
    $installer = Installer::getInstance();
    $config_file = THINKTANK_WEBAPP_PATH . 'config.inc.php';
    $config_file_exists = file_exists($config_file);
    
    if ( $config_file_exists ) {
      // test when config file exists
      $version_met = $installer->checkStep1();
      try {
        $db_check = $installer->checkDb($this->config);
      } catch (Exception $e) {}
      $table_present = $installer->isThinkTankTablesExist($this->config);
      $admin_exists = $installer->isAdminExists($this->config);
      try {
        $is_installed = $installer->isThinkTankInstalled();
      } catch (Exception $e) {}
      $expected = ($version_met && $db_check && $table_present && $admin_exists);
      $this->assertEqual($is_installed, $expected);
      
      // test failing one of requirements
      try {
        $is_installed = $installer->isThinkTankInstalled(FALSE);
      } catch (Exception $e) {}
      $this->assertFalse($is_installed);
    } else {
      // test when config doesn't exist
      $this->assertFalse( $installer->isThinkTankInstalled() );
      $expected = $installer->getErrorMessages();
      $this->assertEqual( $expected['config_file'], "Config file doesn't exist.");
    } 
  }
  
  public function testInstallerExamineQueries() {
    global $install_queries;
    
    $installer = Installer::getInstance();
    $this->drop();
    $installer::$showTables = array();
    $installer->populateTables($this->config);
    
    // test on fully installed tables
    $output = $installer->examineQueries($install_queries);
    $this->assertIdentical( array(), $output['for_update'] );
    $expected = "/INSERT INTO plugins/i";
    $this->assertIdentical( array(), $output['for_update'] );
    $this->assertPattern( $expected, $output['queries'][0] );
    
    // test on missing tables
    $this->del($this->config['table_prefix'] . 'owners');
    $installer::$showTables = array();
    $output = $installer->examineQueries($install_queries);
    $expected = "/Created table {$this->config['table_prefix']}owners/i";
    $this->assertPattern($expected, $output['for_update'][$this->config['table_prefix'] . 'owners']);
    $expected = "/CREATE TABLE {$this->config['table_prefix']}owners /i";
    $this->assertPattern($expected, $output['queries'][$this->config['table_prefix'] . 'owners']);
    
    // test on missing PRIMARY KEY
    $this->db->exec("ALTER TABLE {$this->config['table_prefix']}follows DROP PRIMARY KEY");
    $installer::$showTables = array();
    $output = $installer->examineQueries($install_queries);
    $add_pk = "ALTER TABLE {$this->config['table_prefix']}follows ADD PRIMARY KEY  (user_id,follower_id)";
    $add_pk_exists = in_array($add_pk, $output['queries']);
    $this->assertTrue($add_pk_exists);
    
    // test on missing index
    $this->db->exec("ALTER TABLE {$this->config['table_prefix']}follows DROP INDEX active");
    $installer::$showTables = array();
    $output = $installer->examineQueries($install_queries);
    $add_idx = "ALTER TABLE {$this->config['table_prefix']}follows ADD KEY active (active)";
    $add_idx_exists = in_array($add_idx, $output['queries']);
    $this->drop();
  }
  
  public function testInstallerPopulateTables() {
    $installer = Installer::getInstance();
    $this->drop();
    
    // test without verbose on empty test database
    $installer::$showTables = array();
    $this->assertTrue($installer->populateTables($this->config));
    $installer::$showTables = array();
    $this->assertTrue($installer->isThinkTankTablesExist($this->config));
    $installer::$showTables = array();
    // will throw an exception if table exists
    try {
      $installer->checkTable($this->config);
      $this->fail();
    } catch (Exception $e) {
      $this->pass();
    }
    $this->drop();
    
    // test with verbose on empty test database
    $installer::$showTables = array();
    // supply verbose on second paramater
    $log_verbose = $installer->populateTables($this->config, true);
    $this->assertIsA($log_verbose, 'Array');
    $tables = $installer::$tables;
    $expected = array();
    foreach ($tables as $k => $v) {
      $expected[$v] = "Created table {$this->config['table_prefix']}$v";
    }
    $this->assertEqual($log_verbose, $expected);
    $this->drop();
    
    // test on existent tables that's not recognized as a thinktank table
    $this->create('unordinary_table');
    $installer::$showTables = array();
    // supply verbose on second paramater
    $log_verbose = $installer->populateTables($this->config, true);
    $this->assertIsA($log_verbose, 'Array');
    $tables = $installer::$tables;
    $expected = array();
    foreach ($tables as $k => $v) {
      $expected[$v] = "Created table {$this->config['table_prefix']}$v";
    }
    $this->assertEqual($log_verbose, $expected);
    $this->drop();
    
    // test on existent tables that's recognized as a thinktank table
    $this->createAdminTable();
    $installer::$showTables = array();
    // supply verbose on second paramater
    $log_verbose = $installer->populateTables($this->config, true);
    $this->assertIsA($log_verbose, 'Array');
    $tables = $installer::$tables;
    $expected = array();
    foreach ($tables as $k => $v) {
      $expected[$v] = "Created table {$this->config['table_prefix']}$v";
    }
    unset($expected["{$this->config['table_prefix']}owners"]);
    $this->assertEqual($log_verbose, $expected);
    $this->drop();
    
    // test on fully thinktank table
    $installer::$showTables = array();
    $installer->populateTables($this->config);
    // supply verbose on second paramater
    $log_verbose = $installer->populateTables($this->config, true);
    $expected = array();
    $this->assertIdentical($log_verbose, $expected);
    $this->drop();
  }
  
  public function testInstallerRepairTables() {
    $installer = Installer::getInstance();
    $this->drop();
    
    // test repair on a healthy and complete tables
    $installer::$showTables = array();
    $installer->populateTables($this->config);
    $expected = '<p>Your ThinkTank tables are <strong class="okay">complete</strong>.</p>';
    $messages = $installer->repairTables($this->config);
    $this->assertIdentical($messages['table_complete'], $expected);
    
    // test repair on missing tables
    $this->del($this->config['table_prefix'] . 'owners');
    $installer::$showTables = array();
    $expected = '/There are <strong class="not_okay">1 missing tables/i';
    $messages = $installer->repairTables($this->config);
    $this->assertPattern($expected, $messages['missing_tables']);
    $this->drop();
  }
  
  public function testInstallerValidationAndPasswordGenerator() {
    $installer = Installer::getInstance();
    
    $this->assertTrue($installer->checkSiteName('Dwi Widiastuti'));
    $this->assertTrue($installer->checkSiteName("Dwi's ThinkTank"));
    // test that failed
    try {
      $this->assertTrue($installer->checkSiteName(""));
      $this->fail();
    } catch (Exception $e) {
      $this->pass();
    }
    
    $this->assertTrue($installer->checkValidEmail('admin@diazuwi.web.id'));
    $this->assertTrue($installer->checkValidEmail('username@example.com'));
    // test that failed
    try {
      $this->assertTrue($installer->checkValidEmail('root@localhost'));
      $this->fail();
    } catch (Exception $e) {
      $this->pass();
    }
    
    $random_passwd = $installer->generatePassword();
    $this->assertEqual(8, strlen($random_passwd));
    $random_passwd = $installer->generatePassword(10);
    $this->assertEqual(10, strlen($random_passwd));
  }
}
?>