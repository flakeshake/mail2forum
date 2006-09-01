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
define('M2F_NOT_INSTALLED', TRUE);
require_once('includes/includes.php');
require_once('admin.common.php');


class m2f_admin_install extends m2f_admin_common
{
	var $conf_file = 'conf/conf.ini';
	var $conf_dist_file = 'conf/conf.ini.dist';
	
	var $_lang = array( 'title' => 'Mail2Forum Installation', 
										'error' => 'Error', 
										'message' => 'Message', 
										'save_config' => 'Save Configuration', 
										'install_db' => 'Install Database', 
										'init_db' => 'Initialise Database', 
										'missing_var' => 'Value <strong>%s</strong> must be completed', 
										'log_file' => 'Log file', 
										'language' => 'Language', 
										'db_host' => 'Database host', 
										'db_user' => 'Database user', 
										'db_pass' => 'Database pass', 
										'db_database' => 'Database name', 
										'db_type' => 'Database type', 
										'db_prefix' => 'Database prefix', 
									);	
																		
	function m2f_admin_install()
	{
		$this->_parse_request();
		$this->_init_template();
		$this->_do_action();
		$this->_do_view();
		$this->_do_display('install');
	}
	

	function _do_action()
	{
		switch($this->action)
		{
			case 'save_config':
				$error = FALSE;
				
//				$wanted_vars = array('log_file', 'language', 'db_host', 'db_user', 'db_pass', 'db_database', 'db_type', 'db_prefix');
				$wanted_vars = array('language', 'db_host', 'db_user', 'db_pass', 'db_database', 'db_type', 'db_prefix');
				$conf = $this->_check_form_vars($wanted_vars, 'install', 'install');
				
//				$this->_test_log_file_path($conf['log_file']);
				$this->_connect_db($conf);
				$this->_write_conf_file($conf);
				
				$this->_message = 'Configuration settings saved. Now install the database tables.';
				$this->_redirect('install', 'install_database');
				break;
				
			case 'install_db':
			case 'init_db':
				$conf = parse_ini_file($this->conf_file);
				$this->_connect_db($conf);
				$this->_install_database_tables();
				$this->_populate_db();
				$this->_message = 'Installation complete!';
				$this->_redirect('index');
				break;
				
			default:
				if (!isset($this->_request['message']) && !isset($this->_request['error']) && is_readable($this->conf_file))
				{
					$conf = @parse_ini_file($this->conf_file);
					if (!empty($conf))
					{
						$this->_message = 'Configuration settings are already saved';
						$this->_redirect('install', 'initialise_database');
					}
				}
				break;
		}
	}

	function _do_view()
	{
		switch($this->view)
		{
			case 'install_database':
				$this->tmp->assign('install_database', TRUE);
				break;
				
			case 'initialise_database':
				$this->tmp->assign('initialise_database', TRUE);
				break;
				
			case 'complete':
				$this->tmp->assign('complete', TRUE);
				break;
				
			case 'install':
				$this->tmp->assign('config_form', TRUE);
				$this->tmp->assign('db_types', array('mssql' => 'mssql', 'mysql' => 'mysql', 'oci8' => 'oci8 (Oracle 8/9)', 'postgres7' => 'postgres7', 'postgres8' => 'postgres8'));
				$this->tmp->assign('languages', array('en' => 'English'));
				
				foreach (array('log_file', 'language', 'db_host', 'db_user', 'db_pass', 'db_database', 'db_type', 'db_prefix') as $var)
				{
					if (isset($this->_request[$var])) $this->tmp->assign($var, $this->_request[$var]);
				}
				break;
				
			default:
				$this->tmp->assign('config_form', TRUE);
				$this->tmp->assign('db_types', array('mssql' => 'mssql', 'mysql' => 'mysql', 'oci8' => 'oci8 (Oracle 8/9)', 'postgres7' => 'postgres7', 'postgres8' => 'postgres8'));
				$this->tmp->assign('languages', array('en' => 'English'));
				
				$this->tmp->assign('db_host', 'localhost');
				$this->tmp->assign('db_prefix', 'm2f');
				$this->tmp->assign('db_type', 'mysql');
				$this->tmp->assign('language', 'en');
				break;
		}

		$this->tmp->assign('hide_menu', TRUE);
	}
	
}

new m2f_admin_install;
