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
#                     Filter                         #
#                                                    #
######################################################

class m2f_filter extends m2f_element
{
	/**
	 * Generic message object
	 * @access public
	 * @var object
	 */
	var $message;
	
	/**
	 * Targets on which to perform the filter
	 * @access private
	 * @var array
	 */
	var $_targets;
	
	
	/**
	 * Filter a message - alter its contents somehow. Helper function for _filter()
	 * @access public
	 * @return boolean success?
	 */
	function filter($targets = 'body')
	{
		$this->_targets = (array) $targets;
		$allowable = array('body', 'html_body', 'subject');
		
		foreach ($this->_targets as $key => $target)
		{
			if (!in_array($target, $allowable)) unset($this->_targets[$key]);
		}
		
		$this->_targets = array_values($this->_targets);
		
		if (empty($this->_targets))
		{
			return m2f::raise_error('The filter has no valid targets.', __LINE__, __FILE__);
		}
		
		return $this->_filter();
	}
	

	/**
	 * Filter a message. Should be overridden
	 * @access private
	 * @return boolean success?
	 */
	function _filter()
	{
		return TRUE;
	}

}

?>