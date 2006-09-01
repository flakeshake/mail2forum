<?php
// $Id: admin_mdb2_test.php,v 1.2 2004/11/18 20:34:07 quipo Exp $

require_once 'admin_db_test.php';

class TestOfAdminContainerMDB2 extends TestOfAdminContainerDB {
    function TestOfAdminContainerMDB2($name='Test of Admin Container MDB2') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'MDB2';
        $this->tr = Translation2_Admin::factory($driver, dbms::getDbInfo(), dbms::getParams());
    }
}
?>