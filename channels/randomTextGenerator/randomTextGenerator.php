<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class m2f_channels_randomTextGenerator extends m2f_channel
{


	/** 
	* A simulation of the expected Import function for a channel
	*  
	* @return object Generic Message object, with
	* 	body set to 20 randomly-generated lowercase 'words' (1-5 letters)
	* @access private 
	*/
	function _import()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'importing_text', __LINE__, __FILE__); 
		
		$letters = 'abcdefghijklmnopqrstuvwxyz';
		for ($i = 0; $i < 20; $i++)
		{
			$num_letters = rand(1, 5);
			$word = '';
			for ($j = 0; $j < $num_letters; $j++)
			{
				$letter_key = rand(0, 25);
				$word .= $letters[$letter_key];
			}
			$words[] = $word;
		}
		
		$message =& new m2f_generic_message;
		$message->body = implode(' ', $words);

		$this->_messages[] =& $message;
		
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'generated_text', __LINE__, __FILE__); 
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
}


class m2f_channels_randomTextGenerator_mapper extends m2f_channels_base_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channels_randomTextGenerator (id, direction, useless_param) VALUES (?, ?, ?)';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channels_randomTextGenerator WHERE id=?';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channels_randomTextGenerator SET direction=?, useless_param=? WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channels_randomTextGenerator WHERE id=?';
	}

	function _do_insert(&$object)
	{
		$this->_do_statement($this->_insert_sql, array($object->id, $object->direction, $object->config['useless_param']));
	}
	
	function _do_update(&$object)
	{
		$this->_do_statement($this->_update_sql, array($object->direction, $object->config['useless_param'], $object->id));
	}
	
}

?>