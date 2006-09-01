<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stijn de Reede <sjr@gmx.co.uk>                               |
// +----------------------------------------------------------------------+
//
// $Id: Email.php,v 1.4 2004/02/05 16:39:45 sjr Exp $
//

/**
* @package  HTML_BBCodeParser
*/

require_once('filters/bbcode_ascii_html/Filter/Email.php');

class HTML_BBCodeParser_Filter_Email_html extends HTML_BBCodeParser_Filter_Email
{

    /**
    * An array of tags parsed by the engine
    *
    * There is no "email" html tag
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array();


    /**
     * Creates automatic email links
     * 
     * The regex recognizes an email address that hasen't been linked with
     * markup, and marks it up.
     *
     * @return   none
     * @access   private
     * @see      $_text
     * @author   Stijn de Reede  <sjr@gmx.co.uk>
     * @author   Seth Price  <seth@pricepages.org>
     */
    function _preparse()
    {
        $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
        $o = $options['open'];
        $c = $options['close'];
        $oe = $options['open_esc'];
        $ce = $options['close_esc'];
        
        $pattern = array(   "!(^|\s)([-a-z0-9_.+]+@[-a-z0-9.]+\.[a-z]{2,4})!i");
        
        $replace = array(   "\\1".$o."a href=\"mailto:\\2\"".$c."\\2".$o."/a".$c);
        
        $this->_preparsed = preg_replace($pattern, $replace, $this->_text);
    }
}
?>