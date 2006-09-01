<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/

chdir(realpath('../'));
require('m2f.php');
require_once('admin.common.php');

if (isset($_GET['php_error']))
{
	if (preg_match('#^(.*)in (.*?) on line (\d+)$#', $_GET['php_error'], $matches))
	{
		m2f::raise_error($matches[1], $matches[3], $matches[2], M2F_LOG_FATAL);
	}
	else
	{
		m2f::raise_error($_GET['php_error'], __FILE__, __LINE__, M2F_LOG_FATAL);
	}
}

class m2f_admin extends m2f_admin_common
{
	function m2f_admin()
	{
		$this->_connect_db();
		$this->_parse_request();
		$this->_assign_lang();
		$this->_init_template();
		$this->_do_display('error');
	}	
}

new m2f_admin;

?>