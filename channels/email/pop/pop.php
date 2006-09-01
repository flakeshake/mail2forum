<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



require_once('channels/email/email.common.php');

class m2f_channels_email_pop extends m2f_channels_email_common
{

	/** 
	* Constructor
	*  
	* @return null
	* @access public 
	*/
	function m2f_channels_email_pop()
	{
		parent::m2f_element();
		include_once ('Net/POP3.php');
	  $this->pop =& new Net_POP3();
	}

	/** 
	* Run import routine
	*  
	* @return mixed array of messages or error object
	* @access private 
	*/
	function _import()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'importing_pop', __LINE__, __FILE__); 

		if (m2f::is_error($error = $this->_login())) return $error;

		if (m2f::is_error($num_messages = $this->_get_num_messages())) return $num_messages;
    
		for ($i=1; $i<=$num_messages; $i++)
		{
			if (m2f::is_error($message = $this->_get_message($i))) return $message;
			if (m2f::is_error($message =& $this->_transform($message))) return $message;
			$this->_messages[] = $message;
		}
	}


	/** 
	* Runs PEAR login procedure
	*  
	* @return boolean
	* @access private 
	*/
	function _login()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'pop_login', __LINE__, __FILE__); 

		if (!$this->pop->connect($this->host, $this->port))
		{
			return m2f::raise_error('Cannot connect to the POP3 server at "' . $this->host . '", port "' . $this->port . '".', __LINE__, __FILE__);
		}

	  $login = $this->pop->login($this->user, 
														$this->pass , 
														@$this->apop == '1' ? 'APOP' : 'USER'
													);
	  if (m2f::is_error($login))
		{
			return m2f::raise_error('Cannot login to the POP3 server with username "' . $this->user . '". Error returned was "' . $login->getMessage() . '".', __LINE__, __FILE__);
		}

	  return TRUE;
	}


	/** 
	* Count messages in Inbox
	*  
	* @return mixed integer or FALSE on error
	* @access private 
	*/
	function _get_num_messages()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'pop_get_num_msgs', __LINE__, __FILE__); 
		
		if (($msg_num = $this->pop->numMsg()) === FALSE)
		{
			return m2f::raise_error('Could not get number of messages in POP3 mailbox.', __LINE__, __FILE__);
		}

		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'pop_got_num_msgs', __LINE__, __FILE__, $msg_num, $msg_num); 
		return $msg_num;
	}
	

	/** 
	* Get a specified message
	*  
	* @return string raw message 
	* @access private 
	*/
	function &_get_message($msg_num)
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'pop_get_message', __LINE__, __FILE__, $msg_num); 
		
		if (($message =& $this->pop->getMsg($msg_num)) === false) 
		{
			return m2f::raise_error('Could not get message #' . $msg_num . ' from POP3 mailbox.', __LINE__, __FILE__);
		}
		return $message;
	}
	
	
	/** 
	* Delete all the imported messages
	*  
	* @param int $num number of messages to delete
	* @return boolean
	* @access private 
	*/
	function _delete($num)
	{
		for ($i=1; $i<=$num; $i++)
		{
			if (m2f::is_error($error = $this->_delete_message($i))) return $error;
		}
		return TRUE;
	}
	
	/** 
	* Close the connectioin
	*  
	* @return boolean
	* @access public 
	*/
	function clean_up()
	{
		if (m2f::is_error($error = $this->_disconnect())) return $error;
		return TRUE;
	}
	
	/** 
	* Delete a specified message
	*  
	* @param int $msg_num message ID# to delete
	* @return boolean
	* @access private 
	*/
	function _delete_message($msg_num)
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'pop_delete_msg', __LINE__, __FILE__, $msg_num); 
		
		if ($this->pop->deleteMsg($msg_num) === false) 
	  {
			return m2f::raise_error('Could not delete message #' . $msg_num . ' from POP3 mailbox.', __LINE__, __FILE__);
	  }
	  return TRUE;
	}
	
	
	/** 
	* Runs PEAR disconnect procedure
	*  
	* @return boolean
	* @access private 
	*/
	function _disconnect()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'pop_disconnect', __LINE__, __FILE__); 
		if ($this->pop->disconnect() === false) 
	  {
			return m2f::raise_error('Could not disconnect from POP3 server.', __LINE__, __FILE__);
	  }
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
		return TRUE;
	}
}

class m2f_channels_email_pop_mapper extends m2f_channels_base_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channels_email_pop (id, direction) VALUES (?, ?)';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channels_email_pop WHERE id=?';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channels_email_pop SET direction=? WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channels_email_pop WHERE id=?';
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