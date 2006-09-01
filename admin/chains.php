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


class m2f_admin_chains extends m2f_admin_common
{
	function m2f_admin_chains()
	{
		$this->_connect_db();
		$this->_assign_lang();
		$this->_parse_request();
		$this->_init_template();
		$this->_assign_channel_lang();
		$this->_do_action();
		$this->_do_view();
		$this->_do_display('chains');
	}
	
	function _do_action()
	{
		switch($this->action)
		{
			case 'create_chain':
				$wanted_vars = array('chain_name', 'chain_description');
				$post_vars = $this->_check_form_vars($wanted_vars, 'chains', 'create_chain');
				if (FALSE === ($chain_id = $this->_create_chain($this->_request['chain_name'], $this->_request['chain_description']))) $this->_redirect('chains', 'create_chain', $this->_request);
				$this->_message = $this->_lang['chain_created'];
				$this->_redirect('chains', 'display_chain', array('chain_id' => $chain_id));
				break;
				
			case 'add_chain_channel':
				if (!isset($this->_request['channel_id']) || !isset($this->_request['chain_id'])) $this->_redirect('chains', 'chains');
				$this->_redirect('chains', 'configure_chain_channel', $this->_request);
				break;
				
			case 'save_chain_channel':
				if ($this->_add_chain_channel()) 
				{
					$this->_message = $this->_lang['chain_channel_created'];
					$this->_redirect('chains', 'display_chain', $this->_request);
				}
				break;
				
			case 'save_chain_channel_direction':
				$this->_check_form_vars('direction', 'chains', 'configure_chain_channel');
				$this->_redirect('chains', 'configure_chain_channel_fields', $this->_request);
				break;
				
			case 'delete_chain':
				$chain = $this->_get_chain_from_id();
				$this->_delete_chain($chain->id);
				$this->_message = $this->_lang['chain_deleted'];
				$this->_redirect('chains', 'chains');
				break;

			default:
				break;
		}
	}

	function _do_view()
	{
		switch($this->view)
		{
			case 'display_chain':
				$chain = $this->_get_chain_from_id();
				$this->tmp->assign('display_chain', TRUE);
				$this->tmp->assign('chain', $chain);
				break;
				
			case 'add_chain_channel':
				$chain = $this->_get_chain_from_id();
				$this->tmp->assign('add_chain_channel', TRUE);
				$this->tmp->assign('chain_id', $this->_request['chain_id']);
				
				$channels = array();
				$installed_channels = $this->_get_channels();
				if (empty($installed_channels)) 
				{
					m2f::raise_error($this->_lang['no_installed_channels'], __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
					$this->_redirect('channels', 'uninstalled_channels', $this->_request);
				}
				$this->tmp->assign('channels', $this->_make_channel_array($installed_channels));
				break;

			case 'create_chain':
				foreach (array('chain_name', 'chain_description') as $var)
				{
					$this->tmp->assign($var, (isset($this->_request[$var]) ? $this->_request[$var] : ''));
				}
				$this->tmp->assign('new_chain', TRUE);
				break;
				
			case 'view_chain_element':
				$chain = $this->_get_chain_from_id();
				if (!isset($this->_request['element_number'])) $this->_redirect('chains', 'display_chain', $this->_request);
				$channels = $this->_get_channels();
//				foreach ($channels as $channel)
//				{
//					if ($channel->class == $chain->elements[$this->_request['element_number']]->class) $params = $this->_get_channel_params($channel->id);
//				}
//				foreach ($chain->elements as $element)
//				{
//					if ($element->class == $this->_request['element_type'] && $element->id == $this->_request['element_id']) 
//					{
//						foreach ($params->channel_fields as $field)
//						{
//						}
//						$this->tmp->assign('channel', $element);
//					}
//				}
//				
//				$this->tmp->assign('view_chain_element', TRUE);
//				$this->tmp->assign('chain_id', $chain->id);
				break;
				
			case 'configure_chain_channel':
				$channel = $this->_get_channel_from_id();
				$chain = $this->_get_chain_from_id();
				list($in, $out) = $this->_get_direction_options($channel->properties);
				if ($in && $out)
				{
					$this->tmp->assign('configure_chain_channel_direction', TRUE);
					$this->tmp->assign('choose_direction', TRUE);
					$this->tmp->assign('chain_id', $chain->id);
					$this->tmp->assign('channel', $channel);
					}
				else
				{
					$this->_redirect('chains', 'configure_chain_channel_fields', array('chain_id' => $chain->id, 'channel_id' => $channel->id, 'direction' => ($in ? 'in' :'out')));
				}
				break;
				
			case 'configure_chain_channel_fields':
				if (!isset($this->_request['direction']))
				{
					m2f::raise_error(sprintf($this->_lang['missing_var'], 'direction'), __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
					$this->_redirect('chains', 'configure_chain_channel', $this->_request);
				}
				
				$channel = $this->_get_channel_from_id();
				$chain = $this->_get_chain_from_id();
				
				if (!empty($channel->channel_fields))
				{
					foreach (array_keys($channel->channel_fields) as $field)
					{
						$channel->channel_fields[$field]['value'] = isset($this->_request[$channel->channel_fields[$field]['name']]) 
																												? $this->_request[$channel->channel_fields[$field]['name']]
																												: NULL;
					}
				}
				
				$this->tmp->assign('configure_chain_channel_fields', TRUE);
				$this->tmp->assign('chain_id', $chain->id);
				$this->tmp->assign('channel', $channel);
				$this->tmp->assign('direction', $this->_request['direction']);
				break;
				
			case 'delete_chain':
				$chain = $this->_get_chain_from_id();
				$this->tmp->assign('delete_chain', TRUE);
				$this->tmp->assign('chain', $chain);
				break;
							
			case 'chains':
			default:
				$chains = $this->_get_chains();
				if (empty($chains) && empty($this->_request)) 
				{
					$this->_message = $this->_lang['no_chains'];
					$this->_redirect('chains', 'create_chain');
				}
				$this->tmp->assign('list_chains', TRUE);
				$this->tmp->assign('chains', $chains);
				break;
		}
	}
	

}


new m2f_admin_chains;

?>