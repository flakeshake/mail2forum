<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/

define('M2F_IN_TEST_MODE', TRUE);

define('TESTS_DIR', dirname(__FILE__));
define('FILES_DIR', TESTS_DIR . '/files');


/**
 * Include m2f core
 */
require_once('../m2f.php');

/**
 * Clean up
 */
delete_files(FILES_DIR);


/**
 * Include Simpletest framework
 */
require_once(M2F_EXTERNAL_INCLUDES_PATH . '/simpletest/unit_tester.php');
require_once(M2F_EXTERNAL_INCLUDES_PATH . '/simpletest/reporter.php');
require_once(M2F_EXTERNAL_INCLUDES_PATH . '/simpletest/mock_objects.php');
require_once(M2F_EXTERNAL_INCLUDES_PATH . '/simpletest/web_tester.php');

/**
 * Include m2f base test class
 */
require_once('common.php');
require_once('display/display.php');


require_once 'PHP/Compat.php';
PHP_Compat::loadFunction('clone');

/**
 * Create group test
 */
$test = &new GroupTest('Mail2Forum v2.0 Simpletest Suite');



/**
 * Add m2f test files
 */
$test_files = array('channels', 
										'channels/email/pop', 
										'channels/email/mbox', 
										'channels/email/smtp', 
										'channels/forum/phpbb', 
										'channels/randomTextGenerator', 
										
										'filters', 
										'filters/bbcodeParser', 
										'filters/toUppercase', 
										
										'routers',
										'routers/isAllUppercase', 
										
										'logging', 
										'db', 
										'chains', 
										
										'admin/admin_chains', 
										'admin/admin_channels');
sort($test_files);

if (isset($_GET['all']))
{
	$tests_to_run = $test_files;
}
else if (isset($_GET['selected']) && !empty($_GET['tests']))
{
	$tests_to_run = $_GET['tests'];
}
else
{
	$tests_to_run = array();
}

foreach ($tests_to_run as $test_file)
{
	$test->addTestFile(TESTS_DIR . '/' . $test_file . '.php');
}



/**
 * Run tests
 */
$test->run(new m2f_HtmlReporter());




function delete_files($path)
{
	$handle = opendir($path);
	while (false !== ($file = readdir($handle))) 
	{
		if ($file == '.' || $file == '..' || $file == '.svn') continue;
		$filepath = $path . '/' . $file;
		if (is_file($filepath)) 
		{
			unlink($filepath);
		}
		else if (is_dir($filepath)) delete_files($filepath);
	}
	closedir($handle);
}

?>