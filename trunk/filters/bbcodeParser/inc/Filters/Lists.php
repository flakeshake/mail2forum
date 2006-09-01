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
//require_once('filters/bbcode_ascii_html.php');

/**
 * 
 */
class HTML_BBCodeParser_Filter_Lists extends HTML_BBCodeParser
{

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(  'list'  => array(   'htmlopen'  => 'ol',
                                                    'htmlclose' => 'ol',
                                                    'child'     => array('none', array('li')),
                                                    'parent'    => array('all', array('p')),
                                                    'isValidIn' => array('all', array('p')),
                                                    'attributes'=> array('list'  => 'style=%2$slist-style-type:%1$s;%2$s')
    
    /*
     * The "start" list attribute has been removed in XHTML 1.1. I've decided to
     * adhere to the standard and produce only valid XHTML 1.1 by default.
     * 
     * I've made it easy for you to re-enable it, though. To enable starting a list
     * at a given number, add this back into the attributes for 'list':
     * 
     * 's'     => 'start=%2$s%1$s%2$s'
     */
     
                                                    ),
                                'ulist' => array(   'htmlopen'  => 'ul',
                                                    'htmlclose' => 'ul',
                                                    'child'     => array('none', array('li')),
                                                    'parent'    => array('all', array('p')),
                                                    'isValidIn' => array('all', array('p')),
                                                    'attributes'=> array('list'  => 'style=%2$slist-style-type:%1$s;%2$s')
                                                    ),
                                'li'    => array(   'htmlopen'  => 'li',
                                                    'htmlclose' => 'li',
                                                    'parent'    => array('none', array('list','ulist')),
                                                    'isValidIn' => array('all'),
                                                    'attributes'=> array()
                                                    )
                                );


    /**
     * An array of ASCII "bullets"
     * 
     * The bullets are used (in order) when printing an unordered list as text.
     * 
     * @access   private
     * @var      array
     */
    var $_ulist = array('disc', 'circle', 'square');
    
    /**
     * Translation between CSS bullets and ASCII bullets
     * 
     * @access   private
     * @var      array
     */
    var $_ulistTrns = array(
        'none'   => ' ',
        'disc'   => '*',
        'circle' => 'o',
        'square' => '-'); //you do better...
    
    /**
     * An array of ASCII numrical types
     * 
     * These numrical types are used (in order) when printing an ordered list as
     * text.
     * 
     * @access   private
     * @var      array
     */
    var $_list = array('decimal', 'lower-alpha', 'lower-roman');
    
    /**
     * Ordered list seperator
     * 
     * This is the seperating string for ordered lists. A list with "1.item one"
     * uses '.' as the seperator.
     * 
     * @access   private
     * @var      string
     */
    var $_listSep = '.';
    
    /**
     * Correct "lazy" BBCode
     * 
     * The first regex replaces [*] tags with [li] tags. The problems with
     * closure will be addressed in the stack renderer and not here.
     * 
     * The rest of the regexs translate from traditional BBCode markup to
     * standard CSS markup for the list-style-type.
     * 
     * There used to be another regex, but it was removed because I couldn't
     * figure out what it did of use:
     * 
     * "!".$oe."(u?)list([^".$ce."] *)".$ce."!i" -> $o."\$1list\$2".$c
     *
     * @return   none
     * @access   private
     * @see      $_text
     * @author   Stijn de Reede <sjr@gmx.co.uk>
     * @author   Seth Price <seth@pricepages.org>
     */
    function _preparse()
    {
        $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
        $o = $options['open'];
        $c = $options['close'];
        $oe = $options['open_esc'];
        $ce = $options['close_esc'];
        
        $pattern = array(   "!".$oe."\*".$ce."!",
                            "!".$oe."(u?)list".$ce."!i",
                            "!".$oe."(u?)list=(?-i:A)(\s*[^".$ce."]*)".$ce."!i",
                            "!".$oe."(u?)list=(?-i:a)(\s*[^".$ce."]*)".$ce."!i",
                            "!".$oe."(u?)list=(?-i:I)(\s*[^".$ce."]*)".$ce."!i",
                            "!".$oe."(u?)list=(?-i:i)(\s*[^".$ce."]*)".$ce."!i",
                            "!".$oe."(u?)list=(?-i:1)(\s*[^".$ce."]*)".$ce."!i");
        
        $replace = array(   $o."li".$c,
                            $o."\$1list=disc\$2".$c,
                            $o."\$1list=upper-alpha\$2".$c,
                            $o."\$1list=lower-alpha\$2".$c,
                            $o."\$1list=upper-roman\$2".$c,
                            $o."\$1list=lower-roman\$2".$c,
                            $o."\$1list=decimal\$2".$c );
        
        $this->_preparsed = preg_replace($pattern, $replace, $this->_text);
    }
    
    /**
     * Renders a list as plain ASCII
     * 
     * The PEAR Numbers_Roman package is required to print roman numerals
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
        static $oDepth = 0;
        static $uDepth = 0;
        static $listDepth = 0;

        $numItems = count($items);
        $listText = '';
        $decDepth = false;
        ++$listDepth;
        
        // "  X.", minimum four spaces (tab)
        if($tag['tag'] == 'ol'){
            $listType = 'list';
            if(!empty($tag['attributes']['list'])){
                $listStyleType = $tag['attributes']['list'];
            } else {
                $listStyleType = $this->_list[$oDepth++ % count($this->_list)];
                $decDepth = true;
            }
            
            //Get all of the numbers to find the longest label
            $maxLabelLen = 3;//init
            for ($i = 0;$i < $numItems; $i++){
                $len = strlen($this->_getStyledListNumber($i, $listStyleType));
                if ($len > $maxLabelLen){
                    $maxLabelLen = $len;
                }
            }
            $indentSpaces = $maxLabelLen + strlen($this->_listSep);
            
            //Where to start counting
            if (isset($tag['attributes']['s'])){
                $listIndex = (int) $tag['attributes']['s'];
            } else {
                $listIndex = 1;
            }
        }
        //ulist
        else {
            $listType = 'ulist';
            if(!empty($tag['attributes']['ulist'])){
                $listStyleType = $tag['attributes']['ulist'];
            } else {
                $listStyleType = $this->_ulist[$uDepth++ % count($this->_ulist)];
                $decDepth = true;
            }
            
            /*
             * Find the width of the column. Keep it similar to the ordered list
             * width, and allow for bullets of different lengths. Or at least
             * make an attempt.
             */
            $listBullet = $this->_ulistTrns[$listStyleType];
            $indentSpaces = strlen($this->_listSep) + 3;
            $indentBulletStr = str_repeat(' ', $indentSpaces - (1 + strlen($listBullet))).$listBullet.' ';
        }
        
        $childrenWidth = $width - $indentSpaces;
        $indentSpaceStr = str_repeat(' ', $indentSpaces);
        $lines = array();

        //Each child is going to be a list item tag
        for ($i = 0; $i < $numItems; $i++){
            $itemTag = array_shift($items[$i]);
            
            //Skip if not a list item tag
            if (!$itemTag['tag'] || $itemTag['tag'] != 'li'){
                continue;
            }
            
            //Render each list item as text
            $block = parent::renderAsASCII($definedTags, $itemTag, $items[$i], $childrenWidth, true);

            //Indent & append retrieved text
            if($listType == 'ulist'){
                $lines[] = $indentBulletStr.array_shift($block['lines']);
            } else {
                $label = $this->_getStyledListNumber($listIndex++, $listStyleType, $maxLabelLen).$this->_listSep;
                $lines[] = $label.array_shift($block['lines']);
            }

            foreach ($block['lines'] as $line){
                $lines[] = $indentSpaceStr.$line;
            }
        }
        
        //Decrement depth counter
        if($decDepth){
            if($listType == 'ulist'){
                --$uDepth;
            } else {
                --$oDepth;
            }
        }

        //Make a margin only if we have no depth
        if(--$listDepth){
            $margin = 0;
        } else {
            $margin = 1;
        }
        
        return array('display'=>'block', 'lines'=>$lines, 'margin'=>$margin);
    }
    
    /**
     * Render a given list-style-type number as ASCII
     * 
     * Note that the PEAR package Numbers_Roman must be installed for roman
     * numerals to work.
     * 
     * @param    integer  Number to render
     * @param    string   Way to render the number, matches CSS rendering
     * @param    integer  Amount of padding to use
     * @return   string
     * @access   private
     */
    function _getStyledListNumber($integer, $listStyleType, $width = ''){
        
        //Make sure we have the PEAR class Numbers_Roman if needed
        if (($listStyleType == 'upper-roman' || $listStyleType == 'lower-roman') &&
            !class_exists('Numbers_Roman')){
            
            include_once 'Numbers/Roman.php';
            
            if(!class_exists('Numbers_Roman')){
                trigger_error('Numbers_Roman not installed', E_USER_NOTICE);
                $listStyleType = 'decimal';
            }
            elseif(!method_exists(new Numbers_Roman(), 'toNumeral')){
                trigger_error('Numbers_Roman missing "toNumeral" method', E_USER_NOTICE);
                $listStyleType = 'decimal';
            }
        }
        
        /*
         * CSS 2.1 is undefined past 26 for latin/alpha and I can't find a
         * mention of it in CSS 3.0. We will go with A,B...Z,AA,BB... because my
         * wife likes it. Good luck with that.
         * 
         * Remember that the integer should be starting at 1.
         * 
         * http://www.w3.org/TR/CSS21/generate.html#lists
         * http://www.w3.org/TR/2002/WD-css3-lists-20021107/#upper-alpha
         */
        $numLetters = 26;
        if ($listStyleType == 'upper-roman' ||
            $listStyleType == 'lower-roman' ||
            $listStyleType == 'upper-alpha' ||
            $listStyleType == 'lower-alpha' ){
            
            if ($integer > $numLetters){
                $alphaRep = (int) ceil($integer/$numLetters - 0.0001);
                $alphaOff = ($integer - 1) % $numLetters;
            } else {
                $alphaRep = 1;
                $alphaOff = $integer - 1;
            }
        }

        //Do it to it.
        switch ($listStyleType){
        default:
        case 'decimal':
            return sprintf('%'.$width.'d', $integer);
            
        case 'decimal-leading-zero':
            return sprintf('%0'.$width.'d', $integer);
            
        case 'upper-roman':
            return sprintf('%'.$width.'s', Numbers_Roman::toNumeral($integer, true, false));
            
        case 'lower-roman':
            return sprintf('%'.$width.'s', Numbers_Roman::toNumeral($integer, false, false));
            
        case 'lower-latin':
        case 'lower-alpha':
            return sprintf('%'.$width.'s', str_repeat(chr(97 + $alphaOff), $alphaRep));
            
        case 'upper-latin':
        case 'upper-alpha':
            return sprintf('%'.$width.'s', str_repeat(chr(65 + $alphaOff), $alphaRep));
        }
    }
}

?>