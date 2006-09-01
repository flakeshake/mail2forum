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
require_once('m2f.php');
require_once('admin.common.php');


class m2f_admin_channels extends m2f_admin_common
{
	function m2f_admin_channels()
	{
		$this->_connect_db();
		$this->_assign_lang();
		$this->_parse_request();
		$this->_init_template();
		$this->_assign_channel_lang();
		$this->_do_action();
		$this->_do_view();
		$this->_do_display('channels');
	}
	
	function _do_action()
	{
		switch($this->action)
		{
			case 'install_selected_channels':
				if (!empty($this->_request['channel_path'])) 
				{
					$installed = $this->_install_channels($this->_request['channel_path']);
					if ($installed) $this->_print_channels_installed_message($installed);
					if (isset($this->_request['chain_id']))
					{
						$this->_redirect('chains', 'add_chain_channel', array('chain_id' => $this->_request['chain_id']));
					}
					else
					{
						$this->_redirect('channels', 'channels');
					}
				}
				else 
				{
					m2f::raise_error($this->_lang['no_channels_selected'], __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
					$this->_redirect('channels', 'uninstalled_channels');
				}
				break;

			default:
				break;
		}
	}

	function _do_view()
	{
		switch($this->view)
		{
			case 'uninstalled_channels':
				$this->tmp->assign('list_uninstalled_channels', TRUE);
				$this->tmp->assign('uninstalled_channels', $this->_get_uninstalled_channels());
				if(isset($this->_request['chain_id'])) $this->tmp->assign('chain_id', $this->_request['chain_id']);
				break;
				
			case 'channels':
			default:
				$this->tmp->assign('list_installed_channels', TRUE);
				$channels = $this->_get_channels();
				if (empty($channels)) 
				{
					$this->_message = $this->_lang['no_installed_channels'];
					$this->_redirect('channels', 'uninstalled_channels', $this->_request);
				}
				foreach (array_keys($channels) as $key)
				{
					$channels[$key]->name = m2f_lang::get('channel_name', $channels[$key]->class);
				}
				$this->tmp->assign('installed_channels', $channels);
				if ($this->_get_uninstalled_channels()) $this->tmp->assign('show_install_channel_link', TRUE);
				break;
		}
		
	}
	

}




new m2f_admin_channels;

?>