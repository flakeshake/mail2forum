<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



require_once('channels/email/email.common.php');

class m2f_channels_email_smtp extends m2f_channels_email_common
{

	function _connect()
	{
		require_once('Mail.php');
		$params = array('host' => $this->config['host'], 'port' => $this->config['port'], 'persist' => TRUE);
		$this->smtp =& Mail::Factory('smtp', $params);
	}
	
	/** 
	* Sends an email
	*  
	* @param object $message message
	* @return boolean success?
	* @access private 
	*/
	function _export()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'exporting', __LINE__, __FILE__); 

		$this->_connect();
		
		foreach (array_keys($this->_messages) as $message_id)
		{
			$message =& $this->_messages[$message_id];
			
			$headers['To']      = $this->config['mail_to'];
			$headers['From']    = empty($message->author) ? $message->author_email : '"' . $message->author . '" <' . $message->author_email . '>';
			$headers['Subject'] = $message->subject;
			
			if (m2f::is_error($result =& $this->smtp->send($this->mail_to, $headers, $message->body)))
			{
				return m2f::raise_error('Cannot send mail via SMTP. Error returned was "' . $result->getMessage() . '".', __LINE__, __FILE__);
			}
		}
		
		$this->smtp->disconnect();
		
		$num = count($this->_messages);		
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'sent_mail_smtp', __LINE__, __FILE__, $num, $num); 
		
		return TRUE;
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
}


class m2f_channels_email_smtp_mapper extends m2f_channels_base_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channels_email_smtp (id, direction) VALUES (?, ?)';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channels_email_smtp WHERE id=?';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channels_email_smtp SET direction=? WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channels_email_smtp WHERE id=?';
	}

	function _do_insert(&$object)
	{
		$this->_do_statement($this->_insert_sql, array($object->id, $object->direction));
	}
	
	function _do_update(&$object)
	{
		$this->_do_statement($this->_update_sql, array($object->direction, $object->id));
	}
	
}

?>