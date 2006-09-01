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

class m2f_channels_email_mbox extends m2f_channels_email_common
{

	/**
	 * Resource handle for the mbox file
	 * @access private
	 * @var resource
	 */
	var $_handle;

	
	/**
	 * Array of the start and end byte positions for each message
	 * @access private
	 * @var array
	 */
	var $_message_locs = array();
	

	/** 
	* Run import routine
	*  
	* @return mixed array of messages or error object
	* @access private 
	*/
	function _import()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'importing_mbox', __LINE__, __FILE__); 
		
		if (m2f::is_error($error = $this->_check_file())) return $error;
		if (m2f::is_error($error = $this->_open())) return $error;
		if (m2f::is_error($error = $this->_retrieve())) return $error;

		foreach ($this->_messages as $id => $message)
		{
			if (m2f::is_error($transformed =& $this->_transform($message))) return $transformed;
			$this->_messages[$id] =& $transformed;
		}
	}


	/** 
	* Check mbox file
	*  
	* @return mixed TRUE or error object
	* @access private 
	*/
	function _check_file()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'mbox_check_file', __LINE__, __FILE__); 

		if (!is_readable($this->config['path']) || !is_file($this->config['path']))
		{
			return m2f::raise_error('Cannot open the mbox file "' . $this->config['path'] . '". Check file path and permissions.', __LINE__, __FILE__);
		}

		return TRUE;
	}

	/** 
	* Open mbox file
	*  
	* @return mixed TRUE or error object
	* @access private 
	*/
	function _open()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'mbox_opening', __LINE__, __FILE__); 

		$this->_handle = @fopen($this->config['path'], "r");
		
		if (!is_resource($this->_handle)) 
		{
			return m2f::raise_error('Cannot open the mbox file "' . $this->config['path'] . '" for reading.', __LINE__, __FILE__);
		}
		
		return TRUE;
	}
	

	/** 
	* Scan mbox file for messages. Set start and end pos of each in $this->_message_locs. Add each message to $this->_messages
	*  
	* @return mixed TRUE or error object
	* @access private 
	*/
	function _retrieve()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'mbox_retrieving', __LINE__, __FILE__); 

		if (@fseek($this->_handle, 0) == -1) 
		{
			return m2f::raise_error("Cannot read mbox", __LINE__, __FILE__);
		}
		
		$start       				= 0;
		$laststart   				= 0;
		$hasmessage  				= FALSE;
		$numMessages 				= 0;
		
		while (($line = fgets($this->_handle)) && ( ($this->max_msgs == 0) || ($numMessages <= $this->max_msgs) )) 
		{
			if (0 === strncmp($line, 'From ', 5)) 
			{
				$numMessages++;
				$laststart = $start;
				
				$start = ftell($this->_handle) - strlen($line);
				
				if ($start > 0) 
				{
					// if $hasmessage is true at this stage, it means we've found a message but it wasn't on the first line - 
					// we have blank or non-mbox lines at the beginning of the file. So we don't record the message yet.
					if ($hasmessage === true) 
					{
						$this->_message_locs[] = array($laststart, $start - 1);
					} 
					else 
					{
						$hasmessage = true;
					}
				} 
				else 
				{
					$hasmessage = true;
				}
			}
		}
		
		// Must pick up the one remaining message if necessary
		if (($start == 0 && $hasmessage === true) || (($numMessages <= $this->max_msgs) && $start > 0) || ($this->max_msgs == 0) ) 
		{
			$this->_message_locs[] = array($start, ftell($this->_handle));
		}
		
		if ( ($this->max_msgs != 0) && isset($this->_message_locs[$this->max_msgs]) ) unset($this->_message_locs[$this->max_msgs]);
		
		if ($this->_message_locs === array()) return TRUE;
		
		foreach ($this->_message_locs as $msgLoc) 
		{
			$bytesStart = $msgLoc[0];
			$bytesEnd = $msgLoc[1];
			
			// seek to start of message
			fseek($this->_handle, $bytesStart);
			
			if ($bytesEnd - $bytesStart > 0) 
			{
				$this->_messages[] = fread($this->_handle, ($bytesEnd - $bytesStart)) . "\n";
			}
		}
		
		$num_msgs = count($this->_messages);
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'mbox_got_num_msgs', __LINE__, __FILE__, $num_msgs, $num_msgs); 

		return TRUE;
	}


	/** 
	* Delete pocessed messages. Writes new version of mbox to temp file
	*  
	* @param int number of messages to delete
	* @return mixed TRUE or error object
	* @access private 
	*/
	function _delete($num_to_delete)
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'mbox_deleting', __LINE__, __FILE__); 

		if (!is_resource($this->_handle)) 
		{
			return m2f::raise_error('Cannot delete messages: the mbox file has not been correctly opened.', __LINE__, __FILE__);
		}

		$umaskOld = umask(077);
		$ftempname = tempnam ("/tmp", rand(0, 9));
		umask($umaskOld);
		
		if (!$ftemp = fopen($ftempname, "w")) 
		{
			return m2f::raise_error("Cannot create a temp file.", __LINE__, __FILE__);            
		}
		
		$lastMsg = $this->_message_locs[$num_to_delete-1];
		$lastBytePos = $lastMsg[1];
		
		fseek($this->_handle, $lastBytePos+1);
		
		$toSave = fread($this->_handle, filesize($this->config['path']));
		fwrite($ftemp, $toSave);
		unset($toSave);
		
		fclose($this->_handle);
		fclose($ftemp);
		
		return $this->_move($ftempname, $this->config['path']);
	}


	/** 
	* Move new version of mbox from temp file to correct location
	*  
	* @return mixed TRUE or error object
	* @access private 
	*/
	function _move($ftempname, $filename) 
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'mbox_moving_temp_file', __LINE__, __FILE__); 

		if (!$ftemp = fopen($ftempname, "r")) 
		{
			return m2f::raise_error("Cannot open temp file.", __LINE__, __FILE__);
		}
		
		if (!$fp = @fopen($filename, "w")) 
		{
			return m2f::raise_error("Cannot write to mbox file.", __LINE__, __FILE__);
		}
		
		while (feof($ftemp) != true) 
		{
			$strings = fread($ftemp, 4096);
			if (fwrite($fp, $strings, strlen($strings)) === false) 
			{
				return m2f::raise_error("Cannot write to mbox file.", __LINE__, __FILE__);
			}
		}
		
		fclose($fp);
		fclose($ftemp);
		unlink($ftempname);
		
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
		if (m2f::is_error($error = $this->_check_file())) return FALSE;
		return TRUE;
	}
}




class m2f_channels_email_mbox_mapper extends m2f_channels_base_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channels_email_mbox (id, path, max_msgs, delete_msgs, direction) VALUES (?, ?, ?, ?, ?)';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channels_email_mbox WHERE id=?';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channels_email_mbox SET path=?, max_msgs=?, delete_msgs=?, direction=? WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channels_email_mbox WHERE id=?';
	}

	function _do_insert(&$object)
	{
		$this->_do_statement($this->_insert_sql, array($object->id, $object->config['path'], $object->max_msgs, $object->delete_msgs, $object->direction));
	}
	
	function _do_update(&$object)
	{
		$this->_do_statement($this->_update_sql, array($object->config['path'], $object->max_msgs, $object->delete_msgs, $object->direction, $object->id));
	}
	
}

?>