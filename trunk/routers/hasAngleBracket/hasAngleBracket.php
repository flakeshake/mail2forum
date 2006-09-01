<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class m2f_routers_hasAngleBracket extends m2f_router
{
	/**
	 * Checks message body for a '>' character
	 * @access public
	 * @return boolean TRUE if '>' is found
	 */
	function route()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'has_hasAngleBracket_router', __LINE__, __FILE__); 
		return (!strpos($this->message->body, '>') === FALSE);
	}
}

?>