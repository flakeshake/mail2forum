<?php
// $Id: mdb_test.php,v 1.3 2004/11/18 20:34:07 quipo Exp $

require_once 'db_test.php';

class TestOfContainerMDB extends TestOfContainerDB {
    function TestOfContainerMDB($name='Test of Container MDB') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'MDB';
        $this->tr = Translation2::factory($driver, dbms::getDbInfo(), dbms::getParams());
    }
}
?>