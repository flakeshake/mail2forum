<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class m2f_channels_email_common extends m2f_channel
{
	
	/** 
	* Decode a MIME email
	*  
	* @access private 
	* @param string $raw_msg the raw email message
	* @return object a decoded PEAR MIME message object
	*/
	function &_decode(&$raw_msg)
	{
		require_once('Mail/mimeDecode.php');  // PEAR mimeDecode layer

		// Parameters for  mail decoding 
		$params = array(); 
		$params['include_bodies'] = TRUE; 
		$params['decode_bodies']  = TRUE; 
		$params['decode_headers'] = TRUE; 
		$params['input'] =& $raw_msg; 
			
		$ret = Mail_mimeDecode::decode($params);
		return $ret; 
	}
	

	/** 
	* find message id from email header
	*  
	* @return mixed message ID or NULL
	* @access private 
	*/
	function _get_message_id(&$id_header)
	{
		preg_match('/^\s*<?(.+?)>?\s*$/', $id_header, $matches);
		return $matches[1] ? $matches[1] : $id_header;
	}


	/** 
	* Transform a MIME-decoded email into a generic message
	*  
	* @access private 
	* @param object $raw_msg the PEAR MIME object
	* @return object a generic message object
	*/
	function &_transform(&$message)
	{
		m2f_log::log(M2F_LOG_NORMAL, 'channels_email.common', 'email_transform', __LINE__, __FILE__); 

		if (empty($message)) return m2f::raise_error('Cannot transform a blank message.', __LINE__, __FILE__);
		
		if (m2f::is_error($structure = $this->_decode($message))) 
		{
			return m2f::raise_error('Could not decode MIME message. Error returned was "' . $structure->getMessage() . '".', __LINE__, __FILE__);
		}
		
		$message =& new m2f_generic_message;
		$message->body = trim($structure->body);
		
		foreach ($structure->headers as $header => $value)
		{
			switch($header)
			{
				case 'subject':
					$message->subject = $value;
					break;

				case 'message-id':
					$message->id = $this->_get_message_id($value);
					break;

				case 'from':
					if (preg_match('/"(.*?)" <(.*?)>/', $value, $matches))
					{
						$message->author = $matches[1];
						$message->author_email = $matches[2];
					}
					break;
			}
		}
		
		return $message;
	}
}

?>