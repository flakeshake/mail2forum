<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/

/************************************************************
 *
 *	This file contains functions taken and adapted from phpBB
 *  (http://www.phpbb.com). We copy only the bare minimum of
 *  functions necessary to emulate the correct insert_post
 *	process.
 *
 /***********************************************************/

function encode_ip($dotquad_ip)
{
	$ip_sep = explode('.', $dotquad_ip);
	return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
}

function dss_rand()
{
	global $board_config;
	$val = $board_config['rand_seed'] . microtime();
	$val = md5($val);
	$board_config['rand_seed'] = md5($board_config['rand_seed'] . $val . 'a');
	return substr($val, 4, 16);
}

?>
