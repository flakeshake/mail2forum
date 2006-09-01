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
// $Id: Links.php,v 1.6 2005/10/14 07:05:49 arnaud Exp $
//

/**
* @package  HTML_BBCodeParser
* @author   Stijn de Reede  <sjr@gmx.co.uk>
*/

/**
 * 
 */
require_once('filters/bbcode_ascii_html/Filter/Links.php');

/**
 * 
 */
class HTML_BBCodeParser_Filter_Links_html extends HTML_BBCodeParser_Filter_Links
{
    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(
            'a' => array(
                    'htmlopen'  => 'a',
                    'htmlclose' => 'a',
                    'isValidIn' => array('all', array('a','email','img')),
                    'parent'    => array('all', array('a', 'email')),
                    'attributes'=> array(
                            'href'   => 'href=%2$s%1$s%2$s',
                            'target'     => 'onclick=%2$swindow.open(this.href, &quot;%1$s&quot;); return false;%2$s',
                            'title' => 'title=%2$s%1$s%2$s')
                    )
            );


    /**
     * Does wild things to URLs
     * 
     * The first regex and its companion callback function finds urls that
     * haven't been marked up, and marks them up. Note that this must exclude
     * URLs that are already enclosed by [img] or [url] tags.
     * 
     * The second regex adds the URL as the attribute of the [url] tag if it
     * isn't already. Thus [url]http://example.com/[/url] is expanded into
     * [url=http://example.com/]http://example.com/[/url].
     * 
     * The third regex does some filtering of the url, and attempts to ensure
     * that the scheme (ex. 'http' or 'ftp') is OK. Filtering includes adding a
     * slash after the server name and prepending with a scheme if there isn't
     * already one. (Also, we expand the HTML to include href)
     *
     * @return   none
     * @access   private
     * @see      $_text
     * @author   Stijn de Reede <sjr@gmx.co.uk>
     * @author   Seth Price <seth@pricepages.org>
     */
    function _preparse(){
        $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
        $o = $options['open'];
        $c = $options['close'];
        $oe = $options['open_esc'];
        $ce = $options['close_esc'];
        
        $schemes = implode('|', $this->_allowedSchemes);
        
        $pattern = array(   "/(?<![\"'=".$ce."\/])(".$oe."[^".$ce."]*".$ce.")?(((".$schemes."):\/\/|www)[@-a-z0-9.]+\.[a-z]{2,4}[^\s()\[\]]*)/i",
                            "!".$oe."a([^".$ce."]+)href=(([a-z]*):(?://)?)?([^/\s".$ce."]*)([^\s".$ce."]*)([^".$ce."]*)".$ce."(.*)".$oe."/a".$ce."!i");

        $pp = preg_replace_callback($pattern[0], array($this, 'smarterPPLinkExpand'), $this->_text);
        $this->_preparsed = preg_replace_callback($pattern[1], array($this, 'smarterPPLink'), $pp);
 
    }
    
    /**
     * Intelligently expand a URL into a link
     * 
     * @return  string
     * @access  private
     * @author  Seth Price <seth@pricepages.org>
     */
    function smarterPPLinkExpand($matches){
        $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
        $o = $options['open'];
        $c = $options['close'];
        
        /*
         * If we have an intro tag that starts with [url or [img, then skip this
         * match.
         */
        if (strncasecmp($matches[1], $o.'a', strlen($o.'a')) === 0 ||
            strncasecmp($matches[1], $o.'img', strlen($o.'img')) === 0 ){
            
            return $matches[0];
        }
        
         $off = strpos($matches[2], ':');
        
        //Is a ":" (therefore a scheme) defined?
        if($off === false){
            /*
             * Create a link with the default scheme of http. Notice that the
             * text that is viewable to the user is unchanged, but the link
             * itself contains the "http://".
             */
            return $matches[1].$o.'a href="'.$this->_defaultScheme.'://'.$matches[2].'"'.$c.$matches[2].$o.'/a'.$c;
        }

        $scheme = substr($matches[2], 0, $off);
        
        /*
         * If protocol is in the approved list then allow it. Note that this
         * check isn't really needed, but the created link will just be deleted
         * later in smarterPPLink() if we create it now and it isn't on the
         * scheme list.
         */
        if(in_array($scheme, $this->_allowedSchemes)){
            return $matches[1].$o.'a href="'.$matches[2].'"'.$c.$matches[2].$o.'/a'.$c;
        } else {
            return $matches[0];
        }
    }
    
    /**
     * Finish preparsing URL to clean it up
     * 
     * @return  string
     * @access  private
     * @author  Seth Price <seth@pricepages.org>
     */
    function smarterPPLink($matches){
        $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
        $o = $options['open'];
        $c = $options['close'];
        
        $urlServ = $matches[2];
        $scheme = $matches[3];
        $host = trim($matches[4], '\'"'); //Remove extraneous crap
        $path = trim($matches[5], '\'"');
        $rest = $matches[1].$matches[6];
        
        if($scheme === ''){
            //Default to http
            $urlServ = $this->_defaultScheme.'://';
            $scheme = $this->_defaultScheme;
        }
        
        //Add trailing slash if missing (to create a valid URL)
        if($path === ''){
            $path = '/';
        }

        if(in_array($scheme, $this->_allowedSchemes)){
            //If protocol is in the approved list than allow it
            return $o.'a href="'.$urlServ.$host.$path.'"'.$rest.$c.$matches[7].$o.'/a'.$c;
        } else {
            //Else remove the url tag
            return $matches[7];
        }
    }
    
    /**
     * Render as ASCII by printing the URL next to link's text in parens
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
        
        //If the url equals the text, return the text straight up
        if ($tag['attributes']['href'] == $block['text']){
            return $block;
        }
        
        $block['text'] .= ' ('.$tag['attributes']['href'].')';
        return $block;
    }
}

?>