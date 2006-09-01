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
require_once('Mail/mime.php');
require_once ('Mail.php');

class m2f_channels_email_phpMail extends m2f_channels_email_common
{

	/** 
	* Sends an email
	*  
	* @param object $message message
	* @return boolean success?
	* @access private 
	*/
	function _export()
	{
		foreach (array_keys($this->_messages) as $message_id)
		{
			$message =& $this->_messages[$message_id];
			

			$mime = new Mail_Mime("\n");
			$mime->setTxtBody($message->body);
			$mime->setHtmlBody($message->html_body);
			
			$mail_body = $mime->get(); 
			$mail_body = str_replace("\r\n", "\n", $mail_body);
			
			$headers['From']    = 'g.o.crawford@gmail.com';
			$headers['Subject'] = $message->subject;
			$headers = $mime->headers($headers); 
			
			$pear_mail =& Mail::Factory('mail');
			$send = $pear_mail->send('g.o.crawford@gmail.com', $headers, $mail_body);
			
		}
		
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


class m2f_channels_email_phpMail_mapper extends m2f_channels_base_mapper
{
	function _setup()
	{
		$this->_insert_sql = 'INSERT INTO ' . $this->_prefix . '_channels_email_phpMail (id, direction) VALUES (?, ?)';
		$this->_select_sql = 'SELECT * FROM ' . $this->_prefix . '_channels_email_phpMail WHERE id=?';
		$this->_update_sql = 'UPDATE ' . $this->_prefix . '_channels_email_phpMail SET filepath=? WHERE id=?';
		$this->_delete_sql = 'DELETE FROM ' . $this->_prefix . '_channels_email_phpMail WHERE id=?';
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