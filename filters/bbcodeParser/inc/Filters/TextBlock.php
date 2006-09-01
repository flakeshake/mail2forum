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


//require_once('filters/bbcode_ascii_html.php');


class HTML_BBCodeParser_Filter_TextBlock extends HTML_BBCodeParser
{
    /**
     * When rendering in ASCII, what are the ends of 'hr' tags
     * 
     * @access   private
     * @var      string
     */
    var $_hrEnds = '#';
    
    /**
     * When rendering in ASCII, what is the line of 'hr' tags
     * 
     * @access   private
     * @var      string
     */
    var $_hrLine = '~';
    
    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(
            'align' => array( 'htmlopen'  => 'div',
                              'htmlclose' => 'div',
                              'attributes'=> array('align' =>'style=%2$stext-align:%1$s;%2$s')),

            'hr'    => array( 'htmlopen'  => 'hr',
                              'htmlclose' => '',
                              'attributes'=> array()),

            'p'     => array( 'htmlopen'  => 'p',
                              'htmlclose' => 'p',
                              'parent'    => array('all', array('p')),
                              'attributes'=> array('align' =>'style=%2$stext-align:%1$s;%2$s')),

            'br'    => array( 'htmlopen'  => 'br',
                              'htmlclose' => '',
                              'attributes'=> array()),

            'h1'    => array( 'htmlopen'  => 'h1',
                              'htmlclose' => 'h1',
                              'attributes'=> array()),

            'h2'    => array( 'htmlopen'  => 'h2',
                              'htmlclose' => 'h2',
                              'attributes'=> array()),

            'h3'    => array( 'htmlopen'  => 'h3',
                              'htmlclose' => 'h3',
                              'attributes'=> array()),

            'h4'    => array( 'htmlopen'  => 'h4',
                              'htmlclose' => 'h4',
                              'attributes'=> array()),

            'h5'    => array( 'htmlopen'  => 'h5',
                              'htmlclose' => 'h5',
                              'attributes'=> array()),

            'h6'    => array( 'htmlopen'  => 'h6',
                              'htmlclose' => 'h6',
                              'attributes'=> array())
                              );
    
    /**
     * Preparse to close 'br' and 'hr' tags
     * 
     * @return   none
     * @access   protected
     * @see      $_text
     * @author   Seth Price <seth@pricepages.org>
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
            array( "!".$oe."br ?/?".$ce."!i",
                   "!".$oe."hr ?/?".$ce."!i" ),
            
            array( $o."br".$c.$o."/br".$c,
                   $o."hr".$c.$o."/hr".$c ),
            
            $this->_text);
    }

    /**
     * Render the block text element as ASCII
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
        $margin = 1;
        switch ($tag['tag']){
        case 'align':
        case 'p':
            $margin = 0;
            $block = parent::renderAsASCII($definedTags, $tag, $items, $width, true);
            
            if(empty($tag['attributes']['align'])){
                $lines = $block['lines'];
                $lines[] = '';
                break;
            }
            
            switch(strtolower($tag['attributes']['align'])){
            case 'right':
            default:
                $flags = STR_PAD_LEFT;
                break;
            case 'left':
                $flags = STR_PAD_RIGHT;
                break;
            case 'center':
                $flags = STR_PAD_BOTH;
                break;
            }
            $lines = array();
            foreach ($block['lines'] as $line){
                $lines[] = str_pad(trim($line), $width, ' ', $flags);
            }
            
            //Add extra break after </p>
            if($tag['tag'] == 'p'){
                $lines[] = '';
            }
            
            break;

        case 'hr':
            $margin = 0;
            $num = floor(($width - 2*strlen($this->_hrEnds))/strlen($this->_hrLine));
            $rule = $this->_hrEnds.str_repeat($this->_hrLine, $num).$this->_hrEnds;
            $lines = array($rule);
            break;
        
        case 'br':
            $margin = 0;
            $lines = array();
            break;
        
        //Everything in this class is rendered as a block element
        default:
            $arr = parent::renderAsASCII($definedTags, $tag, $items, $width);
            $lines = $arr['lines'];
            break;
        }

        return array('display'=>'block', 'lines'=>$lines, 'margin'=>$margin);
    }
}

?>