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
#                     Element                        #
#                                                    #
######################################################

class m2f_element
{

	/**
	* Unique ID for this element
	* @access public
	* @var int
	*/
	var $id;
	
	/**
	* Name of the element (class name minus initial 'm2f_')
	* @access public
	* @var string
	*/
	var $class;
	
	/**
	* Type of element (channel, chain.....)
	* @access public
	* @var string
	*/
	var $type;

	function m2f_element()
	{
		$this->_set_class_name();
	}
	
	function _set_class_name()
	{
		$class = strtolower(get_class($this));
		preg_match('#^m2f_(.*?)(s_.*)?$#', $class, $matches);
		$this->class = $matches[1];
		if (isset($matches[2])) $this->class .= $matches[2];
		$this->type = $matches[1];
	}

}

?>