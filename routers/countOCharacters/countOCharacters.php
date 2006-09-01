<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class m2f_routers_countOCharacters extends m2f_router
{

	/**
	 * Counts the number of 'o' characters in the message body
	 * @access public
	 * @return int number of chars
	 */
	function route()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'countOCharacters_router', __LINE__, __FILE__); 
		return substr_count($this->message->body, 'o');
	}
}

?>