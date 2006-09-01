<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



######################################################
#                                                    #
#                     Channel                        #
#                                                    #
######################################################

class m2f_channel extends m2f_element
{

	/**
	 * Array of generic message objects
	 * @access private
	 * @var array
	 */
	var $_messages = array();

	/**
	 * Array of channel-specific configuration parameters
	 * @access public
	 * @var array
	 */
	var $config = array();

	/**
	 * Direction this channel will operate in
	 * @access public
	 * @var string
	 */
	var $direction;

	/**
	 * Delete messages? 
	 * @access public
	 * @var boolean
	 */
	var $delete_msgs = FALSE;

	/**
	 * Number of messages to delete 
	 * @access public
	 * @var int
	 */
	var $max_msgs;
	
	/**
	 * Number of messages to delete during clean_up()
	 * @access private
	 * @var int
	 */
	var $_num_msgs_to_delete = 0;
	
	/**
	 * Has the channel started importing/exporting?
	 * @access public
	 * @var bool
	 */
	var $connection_open = FALSE;

	/**
	 * List of channel parameters, as distinct from channel config variables
	 * @access public
	 * @var int
	 */
	var $channel_params = array('id', 'direction', 'max_msgs', 'delete_msgs');

	/**
	* Bit mask flag of channel properties
	* @access public
	* @var int
	*/
	var $properties;
	
	
	function m2f_channel()
	{
		$this->_set_class_name();
		$this->_set_properties();
	}

	function _set_properties()
	{
		$methods = get_class_methods($this);
		$this->properties = 0;
		if (in_array('_import', $methods)) $this->properties += M2F_CHANNEL_CAN_IMPORT;
		if (in_array('_export', $methods)) $this->properties += M2F_CHANNEL_CAN_EXPORT;
	}

	function count_messages()
	{
		return count($this->_messages);
	}

	/** 
	* Generates a message ID for a message
	*  
	* @return string message ID
	* @access private 
	*/
	function _generate_message_id()
	{
		return (string) time();
	}
	
	/** 
	* Adds a message to the channel's message store
	*  
	* @return boolean success?
	* @access public 
	* @param object $message the message
	*/
	function add_message(&$message)
	{
		$this->_messages[] =& $message;
		return TRUE;
	}
	
	/** 
	* Adds an array of messages to the channel's message store
	*  
	* @return boolean success?
	* @access public 
	* @param array $array the message array
	*/
	function add_messages(&$array)
	{
		foreach (array_keys($array) as $id)
		{
			$this->add_message($array[$id]);
		}
		return TRUE;
	}

	/** 
	* Base function to find message id - should be replaced in most inherited classes
	*  
	* @param object $message
	* @return mixed message ID or FALSE
	* @access private 
	*/
	function _get_message_id(&$message)
	{
		return FALSE;
	}

	/** 
	* Takes control of the private _import() function for a channel.
	*  N.B. that if a channel class defines an _import() function, it is considered a valid channel
	*  on which to perform an import. So only define _import() if you plan to use it!
	*  
	* @return mixed array of message objects, a single mesage object, or an error object
	* @access public 
	*/
	function &import()
	{
		m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'importing_channel', __LINE__, __FILE__); 
		if (m2f::is_error($error = $this->_import())) return $error;
		
		if (!is_array($this->_messages)) $this->_messages = array($this->_messages);
		foreach (array_keys($this->_messages) as $key)
		{
			$message =& $this->_messages[$key];
			if (empty($message->id)) $message->id = $this->_generate_message_id();
		}
		
		if ($this->delete_msgs === TRUE) $this->_num_msgs_to_delete = count($this->_messages);

		return $this->_messages;
	}


	/** 
	* Takes control of the private _export() function for a channel.
	*  N.B. that if a channel class defines an _export() function, it is considered a valid channel
	*  on which to perform an export. So only define _export() if you plan to use it!
	*  
	* @return boolean success?
	* @access public 
	*/
	function export()
	{
		m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'exporting_channel', __LINE__, __FILE__);
		
		$ret = $this->_export();
		$this->clean_up();
		return $ret;
	}
	
	
	/** 
	* Runs the delete messages routine on the descendant channel
	*  
	* @return boolean success?
	* @access public 
	*/
	function delete_msgs()
	{
		if ($this->direction == 'in' && $this->delete_msgs)
		{
			$this->_delete($this->_num_msgs_to_delete);
		}
		return TRUE;
	}

	/** 
	* Base function - to be overridden in any import channels 
	*   where clean up is applicable - closing connections, etc
	*  
	* @return boolean success?
	* @access public 
	*/
	function clean_up()
	{
		return TRUE;
	}
	
	/** 
	* Runs _test_config() in descendant channels. Used to validate config settings before a channel is saved in the DB.
	*  
	* @return mixed TRUE on success or error string
	* @access public 
	*/
	function test_config()
	{
		if (!method_exists($this, '_test_config')) return 'Can\'t find ' . $this->class . '::_test_config(). This method MUST be defined.';         
		return $this->_test_config();
	}
}


class m2f_channel_mapper extends m2f_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channel (id, path, class, properties, channel_fields) VALUES (?, ?, ?, ?, ?)';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channel SET path=?, class=?, channel_fields=? WHERE id=?';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channel WHERE id=?';
		$this->_select_all_sql = 'SELECT id FROM ' . $this->_prefix . '_channel ORDER BY class';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channel WHERE id=?';
	}

	function _do_insert(&$object)
	{
		$this->_do_statement($this->_insert_sql, array($object->id, $object->path, $object->class, $object->properties, $object->channel_fields));
	}
	
	function _do_update(&$object)
	{
		$this->_do_statement($this->_update_sql, array($object->path, $object->class, $object->channel_fields, $object->id));
	}
	
}


?>