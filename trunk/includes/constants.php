<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


/* Element Properties (Bitwise Flags) */
define('M2F_CHANNEL_CAN_EXPORT', 1);
define('M2F_CHANNEL_CAN_IMPORT', 2);



/*
		PEAR Log reference:
		~~~~~~~~~~~~~~~~~~
PEAR_LOG_EMERG	emerg()	System is unusable
PEAR_LOG_ALERT	alert()	Immediate action required
PEAR_LOG_CRIT	crit()	Critical conditions
PEAR_LOG_ERR	err()	Error conditions
PEAR_LOG_WARNING	warning()	Warning conditions
PEAR_LOG_NOTICE	notice()	Normal but significant
PEAR_LOG_INFO	info()	Informational
PEAR_LOG_DEBUG	debug()	Debug-level messages
*/

/* Logging and Errors */
define('M2F_LOG_FATAL', PEAR_LOG_CRIT);
define('M2F_LOG_ERROR', PEAR_LOG_ERR);
define('M2F_LOG_NORMAL', PEAR_LOG_NOTICE);
define('M2F_LOG_ADMIN_ERROR', PEAR_LOG_INFO);
define('M2F_LOG_DEBUG', PEAR_LOG_DEBUG);

?>