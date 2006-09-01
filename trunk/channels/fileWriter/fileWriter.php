<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class m2f_channels_fileWriter extends m2f_channel
{
	
	/**
	 * Resource handle for the file
	 * @access private
	 * @var resource
	 */
	var $_handle;

	/** 
	* Write to a file
	*  
	* @return mixed TRUE or error object
	* @access private 
	*/
	function _export()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'exporting_fileWriter', __LINE__, __FILE__); 

		if (m2f::is_error($error = $this->_open_file())) return $error;
		
		foreach (array_keys($this->_messages) as $message_id)
		{
			$message =& $this->_messages[$message_id];
			if (fwrite($this->handle, $message->body) === FALSE) 
			{
				return m2f::raise_error('Failed to write to the file "' . $this->config['filepath'] . '"', __LINE__, __FILE__);
			}
		}
		fclose($this->handle);
		
		$num = count($this->_messages);
		
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'wrote_file', __LINE__, __FILE__, $num, $num); 
		return TRUE;
	}

	function _open_file()
	{
		if (!@touch($this->config['filepath']) || !@is_writable($this->config['filepath']) || !@is_file($this->config['filepath'])) 
		{
			return m2f::raise_error('"' . $this->config['filepath'] . '" cannot be accessed. Check file path and permissions.', __LINE__, __FILE__);
		}

		if (!$this->handle = fopen($this->config['filepath'], 'a')) 
		{
			return m2f::raise_error('Cannot open the file "' . $this->config['filepath'] . '" for writing', __LINE__, __FILE__);
		}
	}

	/** 
	* Test config options before channel is saved in DB
	*  
	* @return mixed TRUE or error string
	* @access private 
	*/
	function _test_config()
	{
		if (m2f::is_error($this->_open_file())) return FALSE;
		fclose($this->handle);
		return TRUE;
	}
	
}



class m2f_channels_fileWriter_mapper extends m2f_channels_base_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channels_fileWriter (id, filepath, direction) VALUES (?, ?, ?)';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channels_fileWriter WHERE id=?';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channels_fileWriter SET filepath=?, direction=? WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channels_fileWriter WHERE id=?';
	}

	function _do_insert(&$object)
	{
		$this->_do_statement($this->_insert_sql, array($object->id, $object->config['filepath'], $object->direction));
	}
	
	function _do_update(&$object)
	{
		$this->_do_statement($this->_update_sql, array($object->config['filepath'], $object->direction, $object->id));
	}
	
}

?>