<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class m2f_mapper
{
	var $db;
	var $_prefix;
	var $_properties = array();
	
	function m2f_mapper()
	{
		$this->db =& m2f_db::get_instance();
		$this->_prefix = m2f_db::prefix();
		
		$mapper_class = strtolower(get_class($this));	
		$this->class = substr($mapper_class, 4, -7);
		$this->sequence_name = $this->_prefix . '_seq_' . $this->class;
		
		$this->_get_property_list();
		$this->_setup();
	}
	
	function _setup()
	{
		return m2f::raise_error('Function _setup() MUST be overwritten in descendent classes.', __LINE__, __FILE__);
	}
	
	function _get_property_list()
	{
		$sql = (strpos($this->class, 'channels_') !== FALSE)
						? 'SELECT channel_fields FROM ' . $this->_prefix . '_channel WHERE class=?'
						: 'SELECT element_fields FROM ' . $this->_prefix . '_element WHERE name=?';
		$fields = $this->db->GetOne($sql, array($this->class));
		if (empty($fields)) return m2f::raise_error('Can\'t get property list for database mapper class.', __LINE__, __FILE__);
		$this->_properties = unserialize($fields);
	}
	
	function &load($result)
	{
		$array =& $result->fetchRow();
		if (!is_array($array) || !$array['id']) 
		{
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'record_not_found', __LINE__, __FILE__); 
			$ret = NULL;
			return $ret;
		}

		m2f_log::log(M2F_LOG_DEBUG, 'm2f', 'getting_from_db', __LINE__, __FILE__, array($this->class, $array['id'])); 
		$object =& $this->_load_array($array);
		$this->_load_elements($object);
		return $object;
	}
	
	function &_load_array(&$data)
	{
		$obj =& m2f_factory::make_object($this->class);
		foreach ($this->_properties as $property)
		{
			$name = $property['name'];
			$type = $property['type'];
			if (isset($data[$name])) 
			{
				settype($data[$name], $type);
				$obj->$name = $data[$name];
			}
		}
		return $obj;
	}
	
	function &get($id)
	{
		$result =& $this->_do_statement($this->_select_sql, $id);
		return $this->load($result);
	}
	
	function &get_all()
	{
		$array = array();
		$result =& $this->_do_statement($this->_select_all_sql);
		foreach ($result->GetRows() as $record)
		{
			$array[] =& $this->get($record['id']);
		}
		return $array;
	}

	function _get_id($sequence)
	{
		return $this->db->genID($sequence); 
	}
	
	function delete($id)
	{
		m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'deleting_from_db', __LINE__, __FILE__, array($this->class, $id)); 
		$this->_do_statement($this->_delete_sql, $id);
		$this->_delete_elements($id);
	}
	
	function insert(&$object)
	{
		if (isset($object->id))
		{
			$this->update($object);
		}
		else
		{
			$object->id = $this->_get_id($this->sequence_name);
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'saving_in_db', __LINE__, __FILE__, array($this->class, $object->id)); 
			$this->_do_insert($object);
			$this->_add_elements($object);
		}
	}
	
	// override this if necessary
	function _delete_elements(&$object)
	{
		return TRUE;
	}

	// override this if necessary
	function _update_elements(&$object)
	{
		return TRUE;
	}

	// override this if necessary
	function _add_elements(&$object)
	{
		return TRUE;
	}

	// override this if necessary
	function _load_elements(&$object)
	{
		return TRUE;
	}
	
	function update(&$object)
	{
		if (empty($object->id))
		{
			$this->insert($object);
		}
		else
		{
			m2f_log::log(M2F_LOG_NORMAL, 'm2f', 'updating_in_db', __LINE__, __FILE__, array($this->class, $object->id)); 
			$this->_do_update($object);
			$this->_update_elements($object);
		}
	}

	function &_do_statement($statement, $values = NULL)
	{
		$db_result =& $this->db->execute($statement, $values);

		if (!$db_result)
		{
			$error_string = m2f_db::error();
			return m2f::raise_error('Bad SQL statement. Error returned was "' . $error_string . '".', __LINE__, __FILE__);
		}
		return $db_result;
	}
}


class m2f_channels_base_mapper extends m2f_mapper
{

	function &_load_array(&$data)
	{
		$obj =& m2f_factory::make_object($this->class);
		foreach ($this->_properties as $property)
		{
			$name = $property['name'];
			$type = $property['type'];
			if (isset($data[$name])) 
			{
				settype($data[$name], $type);
				if (in_array($name, $obj->channel_params))
				{
					$obj->$name = $data[$name];
				}
				else
				{
					$obj->config[$name] = $data[$name];
				}
			}
		}
		return $obj;
	}
}


?>