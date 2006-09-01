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
* @author   Stijn de Reede  <sjr@gmx.co.uk>
*/

//require_once('filters/bbcode_ascii_html.php');

class HTML_BBCodeParser_Filter_Email extends HTML_BBCodeParser
{

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(  'email' => array(   'htmlopen'  => 'a',
                                                    'htmlclose' => 'a',
                                                    'parent'   => array('all', array('url', 'email')),
                                                    'attributes'=> array('email' =>'href=%2$smailto:%1$s%2$s')

                                               )
                              );


    /**
     * Creates automatic email links
     * 
     * The first regex recognizes an email address that hasen't been linked with
     * markup, and marks it up. The second regex recognizes an email link
     * without an attribute ([email]addr@example.com[/email]) and adds the
     * proper attribute to it ([email=addr@example.com]addr@example.com
     * [/email]).
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
        
        $pattern = array(   "!(^|\s)([-a-z0-9_.+]+@[-a-z0-9.]+\.[a-z]{2,4})!i",
                            "!".$oe."email(".$ce."|\s.*".$ce.")(.*)".$oe."/email".$ce."!Ui");
        
        $replace = array(   "\\1".$o."email=\\2".$c."\\2".$o."/email".$c,
                            $o."email=\\2\\1\\2".$o."/email".$c);
        
        $this->_preparsed = preg_replace($pattern, $replace, $this->_text);
    }

    /**
     * Render as ASCII by printing the URL next to email's text in parens
     * 
     * @param   array    All of the defined tags and their filters.
     * @param   array    The tag for this.
     * @param   array    Children of this tag.
     * @param   integer  The width to render this element at (in columns)
     * @return  array    This rendered element, including text and formatting
     * @access  protected
     * @author  Seth Price  <seth@pricepages.org>
     */
    function renderAsASCII(&$definedTags, &$tag, &$items, $width, $forceWrap = false){
        $block = parent::renderAsASCII($definedTags, $tag, $items, $width);
        
        //If the email equals the text, return the text straight up
        if ($tag['attributes']['email'] == $block['text']){
            return $block;
        }
        
        $block['text'] .= ' ('.$tag['attributes']['email'].')';
        return $block;
    }
}
?>