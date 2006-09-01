<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class m2f_filters_toUppercase extends m2f_filter
{
	/**
	 * Filter a message - alter its contents somehow
	 * @access public
	 * @return boolean success?
	 */
	function _filter()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'toUppercase_filter', __LINE__, __FILE__); 
		$this->message->body = strtoupper($this->message->body);
		return TRUE;
	}
}

?>