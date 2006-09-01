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

class m2f_channels_forum_phpbb3 extends m2f_channels_forum_common
{

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

//		$this->_unprepare_db_entities($message->subject);
//		$this->_unprepare_db_escape($message->subject);
		
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'set_subject', __LINE__, __FILE__, $message->subject); 
	}

//	function _unprepare_db_escape(&$text)
//	{
//		$text = str_replace("''", "'", stripslashes($text));
//	}
//	
//	function _unprepare_db_entities(&$text)
//	{
//		static $unhtml_specialchars_match = array('#&gt;#', '#&lt;#', '#&quot;#', '#&amp;#');
//		static $unhtml_specialchars_replace = array('>', '<', '"', '&');
//		$text = preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, $text);
//	}

	function _set_html_body(&$message)
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'setting_html_body', __LINE__, __FILE__); 
		$message->html_body = $this->forum_post['body'];

//		$this->_unprepare_db_escape($message->html_body);

		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'set_html_body', __LINE__, __FILE__, $message->html_body); 
	}
	
	function _set_text_body(&$message)
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'setting_text_body', __LINE__, __FILE__); 
		$message->body = $this->forum_post['body'];
		
//		$this->_unprepare_db_entities($message->body);
//		$this->_unprepare_db_escape($message->body);

/*
		if ($this->forum_post['html_on'])
		{
			foreach ($this->allowed_html_tags as $tag)
			{
				$message->body = preg_replace('#</?' . $tag . '( [^>]*)?>#', '', $message->body);
			}
		}
*/		
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
	* Test config options before channel is saved in DB
	*  
	* @return mixed TRUE or error string
	* @access private 
	*/
	function _test_config()
	{
		return TRUE;
	}
}


class m2f_channels_forum_phpbb3_mapper extends m2f_channels_base_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channels_forum_phpbb3 (id, direction) VALUES (?, ?)';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channels_forum_phpbb3 WHERE id=?';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channels_forum_phpbb3 SET direction=? WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channels_forum_phpbb3 WHERE id=?';
	}

	function _do_insert(&$object)
	{
		$this->_do_statement($this->_insert_sql, array($object->id, $object->direction));
	}
	
	function _do_update(&$object)
	{
		$this->_do_statement($this->_update_sql, array($object->direction, $object->id));
	}
	
}


?>