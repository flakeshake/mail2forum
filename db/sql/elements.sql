<?php

/************************************************************
 *
 *	$Date: 2006-06-23 00:57:26 +0100 (Fri, 23 Jun 2006) $
 *	$Revision: 78 $
 *	$Author: georgeocrawford $
 *	$HeadURL: https://svn.sourceforge.net/svnroot/m2f/tests/admin.php $
 *
 /***********************************************************/

$command = 'INSERT INTO ' . $this->db_prefix . '_element (name, element_fields) VALUES (?, ?)';

$queries = array(	'channel', 
									'chain',
		);

?>