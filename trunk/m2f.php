<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/

chdir(dirname(__FILE__));

require_once('includes/includes.php');

m2f::init();

######################################################
#                                                    #
#                    Mail2Forum                      #
#                                                    #
######################################################

class m2f 
{
	function init()
	{
		m2f_conf::get_instance();
		m2f_db::check_installed();
		m2f_log::get_instance();
		m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'm2f_starting', __LINE__, __FILE__); 
	}
	
	function is_error(&$data)
	{
		return PEAR::isError($data);
	}
	
	function &raise_error($message, $line, $file, $level = M2F_LOG_ERROR)
	{
		m2f_log::log_error($level, $message, $line, $file);
		return PEAR::raiseError($message);
	}
}


######################################################
#                                                    #
#                      DB                            #
#                                                    #
######################################################

class m2f_db 
{
	function &get_instance() 
	{ 
		static $instance = array(); 
		if (!$instance) 
		{
			$host = m2f_conf::get('db_host');
			$user = m2f_conf::get('db_user');
			$pass = m2f_conf::get('db_pass');
			$db = m2f_conf::get('db_database');
			$type = m2f_conf::get('db_type');
			
			$instance[0] =& adoNewConnection($type);

			$conn = $instance[0]->pconnect($host, $user, $pass, $db);
			$instance[0]->setFetchMode(ADODB_FETCH_ASSOC);
			
			m2f_db::prefix(m2f_conf::get('db_prefix'));
		}
		return $instance[0]; 
	}
	
	function &error()
	{
		$error =& ADODB_Pear_Error();
		return $error->message();
	}
	
	function prefix($new_prefix = NULL)
	{
		static $prefix = '';
		if ($new_prefix) $prefix = $new_prefix;
		return $prefix;
	}
	
	function check_installed()
	{
		$db =& m2f_db::get_instance();
		$tables = $db->MetaTables('TABLES');
		$prefix = m2f_db::prefix() . '_';
		foreach(array('lang_languages', 'lang_strings', 'channel', 'chain_elements', 'chain') as $table)
		{
			if (!in_array($prefix . $table, $tables)) 
			{
				$rel_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', getcwd());
				$host = $_SERVER['HTTP_HOST'];
				header("Location: http://$host$rel_path/admin/install.php");
				exit();
			}
		}
	}
}



######################################################
#                                                    #
#                     Conf                           #
#                                                    #
######################################################

class m2f_conf 
{
	function &get_instance() 
	{ 
		static $instance = array(); 
		if (!$instance) 
		{
			$file = 'conf/conf.ini';
			if (!is_readable($file))
			{
				$rel_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', getcwd());
				$host = $_SERVER['HTTP_HOST'];
				header("Location: http://$host$rel_path/admin/install.php");
				exit();
			}
			$instance[0] = parse_ini_file($file);
		}
		return $instance[0]; 
	}
	
	function get($key)
	{
		$conf =& m2f_conf::get_instance();
		return isset($conf[$key]) ? $conf[$key] : NULL;
	}
}



######################################################
#                                                    #
#                       Log                          #
#                                                    #
######################################################

class m2f_log 
{
	function &get_instance() 
	{ 
		static $instance = array(); 
		if (!$instance) 
		{
			$prefix = m2f_db::prefix();
			$conf['db'] =& m2f_db::get_instance();
			$conf['sequence'] = $prefix . '_seq_log';
			$instance[0] =& Log::singleton('ADODB', $prefix . '_log', '', $conf);
		}
		return $instance[0]; 
	}
	
	function log($level, $class, $message_key, $line, $file, $extras = null, $plural_tag = null)
	{
		$message = m2f_lang::get($message_key, $class);
		if ($extras !== NULL) $message = m2f_log::_replace_message_extras($message, $extras, $plural_tag);
		$message = m2f_log::form_log_message($message, $line, $file);
		m2f_log::_do_log($message, $level);
	}

	function _do_log(&$message, &$level)
	{
		$logger =& m2f_log::get_instance();
		$logger->log($message, $level);
	}
	
	function error_store($level, $message, $line, $file, $retrieve = FALSE)
	{
		static $errors = array();
		if ($retrieve)
		{
			return $errors;
		}
		else
		{
			$errors[] = array('message' => $message, 'level' => $level, 'line' => $line, 'file' => $file);
		}
	}
	
	function get_stored_errors($level = NULL)
	{
		$ret = array();
		$errors = m2f_log::error_store(NULL, NULL, NULL, NULL, TRUE);
		foreach($errors as $error)
		{
			if ($level && $error['level'] >= $level) continue;
			$ret[] = $error;
		}
		return $ret;
	}
	
	function log_error($level, $message, $line, $file)
	{
		m2f_log::error_store($level, $message, $line, $file);
		$message = m2f_log::form_log_message($message, $line, $file);
		if (defined('M2F_IN_TEST_MODE')) echo "<span class='error'>$message<br /></span>\n";
		m2f_log::_do_log($message, $level);
	}
	
	function form_log_message($message, $line, $file)
	{
		$message = preg_replace('/\s+/', ' ', $message);
		$file = str_replace(getcwd() . '/', '', $file);
		return "$message [$file, line $line]";
	}
	
	function _replace_message_extras($message, $extras, $plural_tag = null)
	{
		if (!is_array($extras)) $extras = array($extras);
		if ($plural_tag && is_numeric($plural_tag))
		{
			$message = str_replace('**M2F_PLURAL_TAG**', ($plural_tag == 1 ? '' : m2f_lang::get('**M2F_PLURAL_TAG**', 'core')), $message);
		}
		foreach ($extras as $key => $extra)
		{
			if ($extra === TRUE) $extras[$key] = 'TRUE';
			if ($extra === FALSE) $extras[$key] = 'FALSE';
		}
		return vsprintf($message, $extras);
	}
}



######################################################
#                                                    #
#                     Language                       #
#                                                    #
######################################################

class m2f_lang 
{
	function get($key, $section = 'm2f')
	{
		$lang =& m2f_lang::_load_lang_section((string) $section);
		if (!isset($lang[$key]))
		{
			while ($last_underscore = strrpos($section, '_'))
			{
				$section = substr($section, 0, $last_underscore);
				$lang =& m2f_lang::_load_lang_section($section);
				if (isset($lang[$key])) break;
			}
		}
		return isset($lang[$key]) ? $lang[$key] : '';
	}

	function &get_all($section = 'm2f')
	{
		$arr =& m2f_lang::_load_lang_section((string) $section);
		return $arr;
	}

	function &_load_lang_section($section)
	{
		static $lang = array();
		if (!isset($lang[$section]))
		{
			$tr =& m2f_lang::_get_lang_object();
			$lang[$section] = $tr->getPage($section);
		}
		return $lang[$section];
	}
	
	function _params()
	{
		$prefix = m2f_db::prefix();
		return array(
			'langs_avail_table' => $prefix . '_lang_languages',
			'lang_id_col'	 => 'id',
			'lang_name_col'   => 'name',
			'lang_meta_col'   => 'meta',
			'lang_errmsg_col' => 'error_text',
			'strings_default_table' => $prefix . '_lang_strings',
			'string_id_col'	  => 'id',
			'string_page_id_col' => 'section',
			'string_text_col'	=> '%s'
		);
	}
	
	function &_get_lang_object($refresh = FALSE)
	{
		static $instance = array();
		if ($refresh || !$instance)
		{
			$db =& m2f_db::get_instance();
			$params = m2f_lang::_params();
	
			$instance[0] =& Translation2_Admin::factory('ADODB', $db, $params);
			if (m2f::is_error($instance[0])) return m2f::raise_error('Cannot instantiate PEAR Translation class.', __LINE__, __FILE__);

			$primary_lang = m2f_conf::get('language');

			if (m2f::is_error($error = $instance[0]->setLang($primary_lang))) return m2f::raise_error('Cannot set primary language in PEAR Translation class.', __LINE__, __FILE__);
			$instance[0] =& $instance[0]->getDecorator('Lang');
			$instance[0]->setOption('fallbackLang', 'en');
			$instance[0] =& $instance[0]->getDecorator('ErrorText');
		}
		return $instance[0]; 
	}
	
	function _refresh_lang_object()
	{
		m2f_lang::_get_lang_object(TRUE);
	}
	
	function &_get_lang_admin_object()
	{
		static $instance = array();
		if (!$instance)
		{
			$db =& m2f_db::get_instance();
			$params = m2f_lang::_params();
			
			require_once 'Translation2/Admin.php';
			$instance[0] =& Translation2_Admin::factory('ADODB', $db, $params);
			if (m2f::is_error($instance[0])) return m2f::raise_error('Cannot instantiate PEAR Translation class.', __LINE__, __FILE__);
		}
		return $instance[0]; 
	}
	
	function get_langs()
	{
		$tr =& m2f_lang::_get_lang_object();
		return $tr->getLangs('array');
	}
	
	function add_lang($id, $name, $meta, $error_text, $encoding)
	{
		$params = array('lang_id'    => strtolower($id),
										'table_name' => m2f_db::prefix() . '_lang_strings',
										'name'       => $name,
										'meta'       => $meta,
										'error_text' => $error_text,
										'encoding'   => $encoding);
		$tr =& m2f_lang::_get_lang_admin_object();
		$ret = $tr->addLang($params);
		m2f_lang::_refresh_lang_object();
		return $ret;
	}
	
	function delete_lang($lang)
	{
		$tr =& m2f_lang::_get_lang_admin_object();
		$ret = $tr->removeLang($lang);
		m2f_lang::_refresh_lang_object();
		return $ret;
	}
}


######################################################
#                                                    #
#                     Factory                        #
#                                                    #
######################################################

class m2f_factory
{
	/** 
	* Instatiates a chain, channel, filter or router depending on parameters
	*  
	* @return object The element object 
	* @access public 
	* @param string $object_name name of the object we want to create
	* @param string $mapper special kind of object
	*/
	function &make_object($object_name, $mapper = FALSE)
	{
		$object_name = strtolower($object_name);
		$class_name = 'm2f_' . $object_name . ($mapper ? '_mapper' : '');

		if (!class_exists($class_name))
		{
			if ( ($pos = strrpos($object_name, '_')) !== FALSE)
			{
				$object_path = str_replace('_', '/', $object_name) . '/' . substr($object_name, $pos + 1) . '.php';
			}
			else
			{
				$object_path = $class_name . '.php';
			}
			
			if (is_readable($object_path))
			{
				require_once($object_path);
			}
			else
			{
				return m2f::raise_error("Can't load class file '$object_path' to create a $class_name object", __LINE__, __FILE__);
			}
			
			if (!class_exists($class_name))
			{
				return m2f::raise_error("Can't instantiate class to create a $class_name object", __LINE__, __FILE__);
			}
		}
		$ret =& new $class_name;
		return $ret;
	}
}




######################################################
#                                                    #
#                     Message                        #
#                                                    #
######################################################

class m2f_generic_message
{

	var $subject = '';
	var $body = '';
	var $html_body = '';
	var $signature = '';
	var $author = '';
	var $author_email = '';
	var $id = '';

	/** 
	* Get an object property 
	*  
	* @return mixed propery
	* @access public 
	* @param mixed $property the property
	*/
	function get($property) 
	{
		if (isset($this->$property)) 
		{
			return $this->$property;
		}
	}
	
	/** 
	* Store an object property 
	*  
	* @return 
	* @access public 
	* @param mixed $property the property
	* @param mixed $value value to set property to
	*/
	function set($property, $value) 
	{
		$this->$property = $value;
	}
}








?>