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
#                     Chain                          #
#                                                    #
######################################################

class m2f_chain extends m2f_element
{

	/**
	 * Array of all the elements in the chain - each labelled as 'chain', 'channel', 'router', 'filter', etc
	 * @access private
	 * @var array
	 */
	var $elements = array();

	/**
	 * Array of all the messages owned by this chain
	 * @access private
	 * @var array
	 */
	var $_messages = array();
	
//	/**
//	 * Name of this chain (as stored in database - might not be necessary in the end)
//	 * @access public
//	 * @var string
//	 */
//	var $name;
//
//	/**
//	 * Description of the chain (as stored in database - might not be necessary in the end)
//	 * @access public
//	 * @var string
//	 */
//	var $description;
	

	/** 
	* Adds a message to the chain's message store
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
	* Adds a new element to the chain
	*  
	* @access public 
	* @param object $element the element
	* @param string $direction Applies to channels only - is it input or output?
	* @return boolean success?
	*/
	function add_element(&$element, $direction = NULL)
	{
		if ($element->type == 'channel')
		{
			if (!$direction) return m2f::raise_error('Can\'t add a channel to the chain with no direction parameter.', __LINE__, __FILE__);
			switch ($direction)
			{
				case 'in':
					if (!($element->properties & M2F_CHANNEL_CAN_IMPORT)) return m2f::raise_error($element->class . ' has no _import() function. It cannot be added as an import channel.', __LINE__, __FILE__);
					break;
				case 'out':
					if (!($element->properties & M2F_CHANNEL_CAN_EXPORT)) return m2f::raise_error($element->class . ' has no _export() function. It cannot be added as an export channel.', __LINE__, __FILE__);
					break;
			}
			$element->direction = $direction;
		}
		$this->elements[] =& $element;
		return TRUE;
	}


	/** 
	* Run a channel - import or export
	*  
	* @access private 
	* @param object $channel the channel
	* @return boolean true!
	*/
	function _run_channel(&$channel)
	{
		switch ($channel->direction)
		{
			case 'in':
				$new_msgs =& $channel->import(FALSE);

				if ($new_msgs)
				{
					$this->_messages = array_merge($this->_messages, $new_msgs);
				}
				else
				{
					$channel->clean_up();
				}
				break;
				
			case 'out':
				if (empty($this->_messages)) break;
				$num = count($this->_messages);
				m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'adding_messages_channel', __LINE__, __FILE__, array($num, str_replace('channels_', '', $channel->class)), $num); 
				$channel->add_messages($this->_messages);
				break;
		}
		return TRUE;
	}


	/** 
	* Run a filter - assign it a message and set the filter process going to transform the message
	*  
	* @access private 
	* @param object $filter the filter
	* @return boolean true!
	*/
	function _run_filter(&$filter)
	{
		m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'running_filter', __LINE__, __FILE__); 
		foreach (array_keys($this->_messages) as $msg_id)
		{
			$filter->message =& $this->_messages[$msg_id];
			$filter->filter();
		}
		return TRUE;
	}
	

	/** 
	* Run a router - find the options, test each message and send each to the applicable element for further processing
	*  
	* @access private 
	* @param object $router the router
	* @return mixed error object or TRUE!
	*/
	function _run_router(&$router)
	{
		if (!$router->options) 
		{
			return m2f::raise_error('The router contained no options.', __LINE__, __FILE__);
		}
		
		m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'running_router', __LINE__, __FILE__); 

		foreach ($this->_messages as $msg_id => $message)
		{
			$router->message =& $message;
			
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'routing_msg', __LINE__, __FILE__, $msg_id+1); 

			$result = $router->route();
			foreach (array_keys($router->options) as $opt_id)
			{
				$option =& $router->options[$opt_id];
				if ($option->condition === $result)
				{
					m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'router_passed', __LINE__, __FILE__, $option->condition); 
					switch ($option->type)
					{
						case 'channel':
							$option->add_message($router->message);
							break;
							
						case 'router':
							$this->_run_router($option);
							break;
							
						case 'chain':
							$option->add_message($router->message);
							$option->run(FALSE);
							break;
					}
				}
			}
		}
		return TRUE;
	}
	
	
	/** 
	* Delete the necessary messages and closes connections on an import channel (run after the whole chain has finished importing and exporting)
	*  
	* @param array $elements the elements to work on (recursive function!)
	* @access private 
	* @return boolean true!
	*/
	
	function do_clean_up($elements = NULL)
	{
		if (!$elements)
		{
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'clean_up', __LINE__, __FILE__); 
			$elements =& $this->elements;
		}
		
		foreach (array_keys($elements) as $element_id)
		{
			$element =& $elements[$element_id];
			switch ($element->type)
			{
				case 'channel':
					$element->delete_msgs();
					$element->clean_up();
					break;
					
				case 'router':
					$this->do_clean_up($element->options);
					break;
				
				case 'chain':
					$element->do_clean_up();
					break;
			}
		}
		
		return TRUE;
	}
	

	/** 
	* Export the messages waiting in each output channel's $_messages array. 
	* We only do this after all import channels have finished importing, in case there are errors on the import
	* (better to not export anything than to export corrupted messages!)
	*  
	* @access private 
	* @param object $element element to export - NULL is a master export for the whole chain
	* @return mixed true or error
	*/
	function _export($element)
	{
		if ($element === NULL)
		{
			foreach (array_keys($this->elements) as $element_id)
			{
				$this->_export($this->elements[$element_id]);
			}
		}
		else
		{
			if ($element->type == 'channel' && $element->direction == 'out')
			{
				if ($element->count_messages())
				{
					if (m2f::is_error($error = $element->export())) return $error;
				}
			}		
			else if ($element->type == 'router')
			{
				foreach (array_keys($element->options) as $option_id)
				{
					$option =& $element->options[$option_id];
					switch ($option->type)
					{
						case 'chain':
							foreach (array_keys($option->elements) as $chain_element_id)
							{
								$chain_element =& $option->elements[$chain_element_id];
								$this->_export($chain_element);
							}
							break;
							
						case 'router':
							foreach (array_keys($option->options) as $router_option_id)
							{
								$router_option =& $option->options[$router_option_id];
								if (!$router_option->count_messages()) continue;
								$this->_export($router_option);
							}
							break;
							
						case 'channel':
							if (!$option->count_messages()) break;
							if (m2f::is_error($error = $option->export())) return $error;
							break;
					}
				}
			}
		}
		return TRUE;
	}


	/** 
	* Sets the chain into motion 
	*  
	* @param boolean $final Is this the 'outermost' chain? If so, run the exports and clean up
	* @return boolean success?
	* @access public 
	*/
	function run($final=TRUE)
	{
		if (!empty($this->id))
		{
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'running_chain_num', __LINE__, __FILE__, $this->id); 
		}
		else
		{
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'running_chain', __LINE__, __FILE__); 
		}

		if (empty($this->elements)) 
		{
			return m2f::raise_error('The chain contained no elements.', __LINE__, __FILE__);
		}
		
		foreach (array_keys($this->elements) as $element_id)
		{
			$element =& $this->elements[$element_id];

			switch ($element->type)
			{
				case 'channel':
					$this->_run_channel($element);
					break;
					
				case 'filter':
					if (!empty($this->_messages)) $this->_run_filter($element);
					break;
				
				case 'router':
					if (!empty($this->_messages)) $this->_run_router($element);
					break;
					
				default:
					return m2f::raise_error('Invalid element type: "' . $element->type . '".', __LINE__, __FILE__);
					break;
			}
		}
		
		if ($final)
		{
			$this->_export(NULL);
			$this->do_clean_up();
		}
		
		return TRUE;
	}
}





class m2f_chain_mapper extends m2f_mapper
{
	
	/** 
	* Set up db schema constants
	*  
	* @access private 
	*/
	function _setup()
	{
		$this->chain_elem_seq = $this->_prefix . '_seq_chain_elements';
		
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_chain (id, name, description) VALUES (?, ?, ?)';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_chain WHERE id=?';
		$this->_select_all_sql = 'SELECT id FROM ' . $this->_prefix . '_chain';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_chain SET name=?, description=? WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_chain WHERE id=?';

		$this->_insert_element_sql = 'INSERT INTO ' . $this->_prefix . '_chain_elements (id, chain_id, element_id, element_order, element_type) VALUES (?, ?, ?, ?, ?)';
		$this->_select_elements_sql = 'SELECT * FROM ' . $this->_prefix . '_chain_elements WHERE chain_id=? ORDER BY element_order';
		$this->_delete_elements_sql = 'DELETE FROM ' . $this->_prefix . '_chain_elements WHERE chain_id=?';
	}

	/** 
	* Insert a chain into the database
	*  
	* @access private 
	* @param object $chain chain to insert
	*/
	function _do_insert(&$chain)
	{
		$this->_do_statement($this->_insert_sql, array($chain->id, $chain->name, $chain->description));
	}
	
	/** 
	* Update a chain in the database
	*  
	* @access private 
	* @param object $chain chain to update
	*/
	function _do_update(&$chain)
	{
		$this->_do_statement($this->_update_sql, array($chain->name, $chain->description, $chain->id));
	}
	
	/** 
	* Add the elements of the chain into the database
	*  
	* @access private 
	* @param object $chain chain whose elements are to be inserted
	*/
	function _add_elements(&$chain)
	{
		if (!empty($chain->elements))
		{
			$num = count($chain->elements);
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'saving_chain_elements', __LINE__, __FILE__, $num, $num); 
			$i = 1;
			foreach (array_keys($chain->elements) as $element_id)
			{
				$element =& $chain->elements[$element_id];
				$name = str_replace('m2f_', '', strtolower(get_class($element)));
				$mapper =& m2f_factory::make_object($name, TRUE);
				$mapper->insert($element);
				
				$id = $this->_get_id($this->chain_elem_seq);
				$this->_do_statement($this->_insert_element_sql, array($id, $chain->id, $element->id, $i++, $name));
			}
		}
	}
	
	/** 
	* Update the elements of the chain in the database
	*  
	* @access private 
	* @param object $chain chain whose elements are to be updated
	*/
	function _update_elements(&$chain)
	{
		$this->_delete_elements($chain->id);
		
		if (!empty($chain->elements))
		{
			foreach (array_keys($chain->elements) as $element_id)
			{
				$element =& $chain->elements[$element_id];
				$element->id = NULL;
			}
			
			$this->_add_elements($chain);
		}
	}

	/** 
	* Delete the elements of the chain in the database
	*  
	* @access private 
	* @param int $id ID of chain whose elements are to be deleted
	*/
	function _delete_elements($id)
	{
		$result = $this->_do_statement($this->_select_elements_sql, $id);
		$elements =& $result->GetRows();
		if ($elements)
		{
			$num = count($elements);
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'deleting_chain_elements', __LINE__, __FILE__, $num, $num); 
			foreach ($elements as $element_data)
			{
				$mapper =& m2f_factory::make_object($element_data['element_type'], TRUE);
				$mapper->delete($element_data['element_id']);
			}
		}
		
		$this->_do_statement($this->_delete_elements_sql, $id);
	}

	/** 
	* Load the elements of the chain from the database, adding them to the chain's $_elements array.
	*  
	* @access private 
	* @param object $chain chain whose elements are to be loaded
	*/
	function _load_elements(&$chain)
	{
		$result = $this->_do_statement($this->_select_elements_sql, array($chain->id));
		$array =& $result->GetRows();
		if ($array)
		{
			foreach ($array as $element_data)
			{
				$mapper =& m2f_factory::make_object($element_data['element_type'], TRUE);
				$element =& $mapper->get($element_data['element_id']);
				$chain->add_element($element, $element->direction);
			}
		}
	}

}

?>