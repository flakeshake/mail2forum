<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class m2f_routers_isAllUppercase extends m2f_router
{
	
	/**
	 * Check if message body is all uppercase
	 * @access public
	 * @return boolean TRUE if all uppercase
	 */
	function route()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'is_isAllUppercase_router', __LINE__, __FILE__); 
		return (strtoupper($this->message->body) === $this->message->body);
	}
}

?>