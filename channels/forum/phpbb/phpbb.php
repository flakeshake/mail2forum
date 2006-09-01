<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



require_once('channels/forum/forum.common.php');

class m2f_channels_forum_phpbb extends m2f_channels_forum_common
{
	
	var $topic_id = NULL;
	var $_root_path_ok = FALSE;
	var $_helper_loaded = FALSE;
	

	/** 
	* Run import routine - receives a phpBB forum post, transforms into a GM
	*  
	* @return mixed array of messages or error object
	* @access private 
	*/
	function _import()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'importing', __LINE__, __FILE__); 

		$message =& new m2f_generic_message;
		
		$this->allowed_html_tags = split(',', $this->board_config['allow_html_tags']);

		$this->_strip_bbcode_uid();
		$this->_set_subject($message);
		$this->_set_text_body($message);
		$this->_set_html_body($message);
		
		$this->_messages[] =& $message;
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'imported_post', __LINE__, __FILE__); 
	}


	function _set_subject(&$message)
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'setting_subject', __LINE__, __FILE__); 
		$message->subject =& $this->forum_post['post_subject'];

		$this->_unprepare_db_entities($message->subject);
		$this->_unprepare_db_escape($message->subject);
		
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'set_subject', __LINE__, __FILE__, $message->subject); 
	}

	function _unprepare_db_escape(&$text)
	{
		$text = str_replace("''", "'", stripslashes($text));
	}
	
	function _unprepare_db_entities(&$text)
	{
		static $unhtml_specialchars_match = array('#&gt;#', '#&lt;#', '#&quot;#', '#&amp;#');
		static $unhtml_specialchars_replace = array('>', '<', '"', '&');
		$text = preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, $text);
	}

	function _set_html_body(&$message)
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'setting_html_body', __LINE__, __FILE__); 
		$message->html_body = $this->forum_post['body'];

		$this->_unprepare_db_escape($message->html_body);

		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'set_html_body', __LINE__, __FILE__, $message->html_body); 
	}
	
	function _set_text_body(&$message)
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'setting_text_body', __LINE__, __FILE__); 
		$message->body = $this->forum_post['body'];
		
		$this->_unprepare_db_entities($message->body);
		$this->_unprepare_db_escape($message->body);

		if ($this->forum_post['html_on'])
		{
			foreach ($this->allowed_html_tags as $tag)
			{
				$message->body = preg_replace('#</?' . $tag . '( [^>]*)?>#', '', $message->body);
			}
		}
		
		$message->body = preg_replace('#&lt;([^&]+)&gt;#', '<\1>', $message->body);
		
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'set_text_body', __LINE__, __FILE__, $message->body); 
	}
	
	function _strip_bbcode_uid()
	{
		$expr = '\[																		# opening bracket
						(\/?[a-z*]+)													# \1 - tag, with optional "/"
						(=[a-z0-9]+)?													# \2 - optional "=1" or "=a"
						(:[a-z0-9]+)?													# \3 - optional ":u" or similar
						:'.$this->forum_post['bbcode_uid'].'	# ":" symbol followed by BBCode string
						(=[^\]]+)?														# \4 - optional stuff after BBCode
						\]																		# closing tag';
	
		$this->forum_post['body'] = preg_replace("!$expr!xi", '[\1\2\4]', $this->forum_post['body']);
	}
	
	/** 
	* Delete pocessed messages.
	*  
	* @param int number of messages to delete
	* @return bool TRUE
	* @access private 
	*/
	function _delete($num)
	{
		return TRUE;
	}





	/** 
	* Posts a message as a phpBB forum post
	*  
	* @return mixed success or error object
	* @access private 
	*/
	function _export()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'exporting', __LINE__, __FILE__); 
		
		if (!$this->_root_path_ok && m2f::is_error($error = $this->_check_phpBB_root_path())) return $error;
	
		if (!$this->_helper_loaded && m2f::is_error($error = $this->_phpbb_post_helper())) return $error;
		
		foreach (array_keys($this->_messages) as $message_id)
		{
			$message =& $this->_messages[$message_id];
			
			$post_details = $this->post_helper->insert(	
												$message->body, 
												$message->subject, 
												$this->config['forum_id'], 
												$this->config['user_id'], 
												$this->config['user_name'], 
												$this->topic_id,
												$this->config['user_attach_sig']
											);

			if (m2f::is_error($post_details)) return $post_details;
		}
		
		$num = count($this->_messages);		
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'posted_messages', __LINE__, __FILE__, $num, $num); 

		return TRUE;
	}
	
	
	/** 
	* Checks the root path for the phpBB installation is valid
	*  
	* @return mixed success or error object
	* @access private 
	*/
	function _check_phpBB_root_path()
	{
		if (empty($this->config['phpbb_root'])) return m2f::raise_error('No phpBB root path given.', __LINE__, __FILE__);
		if (!is_dir($this->config['phpbb_root'])) return m2f::raise_error('Invalid phpBB root path given: "' . $this->config['phpbb_root'] . '".', __LINE__, __FILE__);
		$this->config['phpbb_root'] = @realpath($this->config['phpbb_root']) . '/';
		if (!is_readable($this->config['phpbb_root'] . 'extension.inc')) return m2f::raise_error('No valid phpBB installation found at path "' . $this->config['phpbb_root'] . '".', __LINE__, __FILE__);
		
		$this->_root_path_ok = TRUE;
		return TRUE;
	}
	

	/** 
	* Instantiates the Insert Post class. It is important that the core phpBB files are
	*   only ever loaded and run once, so we use a singleton here.
	*  
	* @return boolean success or error object
	* @access private 
	*/
	function _phpbb_post_helper()
	{
		include_once('inc/phpbb_insert_post.php');
		$this->post_helper =& new m2f_phpBB_insert_post($this->config['phpbb_root']);
		if (!$this->post_helper->_loaded && m2f::is_error($error = $this->post_helper->load())) return $error;

		$this->_helper_loaded = TRUE;
		return TRUE;
	}



	/** 
	* Test config options before channel is saved in DB
	*  
	* @return mixed TRUE or error string
	* @access private 
	*/
	function _test_config()
	{
		if (m2f::is_error($this->_check_phpBB_root_path())) return FALSE;
		if (m2f::is_error($post_helper =& $this->_phpbb_post_helper())) return FALSE;
		if (m2f::is_error($post_helper->check_forum_id($this->config['forum_id']))) return FALSE;
		return TRUE;
	}


}





class m2f_channels_forum_phpbb_mapper extends m2f_channels_base_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channels_forum_phpbb (id, direction, phpbb_root, forum_id) VALUES (?, ?, ?, ?)';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channels_forum_phpbb SET direction=?, phpbb_root=?, forum_id=? WHERE id=?';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channels_forum_phpbb WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channels_forum_phpbb WHERE id=?';
	}

	function _do_insert(&$object)
	{
		$this->_do_statement($this->_insert_sql, array($object->id, $object->direction, $object->config['phpbb_root'], $object->config['forum_id']));
	}
	
	function _do_update(&$object)
	{
		$this->_do_statement($this->_update_sql, array($object->direction, $object->config['phpbb_root'], $object->config['forum_id'], $object->id));
	}
	
}


?>