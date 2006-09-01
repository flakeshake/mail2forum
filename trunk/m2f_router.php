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
#                     Router                         #
#                                                    #
######################################################

class m2f_router extends m2f_element
{
	
	/**
	 * Array of all the routing options and their respective test conditions
	 * @access public
	 * @var array
	 */
	var $options = array();

	/**
	 * Generic message object
	 * @access public
	 * @var object
	 */
	var $message;

	/** 
	* Adds an option to this router, and the condition which will trigger the option
	*  
	* @return boolean success?
	* @access public 
	* @param object $object the 'thing' to add as an option depending on this router's tests 
	*   (could be a chain or just a simple output channel)
	* @param mixed $condition if this condition matches the outcome of the router test, the option will be triggered
	*/
	function add_option(&$object, $condition)
	{
		if ($object->type == 'channel')
		{
			if (!($object->properties & M2F_CHANNEL_CAN_EXPORT)) return m2f::raise_error('You can\'t add an import channel to a router!', __LINE__, __FILE__);
			$object->direction = 'out';
		}
		$object->condition = $condition;
		
		$this->options[] =& $object;
		
		return TRUE;
	}
	
	/**
	 * Route a message - check for something and return the results. Should be overridden in descendent classes
	 * @access public
	 * @return mixed boolean success, charcter count, or whatever
	 */
	function route()
	{
		return NULL;
	}
}

?>