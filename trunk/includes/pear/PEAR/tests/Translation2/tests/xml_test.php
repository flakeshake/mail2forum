<?php
// $Id: xml_test.php,v 1.2 2004/12/06 15:28:59 quipo Exp $

require_once 'db_test.php';

class TestOfContainerXML extends TestOfContainerDB {
    function TestOfContainerXML($name='Test of Container XML') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'XML';
        $options = array(
            'filename'         => 'i18n.xml',
            'save_on_shutdown' => true,
        );
        $this->tr =& Translation2::factory($driver, $options);
    }
}
?>