<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


define('M2F_EXTERNAL_INCLUDES_PATH', 'includes');

// make sure we use the local PEAR rather than any currently installed on the server
ini_set('include_path', realpath(M2F_EXTERNAL_INCLUDES_PATH . '/pear/PEAR') . PATH_SEPARATOR . '.' . PATH_SEPARATOR . ini_get('include_path'));


/* m2f files to be included first */
require_once('errors.php');
require_once('security.php');

/* m2f classes */
require_once('m2f_mapper.php');
require_once('m2f_element.php');
require_once('m2f_channel.php');
require_once('m2f_filter.php');
require_once('m2f_router.php');
require_once('m2f_chain.php');

/* PEAR classes */
require_once('PEAR.php');
require_once('Log.php');
require_once(M2F_EXTERNAL_INCLUDES_PATH . '/pear_extensions/Log_ADODB.php');
require_once('Translation2/Admin.php');

/* ADODB */
require_once(M2F_EXTERNAL_INCLUDES_PATH . '/adodb/adodb-errorpear.inc.php');
require_once(M2F_EXTERNAL_INCLUDES_PATH . '/adodb/adodb.inc.php');
require_once(M2F_EXTERNAL_INCLUDES_PATH . '/adodb/adodb-xmlschema03.inc.php');

/* other m2f files */
require_once('constants.php');

?>