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
* @author   Stijn de Reede  <sjr@gmx.co.uk>
*/

//require_once('filters/bbcode_ascii_html.php');

class HTML_BBCodeParser_Filter_Images extends HTML_BBCodeParser
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
                            'img'   => 'src=%2$s%1$s%2$s',
                            'alt'   => 'alt=%2$s%1$s%2$s',
                            'float' => 'style=%2$sfloat:%1$s;%2$s',
                            'w'     => 'width=%2$s%1$d%2$s',
                            'h'     => 'height=%2$s%1$d%2$s')
                    )
             );

    /**
     * Alters the 'img' tag to be properly closed
     * 
     * Replaces [img]image_link.jpg[/img] with [img=image_link.jpg][/img] in
     * order to more easily deal with proper XML style closure.
     *
     * @return   none
     * @access   protected
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
        
        //Convert to a single tag
        $this->_preparsed = preg_replace(
            "!".$oe."img(\s?.*)".$ce."(.*)".$oe."/img".$ce."!Ui",
            $o."img=\$2\$1".$c.$o."/img".$c,
            $this->_text);
    }
    
    /**
     * Ensure that the image has an alt attribute
     * 
     * @return   none
     * @see      _checkTags
     * @author   Seth Price  <seth@pricepages.org>
     */
    function _checkTag(&$tags, $i){
        if( $tags[$i]['type'] == 1 &&
            !isset($tags[$i]['attributes']['alt'])){
                
            $tags[$i]['attributes']['alt'] = '';
        }
    }
}

?>