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


class m2f_admin_schedule extends m2f_admin_common
{
	function m2f_admin_schedule()
	{
		$this->_connect_db();
		$this->_assign_lang();
		$this->_parse_request();
		$this->_init_template();
		$this->_assign_channel_lang();
		$this->_do_action();
		$this->_do_view();
		$this->_do_display('schedule');
	}
	
	function _do_action()
	{
		switch($this->action)
		{
			case 'run_all_chains':
				$this->_run_all();
				$this->_redirect('schedule', 'run_all_results');
				break;

			default:
				break;
		}
	}

	function _do_view()
	{
		switch($this->view)
		{
			case 'run_all_results':
				$this->tmp->assign('results', TRUE);
				break;
				
			default:
				$this->tmp->assign('display_options', TRUE);
				break;
		}
	}
	
	function _run_all()
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$chains =& $mapper->get_all();
		foreach ($chains as $chain)
		{
			$chain->run();
		}
	}
}


new m2f_admin_schedule;

?>