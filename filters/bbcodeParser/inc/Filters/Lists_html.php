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
// $Id: Lists.php,v 1.4 2005/10/14 07:05:49 arnaud Exp $
//


/**
* @package  HTML_BBCodeParser
* @author   Stijn de Reede  <sjr@gmx.co.uk>
*/

/**
 * 
 */
require_once('filters/bbcode_ascii_html/Filter/Lists.php');

/**
 * 
 */
class HTML_BBCodeParser_Filter_Lists_html extends HTML_BBCodeParser_Filter_Lists
{

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(  'ol'  => array(   'htmlopen'  => 'ol',
                                                    'htmlclose' => 'ol',
                                                    'child'     => array('none', array('li')),
                                                    'parent'    => array('all', array('p')),
                                                    'isValidIn' => array('all', array('p')),
                                                    'attributes'=> array('type'  => 'style=%2$slist-style-type:%1$s;%2$s')
    
    /*
     * The "start" list attribute has been removed in XHTML 1.1. I've decided to
     * adhere to the standard and produce only valid XHTML 1.1 by default.
     * 
     * I've made it easy for you to re-enable it, though. To enable starting a list
     * at a given number, add this back into the attributes for 'ol':
     * 
     * 'start'     => 'start=%2$s%1$s%2$s'
     * 
     * and this to 'li':
     * 'value'     => 'value=%2$s%1$s%2$s'
     */
     
                                                    ),
                                'ul' => array(   'htmlopen'  => 'ul',
                                                    'htmlclose' => 'ul',
                                                    'child'     => array('none', array('li')),
                                                    'parent'    => array('all', array('p')),
                                                    'isValidIn' => array('all', array('p')),
                                                    'attributes'=> array('type'  => 'style=%2$slist-style-type:%1$s;%2$s')
                                                    ),
                                'li'    => array(   'htmlopen'  => 'li',
                                                    'htmlclose' => 'li',
                                                    'parent'    => array('none', array('ol','ul')),
                                                    'isValidIn' => array('all'),
                                                    'attributes'=> array()
                                                    )
                                );

    /**
     * We'll update the tag in _checkTag
     *
     * @return   none
     * @access   private
     * @see      $_text
     * @author   Stijn de Reede <sjr@gmx.co.uk>
     * @author   Seth Price <seth@pricepages.org>
     */
    function _preparse()
    {
        $this->_preparsed = $this->_text;
    }
    
    /**
     * Translate the list style type
     * 
     * @return   none
     * @see      _checkTags
     * @author   Seth Price  <seth@pricepages.org>
     */
    function _checkTag(&$tags, $i){
        if( $tags[$i]['type'] == 1 &&
            !empty($tags[$i]['attributes']['type'])){
            
            switch($tags[$i]['attributes']['type']){
                case 'A':
                    $new = 'upper-alpha';
                    break;
                case 'a':
                    $new = 'lower-alpha';
                    break;
                case 'I':
                    $new = 'upper-roman';
                    break;
                case 'i':
                    $new = 'lower-roman';
                    break;
                case '1':
                    $new = 'decimal';
                    break;
            }
            
            $tags[$i]['attributes']['type'] = $new;
        }
    }

}

?>