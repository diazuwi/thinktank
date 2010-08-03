<?php
require_once 'classes/class.ThinkUpInstallerTestCase.php';

/**
 * Test Of Installer DAO
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 */
class TestOfInstallerMySQLDAO extends ThinkUpInstallerTestCase {
    public function testInstallerConnect() {
        $expected = Installer::$dao[$this->config['db_type']];
        $this->assertIsA($this->db, $expected);
        $this->assertIsA($this->db->installerConnect($this->config), 'PDO');
    }
    
    public function testExec() {
        $admin['email'] = 'admin@diazuwi.web.id';
        $admin['password'] = 'password';
        $admin['is_admin'] = 1;
        $this->drop();
        $this->createAdminTable($admin);
        
        $e = $this->db->exec( sprintf("SELECT email FROM %s WHERE email = '%s'", 
                     $this->config['table_prefix'] . 'owners', 'admin@diazuwi.web.id') );
        $result = $e->fetch(PDO::FETCH_ASSOC);
        $this->assertIdentical($result['email'], $admin['email']);
        
        $admin['email'] = 'diazuwi@gmail.com';
        $this->db->insertAdmin($this->config['table_prefix'] . 'owners', $admin);
        $e = $this->db->exec( sprintf("SELECT email FROM %s WHERE 1", 
                     $this->config['table_prefix'] . 'owners') );
        $rows = array();
        while ($result = $e->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $result;
        }
        $expected = array(
            array('email' => 'admin@diazuwi.web.id'), array('email' => 'diazuwi@gmail.com')
        );
        $this->assertIdentical($rows, $expected);
        $this->drop();
    }
    
    public function testShowTables() {
        $this->createAdminTable(null);
        $result = $this->db->showTables();
        $this->assertIdentical($result, array($this->config['table_prefix'] . 'owners'));
        $this->drop();
    }
    
    public function testCheckTable() {
        $this->createAdminTable(null);
        $result = $this->db->checkTable($this->config['table_prefix'] . 'owners');
        $this->assertTrue(array_key_exists('Msg_text', $result));
        $this->drop();
    }
    
    public function testIsAdminExists() {
        $this->createAdminTable(null);
        $result = $this->db->isAdminExists($this->config['table_prefix'] . 'owners');
        $this->assertFalse($result);
        $this->drop();
        
        $admin['email'] = 'admin@diazuwi.web.id';
        $admin['password'] = 'password';
        $admin['is_admin'] = 1;
        $this->createAdminTable($admin);
        $result = $this->db->isAdminExists($this->config['table_prefix'] . 'owners');
        $this->assertTrue($result);
        $this->drop();
    }
    
    public function testInsertAdmin() {
        $this->createAdminTable(null);
        $admin['email'] = 'admin@diazuwi.web.id';
        $admin['password'] = 'password';
        $result = $this->db->insertAdmin($this->config['table_prefix'] . 'owners', $admin);
        $this->assertTrue($result);
        
        $e = $this->db->exec('SELECT email from ' . $this->config['table_prefix'] . 'owners');
        $result = $e->fetch(PDO::FETCH_ASSOC);
        $this->assertIdentical($result['email'], $result['email']);
        $this->drop();
    }
    
    public function testRepairTable() {
        $this->createAdminTable(null);
        $result = $this->db->repairTable($this->config['table_prefix'] . 'owners');
        $this->assertTrue(array_key_exists('Msg_text', $result));
        $this->drop();
    }
    
    public function testDescribeTable() {
        $this->createAdminTable(null);
        $result = $this->db->describeTable($this->config['table_prefix'] . 'owners');
        foreach ($result as $field) {
            $this->assertTrue(isset($field->Field));
            $this->assertTrue(isset($field->Type));
            $this->assertTrue(isset($field->Null));
            $this->assertTrue(isset($field->Key));
            $this->assertTrue(isset($field->Extra));
        }
        $this->drop();
    }
    
    public function testShowIndex() {
        $this->createAdminTable(null);
        $result = $this->db->showIndex($this->config['table_prefix'] . 'owners');
        $this->assertIdentical("id", $result[0]->Column_name);
        $this->assertIdentical("PRIMARY", $result[0]->Key_name);
        $this->drop();
    }
    
    public function testExamineQueries() {
        global $install_queries;
        
        // populate tables using Installer
        $installer = Installer::getInstance($this->controller);
        $installer->setDb($this->config);
        $this->drop();
        $installer::$showTables = array();
        $installer->populateTables($this->config);
        unset($installer);
        
        // test on fully installed tables
        $output = $this->db->examineQueries($install_queries, $this->db->showTables());
        $this->assertIdentical( array(), $output['for_update'] );
        $expected = "/INSERT INTO {$this->config['table_prefix']}plugins/i";
        $this->assertIdentical( array(), $output['for_update'] );
        $this->assertPattern( $expected, $output['queries'][0] );
        
        // test on missing tables
        $this->del($this->config['table_prefix'] . 'owners');
        $output = $this->db->examineQueries($install_queries, $this->db->showTables());
        $expected = "/Created table {$this->config['table_prefix']}owners/i";
        $this->assertPattern($expected, $output['for_update'][$this->config['table_prefix'] . 'owners']);
        $expected = "/CREATE TABLE {$this->config['table_prefix']}owners /i";
        $this->assertPattern($expected, $output['queries'][$this->config['table_prefix'] . 'owners']);
        
        // test on missing PRIMARY KEY
        $this->db->exec("ALTER TABLE {$this->config['table_prefix']}follows DROP PRIMARY KEY");
        $output = $this->db->examineQueries($install_queries, $this->db->showTables());
        $add_pk = "ALTER TABLE {$this->config['table_prefix']}follows ADD PRIMARY KEY  (user_id,follower_id)";
        $add_pk_exists = in_array($add_pk, $output['queries']);
        $this->assertTrue($add_pk_exists);
        
        // test on missing index
        $this->db->exec("ALTER TABLE {$this->config['table_prefix']}follows DROP INDEX active");
        $output = $this->db->examineQueries($install_queries, $this->db->showTables());
        $add_idx = "ALTER TABLE {$this->config['table_prefix']}follows ADD KEY active (active)";
        $add_idx_exists = in_array($add_idx, $output['queries']);
        $this->drop();
    }
}
