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
// $Id: Images.php,v 1.5 2005/10/14 07:05:49 arnaud Exp $
//

/**
* @package  HTML_BBCodeParser
*/

require_once('filters/bbcode_ascii_html/Filter/Images.php');

class HTML_BBCodeParser_Filter_Images_html extends HTML_BBCodeParser_Filter_Images
{

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(
            'img' => array(
                    'htmlopen'  => 'img',
                    'htmlclose' => '',
                    'child'     => array('none'),
                    'attributes'=> array(
                            'src'   => 'src=%2$s%1$s%2$s',
                            'alt'   => 'alt=%2$s%1$s%2$s',
                            'width'     => 'width=%2$s%1$d%2$s',
                            'height'     => 'height=%2$s%1$d%2$s')
                    )
             );

    /**
     * Alters the 'img' tag to be properly closed
     * 
     * There is no <img>image_link.jpg</img> in HTML.
     *
     * @return   none
     * @access   protected
     * @see      $_text
     * @author   Seth Price  <seth@pricepages.org>
     */
    function _preparse()
    {
        $this->_preparsed = $this->_text;
    }
    
}

?>