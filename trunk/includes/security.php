<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/

// Not really security, but worth checking!
set_magic_quotes_runtime(FALSE);


// Stolen from phpBB2. Unset any variables in the global scope due to register_globals
if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
{
	$not_unset = array('HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_COOKIE_VARS', 'HTTP_SERVER_VARS', 'HTTP_SESSION_VARS', 'HTTP_ENV_VARS', 'HTTP_POST_FILES');

	if (!isset($HTTP_SESSION_VARS) || !is_array($HTTP_SESSION_VARS)) $HTTP_SESSION_VARS = array();
	$input = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_SESSION_VARS, $HTTP_ENV_VARS, $HTTP_POST_FILES);

	unset($input['input']);
	unset($input['not_unset']);

	while (list($var,) = @each($input))
	{
		if (in_array($var, $not_unset)) die('Hacking attempt!');
		unset($$var);
	}

	unset($input);
}


?>