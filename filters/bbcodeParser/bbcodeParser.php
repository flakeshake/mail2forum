<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


		
class m2f_filters_bbcodeParser extends m2f_filter
{

	/**
	 * Filter a message
	 * @access private
	 * @return boolean success?
	 */
	function _filter()
	{
		m2f_log::log(M2F_LOG_NORMAL, $this->class, 'parsing', __LINE__, __FILE__); 

		if (!defined('M2F_BBCODE_INC_PATH')) define('M2F_BBCODE_INC_PATH', 'filters/bbcodeParser/inc/');
		
		if (!defined('M2F_BBCODE_QUOTE')) define('M2F_BBCODE_QUOTE', m2f_lang::get('quote', $this->class));
		if (!defined('M2F_BBCODE_AUTHORED_QUOTE')) define('M2F_BBCODE_AUTHORED_QUOTE', m2f_lang::get('authored_quote', $this->class));

		if (!defined('M2F_BBCODE_ASCII_QUOTE_BEFORE')) define('M2F_BBCODE_ASCII_QUOTE_BEFORE', sprintf(m2f_lang::get('ascii_quote_before', $this->class), M2F_BBCODE_QUOTE));
		if (!defined('M2F_BBCODE_ASCII_AUTHORED_QUOTE_BEFORE')) define('M2F_BBCODE_ASCII_AUTHORED_QUOTE_BEFORE', sprintf(m2f_lang::get('ascii_quote_before', $this->class), M2F_BBCODE_AUTHORED_QUOTE));
		if (!defined('M2F_BBCODE_ASCII_QUOTE_AFTER')) define('M2F_BBCODE_ASCII_QUOTE_AFTER', m2f_lang::get('ascii_quote_after', $this->class));

		if (!defined('M2F_BBCODE_HTML_QUOTE_CLASS')) define('M2F_BBCODE_HTML_QUOTE_CLASS', m2f_lang::get('html_quote_class', $this->class));
		if (!defined('M2F_BBCODE_HTML_QUOTE_LABLE_CLASS')) define('M2F_BBCODE_HTML_QUOTE_LABLE_CLASS', m2f_lang::get('html_quote_lable_class', $this->class));

		$options =& PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
		$options = parse_ini_file(M2F_BBCODE_INC_PATH . 'conf.ini');
		unset($options);

		require_once(M2F_BBCODE_INC_PATH . 'BBCodeParser.php');
		$parser =& new HTML_BBCodeParser();
		
		if (in_array('body', $this->_targets)) $this->message->body = $parser->qparse($this->message->body, 'bbcode', 'ASCII');
		if (in_array('html_body', $this->_targets)) $this->message->html_body = $parser->qparse($this->message->html_body, 'bbcode', 'HTML');
		
		return TRUE;
	}
}
		

?>