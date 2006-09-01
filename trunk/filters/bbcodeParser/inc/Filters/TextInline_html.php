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

/**
* @package  HTML_BBCodeParser
* @author   Seth Price  <seth@pricepages.org>
*/

require_once('filters/bbcode_ascii_html/Filter/TextInline.php');

class HTML_BBCodeParser_Filter_TextInline_html extends HTML_BBCodeParser_Filter_TextInline
{

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(
            'font'  => array( 'htmlopen'  => 'span',
                              'htmlclose' => 'span',
                              'isValidIn' => array('all'),
                              'parent'    => array('all'),
                              'attributes'=> array(
                                  'face' =>'face=%2$s%1$s%2$s',
                                  'color' =>'color=%2$s%1$s%2$s',
                                  'size' =>'size=%2$s%1$s%2$s',
                                  'style' =>'style=%2$s%1$s%2$s')),

            'strong'  => array( 'htmlopen'  => 'strong',
                              'htmlclose' => 'strong',
                              'attributes'=> array()),

            'em'  => array( 'htmlopen'  => 'em',
                              'htmlclose' => 'em',
                              'attributes'=> array()),

            'code'  => array( 'htmlopen'  => 'code',
                              'htmlclose' => 'code',
                              'attributes'=> array()),

            'abbr'  => array( 'htmlopen'  => 'abbr',
                              'htmlclose' => 'abbr',
                              'attributes'=> array('abbr' =>'title=%2$s%1$s%2$s')),

            'q' => array( 'htmlopen'  => 'q',
                              'htmlclose' => 'q',
                              'attributes'=> array('quote' =>'cite=%2$s%1$s%2$s'))
                             );
    /**
     * Translate the font tag into
     * 
     * @return   none
     * @see      _checkTags
     * @author   Seth Price  <seth@pricepages.org>
     */
    function _checkTag(&$tags, $i){
        if( $tags[$i]['type'] == 1 &&
            !empty($tags[$i]['attributes']['style'])){
            
            $style = $tags[$i]['attributes']['style'];
        } else {
            $style = '';
        }

        if( $tags[$i]['type'] == 1 &&
            !empty($tags[$i]['attributes']['face'])){
            
            $style .= 'font-family:'.$tags[$i]['attributes']['face'].';';
            unset($tags[$i]['attributes']['face']);
        }
        
        if( $tags[$i]['type'] == 1 &&
            !empty($tags[$i]['attributes']['size'])){
            
            $size = $tags[$i]['attributes']['size'];
            
            if(is_numeric($size)){
                switch(intval($size)){
                    case 1:
                        $size = 'xx-small';
                        break;
                    case 2:
                        $size = 'x-small';
                        break;
                    case 3:
                        $size = 'small';
                        break;
                    case 4:
                    default:
                        $size = 'medium';
                        break;
                    case 5:
                        $size = 'large';
                        break;
                    case 6:
                        $size = 'x-large';
                        break;
                    case 7:
                        $size = 'xx-large';
                        break;
                }
            }
            
            $style .= 'font-size:'.$size.';';
            unset($tags[$i]['attributes']['size']);
        }
        
        if( $tags[$i]['type'] == 1 &&
            !empty($tags[$i]['attributes']['color'])){
            
            $style .= 'color:'.$tags[$i]['attributes']['color'].';';
            unset($tags[$i]['attributes']['color']);
        }
        
        $tags[$i]['attributes']['style'] = $style;
    }

}

?>