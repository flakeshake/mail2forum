<?php
// $Id: containers_tests.php,v 1.4 2004/11/19 17:09:21 quipo Exp $

require_once 'simple_include.php';
require_once 'translation2_include.php';

class ContainersTests extends GroupTest {
    function ContainersTests() {
        $this->GroupTest('Containers Tests');
        $this->addTestFile('db_test.php');
        $this->addTestFile('mdb_test.php');
        $this->addTestFile('mdb2_test.php');
        $this->addTestFile('gettext_mo_test.php');
        $this->addTestFile('gettext_po_test.php');
        $this->addTestFile('xml_test.php');
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new ContainersTests();
    $test->run(new HtmlReporter());
}
?>