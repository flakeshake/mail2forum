<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


if (!defined('M2F_IN_TEST_MODE') && !defined('M2F_NOT_INSTALLED')) 
{
	ini_set('display_errors', '1');
	set_error_handler('m2f_error_handler');
}


function m2f_error_handler($code, $message, $file, $line, $errcontext)
{
	switch ($code) 
	{
		case E_WARNING:
		case E_USER_WARNING:
			$priority = M2F_LOG_NORMAL;
			$level = 'Warning';
			break;
			
		case E_NOTICE:
		case E_USER_NOTICE:
			$priority = M2F_LOG_DEBUG;
			$level = 'Notice';
			break;
			
		case E_ERROR:
		case E_USER_ERROR:
			$priority = M2F_LOG_ERROR;
			$level = 'Error';
			break;
			
		case E_STRICT:
//			$priority = M2F_LOG_DEBUG;
//			$level = 'Strict Notice';
			break;
			
		default:
			$priority = M2F_LOG_NORMAL;
			$level = 'Other';
	}
	
	if (isset($priority)) 
	{
		$message = "PHP Error ($level): " . $message;
		m2f_log::log_error($priority, $message, $line, $file);
	}
}
?>