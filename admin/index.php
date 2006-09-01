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


class m2f_admin extends m2f_admin_common
{
	function m2f_admin()
	{
		$this->_connect_db();
		$this->_parse_request();
		$this->_assign_lang();
		$this->_init_template();
		$this->_do_action();
		$this->_do_view();
		$this->_do_display('index');
	}
	
	function _do_action()
	{
		switch($this->action)
		{
			default:
				break;
		}
	}

	function _do_view()
	{
		switch($this->view)
		{
			default:
				break;
		}
		
	}
	
}


new m2f_admin;

?>