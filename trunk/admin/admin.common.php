<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


/**
 *   Set special error handler for Fatal errors
 */
 
if (!defined('M2F_IN_TEST_MODE') && !defined('M2F_NOT_INSTALLED')) 
{
	$url = preg_replace('#/[^/]+\.php#', '/error.php', $_SERVER['PHP_SELF']);
	ini_set('html_errors',false);
	ini_set('error_prepend_string','<html><head><META http-equiv="refresh" content="0;URL=' . $url . '?php_error=');
	ini_set('error_append_string','"></head></html>');
}

class m2f_admin_common
{

	######################################################
	#                                                    #
	#                  Core Functions                    #
	#                                                    #
	######################################################
	
	function _assign_lang()
	{
		$this->_lang = m2f_lang::get_all('admin');
	}
	
	function _parse_request()
	{
		$this->_get_request_vars();
		$this->view = empty($this->_request['view']) ? '' : $this->_request['view'];
		
		$this->action = empty($this->_request['action']) ? '' : current(array_keys($this->_lang, $this->_request['action']));
	}

	function _stripslashes_deep($value)
	{
		$value = is_array($value)
							?	array_map(array($this, '_stripslashes_deep'), $value)
							:	stripslashes($value);
		
		return $value;
	}

	function _get_request_vars()
	{
		if (get_magic_quotes_gpc())
		{
			$_POST = array_map(array($this, '_stripslashes_deep'), $_POST);
			$_GET = array_map(array($this, '_stripslashes_deep'), $_GET);
//			$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
		}
		$this->_request = array_merge($_GET, $_POST);
	}

	function _init_template()
	{
		require_once('includes/smarty/Smarty.class.php');
		$this->tmp = new Smarty();
		$this->tmp->template_dir = 'admin/template_files';
		$this->tmp->compile_dir = 'admin/template_compiled';
		$this->tmp->cache_dir = 'admin/template_cache';
		$this->tmp->config_dir = 'admin/template_config';
		$this->tmp->plugins_dir[] = 'admin/template_plugins'; 

		$this->tmp->assign('lang', $this->_lang);
	}

	function _redirect($page = 'index', $view = '', $values = array())
	{
		$host = $_SERVER['HTTP_HOST'];
		$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$query = "?view=$view";
		unset($values['action'], $values['view']);
		foreach ($values as $key => $val) $query .= "&$key=" . urlencode($val);
		
		if (!defined('M2F_NOT_INSTALLED') && $errors = m2f_log::get_stored_errors()) $query .= "&error=" . urlencode(serialize($errors));
		
		if (isset($this->_message)) $query .= "&message=" . urlencode($this->_message);

		if (!headers_sent() && !ob_get_contents())
		{
			header("Location: http://$host$uri/$page.php$query");
		}
		else
		{
			$this->tmp->assign('redirect', "http://$host$uri/$page.php$query");
			$this->tmp->display('redirect.tpl');
		}
		exit;
	}

	function _do_display($page)
	{

		$errors = array();
		
		if (!empty($this->_request['error'])) $errors = unserialize($this->_request['error']);
		
		if (!defined('M2F_NOT_INSTALLED') && $new_errors = m2f_log::get_stored_errors()) $errors = array_merge($errors, $new_errors);

		if ($errors)
		{
			foreach ($errors as $error)
			{
				$error['file'] = str_replace(getcwd() . '/', '', $error['file']);
				switch($error['level'])
				{
					case M2F_LOG_ADMIN_ERROR:
						$error_list[] = array('message' => $error['message'],
																	'type' => 'admin');
						break;
					case M2F_LOG_FATAL:
						$error_list[] = array('message' => $error['message'] . ' [' . $error['file'] . ', line ' . $error['line'] . ']',
																	'type' => 'fatal');
						break;
					default:
						$error_list[] = array('message' => $error['message'] . ' [' . $error['file'] . ', line ' . $error['line'] . ']',
																	'type' => 'other');
						break;
				}
			}
		}
		
		if (!empty($error_list))
		{
			$this->tmp->assign('errors', $error_list);
		}
		else if (!empty($this->_request['message'])) 
		{
			$this->tmp->assign('message', $this->_request['message']);
		}
		
		$this->tmp->display($page . '.tpl');		
	}
	
	function _check_form_vars($wanted, $redirect_page, $redirect_view)
	{
		$missing = array();
		$wanted = (array) $wanted;
		$ret = array();
		foreach ($wanted as $var)
		{
			if (!isset($this->_request[$var]) || $this->_request[$var] === '')
			{
				$missing[] = $this->_get_lang_key_for_error($var);
			}
			else
			{
				$ret[$var] = $this->_request[$var];
			}
		}
		if (!empty($missing))
		{
			if (defined('M2F_NOT_INSTALLED'))
			{
				$and = 'and';
				$missing_vars = 'Form fields [%s] must be completed.';
			}
			else
			{
				$and = m2f_lang::get('and', 'core');
				$missing_vars = $this->_lang['missing_vars'];
			}
			
			$missing_list = preg_replace('#,(?= [^,]+$)#', ' ' . $and, implode('], [', $missing));
			$message = sprintf($missing_vars, $missing_list);
			
			if (defined('M2F_NOT_INSTALLED'))
			{
				$this->_redirect($redirect_page, $redirect_view, array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));
			}
			else
			{
				m2f::raise_error($message, __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
				$this->_redirect($redirect_page, $redirect_view, $this->_request);
			}
			
		}
		return $ret;
	}

	function _get_lang_key_for_error($var)
	{
		return isset($this->_lang[$var]) ? $this->_lang[$var] : ( isset($this->_request['channel_class']) && (isset($this->_lang[$this->_request['channel_class']]['field_' . $var]) ) ? $this->_lang[$this->_request['channel_class']]['field_' . $var] : $var);
	}




	######################################################
	#                                                    #
	#                    Channels                        #
	#                                                    #
	######################################################

	function _get_channels($channel_id = NULL)
	{
		$mapper =& m2f_factory::make_object('channel', TRUE);
		return $channel_id ? $mapper->get($channel_id) : $mapper->get_all();
	}

	function _get_uninstalled_channels()
	{
		$all = $this->_get_channels_in_directory();

		$installed = array();
		foreach ($this->_get_channels() as $info)
		{
			$installed[] = $info->path;
		}
		
		$uninstalled = array();
		foreach ($all as $info)
		{
			if (in_array($info['path'], $installed)) continue;
			$uninstalled[] = $info;
		}
		
		return $uninstalled;
	}

	function _get_channels_in_directory($root = 'channels', $init = TRUE)
	{
		static $available;
		static $directories;
		
		if ($init) $available = $directories = array();
		
		$handle = opendir($root);
		while (false !== ($filename = readdir($handle))) 
		{
			if ($filename == '.' || $filename == '..' || $filename == '.svn') continue;
			$path = $root . '/' . $filename;
			if (is_dir($path)) 
			{
				$directories[] = $filename;
				$this->_get_channels_in_directory($path, FALSE);
			}
			else if (in_array(($channel = str_replace('.php', '', $filename)), $directories))
			{
				$available[] = array('name' => $channel, 'path' => $path);
			}
		}
		closedir($handle);
		return $available;
	}

	function _install_channels($channel_array)
	{
		$installed = $this->_get_channels();

		foreach ($installed as $info)
		{
			if (in_array($info->path, $channel_array))
			{
				m2f::raise_error(sprintf($this->_lang['channel_already_installed'], $info->path), __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
				return FALSE;
			}
		}

		$installed = array();
		
		foreach ($channel_array as $path)
		{
			if (!preg_match("#^(channels/.*?)/(\w+)\.php$#i", $path, $matches))
			{
				m2f::raise_error(sprintf($this->_lang['invalid_channel_path'], $path), __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
				return FALSE;
			}
			$dir = $matches[1];
			$class = str_replace("/", "_", $dir);
			$obj =& m2f_factory::make_object($class);
			$obj->path = $path;
			$obj->class = $class;
			
			$schema_file = $dir . '/schema.xml';

			if (!is_readable($schema_file))
			{
				m2f::raise_error(sprintf($this->_lang['bad_permissions_for_channel_XML'], $schema_file), __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
				$this->_redirect('channels', 'channels');
			}
			
			$this->_create_table($schema_file, 'channels');
			$this->_truncate_table($class, 'channels');
			$this->_drop_sequence($class);
			$fields = $this->_get_element_fields($schema_file);

			$obj->channel_fields = $fields;

			$mapper =& m2f_factory::make_object('channel', TRUE);
			$mapper->insert($obj);

			$installed[] = m2f_lang::get('channel_name', $class);
		}

		return $installed;
	}
	
	function _print_channels_installed_message($installed)
	{
		$num = count($installed);
		$str = preg_replace('#,(?= [^,]+$)#', ' ' . m2f_lang::get('and', 'core'), implode('], [', $installed));
		$this->_message = ($num == 1) 
			? sprintf($this->_lang['channel_installed'], $str) 
			: sprintf($this->_lang['channels_installed'], $str);
	}
	
	function _assign_channel_lang()
	{
		foreach ($this->_get_channels() as $channel)
		{
			$this->_lang[strtolower($channel->class)] = m2f_lang::get_all($channel->class);
		}
		$this->tmp->assign('lang', $this->_lang);
	}

	function _make_channel_array($installed_channels)
	{
		$labled_channels = array();
		$other_channels = array();
		foreach ($installed_channels as $installed_channel) 
		{
			if (preg_match('#channels_(.*?)_.*?#', $installed_channel->class, $matches))
			{
				$labled_channels[$matches[1]][$installed_channel->id] = m2f_lang::get('channel_name', $installed_channel->class);
			}
			else
			{
				$other_channels['other'][$installed_channel->id] = m2f_lang::get('channel_name', $installed_channel->class);
			}
		}
		return $labled_channels + $other_channels;
	}

	function _get_channel_from_id()
	{
		if (empty($this->_request['channel_id']))
		{
			m2f::raise_error($this->_lang['unspecified_channel'], __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
			$this->_redirect('chains', 'chains', $this->_request);
		}
		if (!($params = $this->_get_channel_params($this->_request['channel_id'])))
		{
			m2f::raise_error(sprintf($this->_lang['unrecongnised_channel'], $this->_request['channel_id']), __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
			$this->_redirect('chains', 'chains', $this->_request);
		}
		return $params;
	}

	function _get_channel_params($channel_id)
	{
		$mapper =& m2f_factory::make_object('channel', TRUE);
		$channel =& $mapper->get($channel_id);
		$channel_fields = !empty($channel->channel_fields) ? unserialize($channel->channel_fields) : '';
		if (!$channel_fields) return $channel;
		foreach ($channel_fields as $key => $field)
		{
			if (in_array($field['name'], array('id', 'direction'))) unset($channel_fields[$key]);
		}
		$channel->channel_fields = array_values($channel_fields);
		return $channel;
	}

	function _get_direction_options($properties)
	{
		$in = ($properties & M2F_CHANNEL_CAN_IMPORT) ? TRUE : FALSE;
		$out = ($properties & M2F_CHANNEL_CAN_EXPORT) ? TRUE : FALSE;
		return array($in, $out);
	}

	function _add_chain_channel()
	{
		$channel_params = $this->_get_channel_params($this->_request['channel_id']);

		$fields = array();
		foreach ($channel_params->channel_fields as $field) $fields[] = $field['name'];
		array_push($fields, 'direction');
		
		$post_data = $this->_check_form_vars($fields, 'chains', 'configure_chain_channel_fields');
		
		$fields = $this->_check_field_values($channel_params->channel_fields, $post_data);
		$channel =& m2f_factory::make_object($this->_request['channel_class']);
		foreach ($fields as $name => $val)
		{
			if (in_array($name, $channel->channel_params))
			{
				$channel->$name = $val;
			}
			else
			{
				$channel->config[$name] = $val;
			}
		}
		
		if (TRUE !== $channel->test_config()) 
		{
			$this->_redirect('chains', 'configure_chain_channel_fields', $this->_request);
		}

		$chain_mapper =& m2f_factory::make_object('chain', TRUE);
		$chain =& $chain_mapper->get($this->_request['chain_id']);
		$chain->add_element($channel, $this->_request['direction']);
		$chain_mapper->update($chain);

		return TRUE;
	}

	function _check_field_values($field_list, $request)
	{
		$ret = array();
		foreach ($field_list as $field)
		{
			$var = $request[$field['name']];
			$size = (int) $field['size'];
						
			$error = FALSE;
			switch ($field['type'])
			{
				case 'boolean':
					if ($var !== '0' && $var !== '1') $error = TRUE;
					$var = (bool) $var;
					break;
					
				case 'integer':
				case 'float':
					if (is_numeric($var)) 
					{
						settype($var, $field['type']);
					}
					else 
					{
						$error = TRUE;
					}
					break;
					
				case 'string':
				default:
					$var = (string) trim($var);
					if ($size > 0 && strlen($var) > $size)
					{
						m2f::raise_error(sprintf($this->_lang['channel_field_too_long'], $this->_get_lang_key_for_error($field['name']), $size), __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
						$request[$field['name']] = substr($request[$field['name']], 0, $size);
						$this->_redirect('chains', 'configure_chain_channel_fields', $this->_request);
					}
					break;
			}
			
			if ($error)
			{
				m2f::raise_error(sprintf($this->_lang['channel_field_incorrect_type'], $this->_get_lang_key_for_error($field['name']), $field['type']), __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
				$this->_redirect('chains', 'configure_chain_channel_fields', $this->_request);
			}
			
			$ret[$field['name']] = $var;
		}
		return $ret;
	}



	######################################################
	#                                                    #
	#                       Chains                       #
	#                                                    #
	######################################################

	function _get_chains($chain_id = NULL)
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		return $chain_id ? $mapper->get($chain_id) : $mapper->get_all();
	}

	function _create_chain($name, $description)
	{
		$chain =& m2f_factory::make_object('chain');
		$chain->name = $name;
		$chain->description = $description;

		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($chain);
		
		return $chain->id;
	}
	
	function _get_chain_from_id()
	{
		if (empty($this->_request['chain_id']))
		{
			m2f::raise_error($this->_lang['unspecified_chain'], __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
			$this->_redirect('chains', 'chains', $this->_request);
		}
		if (!$chain = $this->_get_chains($this->_request['chain_id']))
		{
			m2f::raise_error(sprintf($this->_lang['unrecongnised_chain'], $this->_request['chain_id']), __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
			$this->_redirect('chains', 'chains', $this->_request);
		}
		return $chain;
	}

	function _delete_chain($chain_id)
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->delete($chain_id);
		return TRUE;
	}
	



	######################################################
	#                                                    #
	#                      Install                       #
	#                                                    #
	######################################################

	function _connect_db($conf = NULL)
	{
		if ($conf)
		{
			$this->db = adoNewConnection($conf['db_type']);
			$conn = $this->db->pconnect($conf['db_host'], $conf['db_user'], $conf['db_pass'], $conf['db_database']);
	
			if (!$conn)
			{
				$message = sprintf('Bad database connection: [%s]', $this->db->ErrorMsg());
				$this->_redirect('install', 'install', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));
			}
			
			$this->db->setFetchMode(ADODB_FETCH_ASSOC);
			$this->db_prefix = $conf['db_prefix'];
			
			return TRUE;
		}
		else if (!isset($this->db))
		{
			$this->db =& m2f_db::get_instance();
			$this->db_prefix = m2f_db::prefix();
		}
	}

	function _install_database_tables()
	{
		foreach (array('lang_languages', 'lang_strings', 'channel', 'chain_elements', 'chain', 'element', 'log') as $db_table)
		{
			$file = 'db/schema/' . $db_table . '.xml';
			if (!is_readable($file))
			{
				$message = sprintf('m2f schema file [%s] is not readable', $file);
				$this->_redirect('install', 'install_database', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));
			}
			$this->_create_table($file, 'install');
			$this->_truncate_table($db_table, 'install');
			$this->_drop_sequence($db_table);
		}
		return TRUE;
	}

	function _create_table($schema_file, $realm)
	{
		$xml = new adoSchema($this->db);
		$xml->setPrefix($this->db_prefix);
		
		$sql = $xml->ParseSchema($schema_file);
		if (is_string($sql))
		{
			$message = sprintf('Error in XML parsing [%s] in file [%s]', $sql, $schema_file);
			if ($realm == 'install')
			{
				$this->_redirect('install', 'install_database', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));
			}
			else
			{
				m2f::raise_error($message, __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
				$this->_redirect($realm, $realm);
			}
		}
		$result = $xml->ExecuteSchema();
		if (!$result) return $this->_sql_error($schema_file, $realm);
		return TRUE;
	}

	function _sql_error($schema_file = NULL, $realm = 'install')
	{
		$e = ADODB_Pear_Error();
		$message = $schema_file 
								? sprintf('Error executing SQL for [%s]: %s', $schema_file, $e->message)
								: sprintf('Error executing SQL: %s', $e->message);
			if ($realm == 'install')
			{
				$this->_redirect('install', 'install_database', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));
			}
			else
			{
				m2f::raise_error($message, __LINE__, __FILE__, M2F_LOG_ADMIN_ERROR);
				$this->_redirect($realm, $realm);
			}
	}

	function _truncate_table($db_table, $realm)
	{
		$result = $this->db->Execute('TRUNCATE TABLE ' . $this->db_prefix . '_' . $db_table);
		if (!$result) return $this->_sql_error($db_table, $realm);
		return TRUE;
	}
	
	function _drop_sequence($db_table)
	{
		$sequence = $this->db_prefix . '_seq_' . $db_table;
		$this->db->DropSequence($sequence);
		return TRUE;
	}

	function _populate_db()
	{
		foreach (array('db/sql/language_strings.sql', 'db/sql/language_languages.sql') as $file)
		{
			$array = $this->_get_sql_queries_and_command($file);
			list($queries, $command) = $array;
			$this->_execute_sql_queries($queries, $command);
		}

		$array = $this->_get_sql_queries_and_command('db/sql/elements.sql');
		list($element_types, $command) = $array;
		$queries = array();
		foreach ($element_types as $type)
		{
			$fields = $this->_get_element_fields('db/schema/' . $type . '.xml');
			$queries[] = array($type, $fields);
		}
		$this->_execute_sql_queries($queries, $command);

		return TRUE;
	}
	
	function _get_sql_queries_and_command($file)
	{
		if (!is_readable($file))
		{
			$message = sprintf('SQL file [%s] is not readable', $file);
			$this->_redirect('install', 'install_database', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));
		}
		include($file);
		if (empty($queries) || !is_array($queries) || empty($command) || !is_string($command))
		{
			$message = sprintf('SQL file [%s] is not in the correct format', $file);
			$this->_redirect('install', 'install_database', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));
		}
		return array($queries, $command);
	}
		
	function _execute_sql_queries($queries, $command)
	{
		foreach ($queries as $query)
		{
			$result = $this->db->execute($command, $query);
			if (!$result) return $this->_sql_error();
		}
		return TRUE;
	}
	
	function _get_element_fields($schema_file)
	{
		$schema = file_get_contents($schema_file);
		$fields = array();
		$xml = new m2f_element_xml_parser($fields);
		$xml->parse($schema);
		
		$new = array();
		foreach($fields as $field)
		{
  		switch ($field['TYPE'])
			{	
				case 'L':
					$type = 'boolean';
					break;
					
				case 'I':
				case 'I1':
				case 'I2':
				case 'I4':
				case 'I8':
					$type = 'integer';
					break;
					
				case 'N':
				case 'F':
					$type = 'float';
					break;
					
				default:
					$type = 'string';
					break;
			}
			$size = isset($field['SIZE']) ? (int) $field['SIZE'] : 0;
			$new[] = array('name' => $field['NAME'], 'type' => $type, 'size' => $size);
		}
		return serialize($new);
	}
	
	function _test_log_file_path($path)
	{
		if (is_file($path))
		{
			if (!is_writable($path)) $message = sprintf('Invalid file permissions of %s on log file: [%s]', substr(sprintf('%o', fileperms($path)), -4), $path);
		}
		else
		{
//
//			if (is_dir($path))
//			{
//				if (!is_writeable($path)) $message =  sprintf('Invalid permissions [%s] or owner for log file directory: [%s]', substr(sprintf('%o', fileperms($path)), -4), $path);
//			}
//			else
//			{
//				$message = sprintf('Invalid path for log file: [%s]', $path);
//			}
		}

		if (isset($message)) $this->_redirect('install', 'install', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));
		return TRUE;
	}

	function _write_conf_file($conf)
	{
		if (!is_readable($this->conf_dist_file))
		{
			$message = sprintf('Distributed configuration file [%s] is not readable', $this->conf_dist_file);
		}
		else if (!is_writeable('conf/'))
		{
			$message = sprintf('Configuration directory [%s] is not writeable', 'conf/');
		}
		else if (is_file($this->conf_file))
		{
			$message = sprintf('Configuration file [%s] already exists', $this->conf_file);
		}
		if (isset($message)) $this->_redirect('install', 'install', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));

		$handle = fopen($this->conf_dist_file, "r");
		$dist = fread($handle, filesize($this->conf_dist_file));
		fclose($handle);

		foreach ($conf as $lable => $var)
		{
			$dist = preg_replace('#^\s*' . $lable . '\s+=\s+".*?"#m', "$lable = \"$var\"", $dist);
		}
		
		if (!$handle = fopen($this->conf_file, 'w'))
		{
			$message = sprintf('Cannot open configuration file [%s] for writing', $this->conf_file);
		}
		else if (fwrite($handle, $dist) === FALSE)
		{
			$message = sprintf('Cannot write to file [%s]', $this->conf_file);
		}
		if (isset($message)) $this->_redirect('install', 'install', array_merge($this->_request, $this->_form_uninstalled_error_message($message, __LINE__, __FILE__)));

		fclose($handle);
		return TRUE;
	}

	function _form_uninstalled_error_message($message, $line, $file)
	{
		$errors[] = array('message' => $message,
												'line' => $line,
												'file' => $file,
												'level' => M2F_LOG_ADMIN_ERROR
												);
		return array('error' => serialize($errors));
	}

}




class m2f_element_xml_parser
{
	var $parser;
	
	function m2f_element_xml_parser(&$array) 
	{
		$this->array =& $array;
		$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'tag_open', 'tag_close');
	}
	
	function parse($data) 
	{
		xml_parse($this->parser, $data);
	}
	
	function tag_open($parser, $tag, $attributes) 
	{
		if ($tag === 'FIELD') $this->array[] = $attributes;
	}
	
	function tag_close($parser, $tag) 
	{
		return;
	}
}


?>